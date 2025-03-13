import { ref } from 'vue';
import api from '../lib/axios';

// Define interfaces for our data structures
export interface User {
    id?: number;
    username: string;
    email: string;
    password?: string;
    roles: string[];
    created_at?: string;
    updated_at?: string | null;
}

export interface ApiResponse<T> {
    'member': T[];
    'totalItems': number;
    '@context'?: string;
    '@id'?: string;
    '@type'?: string;
    [key: string]: any;
}

/**
 * Composable for User management API calls
 */
export function useUserService() {
    // Using API Platform endpoints
    const apiUrl = '/users';
    const error = ref<string | null>(null);

    /**
     * Fetch all users
     */
    const fetchUsers = async (): Promise<User[]> => {
        try {
            error.value = null;
            const response = await api.get<ApiResponse<User>>(apiUrl);

            let users: User[] = [];

            // Check if response has 'member' property (API Platform format)
            if (response.data && 'member' in response.data && Array.isArray(response.data.member)) {
                users = response.data.member as User[];
            }
            // Fallback to checking for hydra:member (different API Platform versions)
            else if (response.data && 'hydra:member' in response.data && Array.isArray(response.data['hydra:member'])) {
                users = response.data['hydra:member'] as User[];
            }
            // Fallback to direct array
            else if (Array.isArray(response.data)) {
                users = response.data;
            }
            else {
                return [];
            }
            return users;
        } catch (err: any) {
            error.value = err.response?.data?.detail || 'Failed to fetch users';
            throw new Error(error.value || 'Unknown error');
        }
    };

    /**
     * Fetch a single user by ID
     */
    const fetchUserById = async (id: number): Promise<User> => {
        try {
            error.value = null;
            const response = await api.get<User>(`${apiUrl}/${id}`);

            // Ensure the response contains a user object with roles
            if (response.data && response.data.username) {
                if (!response.data.roles || !Array.isArray(response.data.roles)) {
                    response.data.roles = ['ROLE_USER'];
                }
                return response.data;
            }

            throw new Error(`Invalid user data received for ID ${id}`);
        } catch (err: any) {
            error.value = err.response?.data?.detail || `Failed to fetch user with ID ${id}`;
            throw new Error(error.value || 'Unknown error');
        }
    };

    /**
     * Create a new user
     */
    const createUser = async (userData: User): Promise<User> => {
        try {
            error.value = null;

            // Create a clean object with only the properties expected by the API
            const cleanUserData = {
                username: userData.username,
                email: userData.email,
                password: userData.password,
                // Ensure roles are properly handled - Symfony will add ROLE_USER automatically
                roles: [...userData.roles]
            };

            const response = await api.post<User>(apiUrl, cleanUserData);
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.detail || 'Failed to create user';
            throw new Error(error.value || 'Unknown error');
        }
    };

    /**
     * Update an existing user
     */
    const updateUser = async (userData: User): Promise<User> => {
        try {
            error.value = null;
            if (!userData.id) {
                throw new Error('User ID is required for updates');
            }

            const { id, ...userDataWithoutId } = userData;

            // Remove the password field if it's empty
            if (!userDataWithoutId.password) {
                delete userDataWithoutId.password;
            }

            // Remove any properties that might not be expected by the API
            const cleanUserData = {
                username: userDataWithoutId.username,
                email: userDataWithoutId.email,
                // Only include password if it exists
                ...(userDataWithoutId.password ? { password: userDataWithoutId.password } : {}),
                // Ensure roles are properly handled - Symfony will add ROLE_USER automatically
                roles: [...userDataWithoutId.roles]
            };

            const response = await api.put<User>(`${apiUrl}/${id}`, cleanUserData);
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.detail || `Failed to update user with ID ${userData.id}`;
            throw new Error(error.value || 'Unknown error');
        }
    };

    /**
     * Delete a user by ID
     */
    const deleteUserById = async (id: number): Promise<boolean> => {
        try {
            error.value = null;
            await api.delete(`${apiUrl}/${id}`);
            return true;
        } catch (err: any) {
            error.value = err.response?.data?.detail || `Failed to delete user with ID ${id}`;
            throw new Error(error.value || 'Unknown error');
        }
    };

    return {
        error,
        fetchUsers,
        fetchUserById,
        createUser,
        updateUser,
        deleteUserById
    };
} 