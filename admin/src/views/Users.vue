<template>
    <div class="p-4 bg-surface-50 dark:bg-surface-900 rounded-lg shadow">
        <div class="flex justify-between items-center mb-4">
            <div class="relative w-full max-w-md">
                <IconField class="w-full">
                    <InputIcon class="pi pi-search" />
                    <InputText v-model="searchQuery" placeholder="Rechercher un utilisateur..." class="w-full"
                        @input="onSearchChange" />
                </IconField>
            </div>
            <Button label="Ajouter un utilisateur" icon="pi pi-plus"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md shadow-sm transition-colors duration-200"
                @click="openNewUserDialog" />
        </div>

        <div class="bg-surface-50 dark:bg-surface-900 p-1 rounded-lg shadow overflow-hidden">
            <DataTable :value="users" :paginator="true" :rows="10" :loading="loading" stripedRows
                responsiveLayout="scroll" class="border border-surface-200 dark:border-surface-700" dataKey="id"
                :rowHover="true">
                <Column field="id" header="ID" sortable
                    headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left">
                </Column>
                <Column field="username" header="Username" sortable
                    headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left">
                </Column>
                <Column field="email" header="Email" sortable
                    headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left">
                    <template #body="slotProps">
                        {{ slotProps.data.email || 'No email provided' }}
                    </template>
                </Column>
                <Column field="roles" header="Roles"
                    headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left">
                    <template #body="slotProps">
                        <div class="flex flex-wrap gap-1">
                            <span v-for="role in slotProps.data.roles" :key="role"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                {{ role }}
                            </span>
                        </div>
                    </template>
                </Column>
                <Column header="Actions"
                    headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left">
                    <template #body="slotProps">
                        <div class="flex space-x-2">
                            <Button 
                                icon="pi pi-pencil" 
                                class="p-button-rounded p-button-success p-button-sm"
                                @click="editUser(slotProps.data)"
                                v-tooltip.top="'Modifier'"
                            />
                            <Button 
                                icon="pi pi-trash" 
                                class="p-button-rounded p-button-danger p-button-sm"
                                @click="confirmDeleteUser(slotProps.data)"
                                v-tooltip.top="'Supprimer'"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- User Dialog -->
        <Dialog 
            v-model:visible="userDialog" 
            :style="{ width: '450px' }" 
            :header="user.id ? 'Modifier l\'utilisateur' : 'Créer un utilisateur'" 
            :modal="true"
            class="rounded-lg shadow-lg"
        >
            <div class="space-y-4">
                <div class="space-y-1">
                    <label for="username"
                        class="text-sm font-medium text-surface-700 dark:text-surface-300">Nom d'utilisateur</label>
                    <InputText id="username" v-model.trim="user.username" required="true" autofocus
                        class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                        :class="{ 'border-red-500': submitted && !user.username }" />
                    <small v-if="submitted && !user.username" class="text-red-500 text-xs">Le nom d'utilisateur est requis.</small>
                </div>

                <div class="space-y-1">
                    <label for="email" class="text-sm font-medium text-surface-700 dark:text-surface-300">Email</label>
                    <InputText id="email" v-model.trim="user.email" required="true"
                        class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                        :class="{ 'border-red-500': submitted && !user.email }" />
                    <small v-if="submitted && !user.email" class="text-red-500 text-xs">L'email est requis.</small>
                </div>

                <div class="space-y-1">
                    <label for="password"
                        class="text-sm font-medium text-surface-700 dark:text-surface-300">Mot de passe</label>
                    <Password id="password" v-model="user.password" :feedback="false" toggleMask class="w-full"
                        inputClass="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                        :class="{ 'border-red-500': submitted && !user.password && !user.id }" />
                    <small v-if="submitted && !user.password && !user.id" class="text-red-500 text-xs">
                        Le mot de passe est requis pour les nouveaux utilisateurs.
                    </small>
                </div>

                <div class="space-y-1">
                    <label for="roles" class="text-sm font-medium text-surface-700 dark:text-surface-300">Rôles</label>
                    <MultiSelect id="roles" v-model="user.roles" :options="availableRoles" optionLabel="label"
                        optionValue="value" display="chip" :filter="true" :showClear="false" :showToggleAll="false"
                        placeholder="Sélectionner des rôles" class="w-full" />
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end space-x-2 pt-4 border-t border-surface-200 dark:border-surface-700">
                    <Button 
                        label="Annuler" 
                        icon="pi pi-times" 
                        class="p-button-text" 
                        @click="hideDialog"
                    />
                    <Button 
                        label="Enregistrer" 
                        icon="pi pi-check" 
                        class="p-button-primary" 
                        @click="saveUser"
                    />
                </div>
            </template>
        </Dialog>

        <!-- Delete User Confirmation -->
        <Dialog 
            v-model:visible="deleteUserDialog" 
            :style="{ width: '450px' }" 
            header="Confirmer la suppression" 
            :modal="true"
            class="rounded-lg shadow-lg"
        >
            <div class="flex items-center justify-center p-4">
                <i class="pi pi-exclamation-triangle text-yellow-500 mr-3 text-3xl" />
                <span v-if="user" class="text-surface-700 dark:text-surface-300">
                    Êtes-vous sûr de vouloir supprimer <b>{{ user.username }}</b> ?
                </span>
            </div>
            <template #footer>
                <div class="flex justify-end space-x-2 pt-4 border-t border-surface-200 dark:border-surface-700">
                    <Button 
                        label="Non" 
                        icon="pi pi-times" 
                        class="p-button-text" 
                        @click="deleteUserDialog = false"
                    />
                    <Button 
                        label="Oui" 
                        icon="pi pi-check" 
                        class="p-button-danger" 
                        @click="deleteUser"
                    />
                </div>
            </template>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useUserService, type User } from '../composables/useUserService';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Chip from 'primevue/chip';
import Password from 'primevue/password';
import MultiSelect from 'primevue/multiselect';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';

interface RoleOption {
    label: string;
    value: string;
}

const toast = useToast();
const { fetchUsers, createUser, updateUser, deleteUserById } = useUserService();

// Data
const users = ref<User[]>([]);
const user = ref<User>({
    id: null as unknown as number,
    username: '',
    email: '',
    password: '',
    roles: ['ROLE_USER']
});
const userDialog = ref(false);
const deleteUserDialog = ref(false);
const submitted = ref(false);
const loading = ref(false);

// Available roles for selection
const availableRoles = ref<RoleOption[]>([
    { label: 'Utilisateur', value: 'ROLE_USER' },
    { label: 'Administrateur', value: 'ROLE_ADMIN' }
]);

// Add search functionality
const searchQuery = ref('');
const allUsers = ref<User[]>([]);

// Lifecycle hooks
onMounted(() => {
    loadUsers();
});

// Methods
const loadUsers = async (): Promise<void> => {
    loading.value = true;
    try {
        const fetchedUsers = await fetchUsers();
        // Ensure we have an array of users with valid roles
        allUsers.value = fetchedUsers.map(user => {
            return user;
        });
        applyFilters(); // Apply filters after loading users
    } catch (error: any) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: error.message || 'Impossible de charger les utilisateurs', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const openNewUserDialog = (): void => {
    user.value = {
        id: null as unknown as number,
        username: '',
        email: '',
        password: '',
        roles: ['ROLE_USER']
    };
    submitted.value = false;
    userDialog.value = true;
};

const editUser = (userData: User): void => {
    // Create a deep copy of the user data
    user.value = JSON.parse(JSON.stringify(userData));
    // Clear password for edit mode
    user.value.password = '';
    userDialog.value = true;
};

const hideDialog = (): void => {
    userDialog.value = false;
    submitted.value = false;
};

const saveUser = async (): Promise<void> => {
    submitted.value = true;

    if (!user.value.username || !user.value.email || (!user.value.id && !user.value.password)) {
        return;
    }

    try {
        if (user.value.id) {
            // Update existing user
            await updateUser(user.value);
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Utilisateur mis à jour', life: 3000 });
        } else {
            // Create new user
            await createUser(user.value);
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Utilisateur créé', life: 3000 });
        }

        userDialog.value = false;
        loadUsers();
    } catch (error: any) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: error.message || 'Opération échouée', life: 3000 });
    }
};

const confirmDeleteUser = (userData: User): void => {
    user.value = userData;
    deleteUserDialog.value = true;
};

const deleteUser = async (): Promise<void> => {
    try {
        if (user.value.id) {
            await deleteUserById(user.value.id);
            deleteUserDialog.value = false;
            loadUsers();
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Utilisateur supprimé', life: 3000 });
        }
    } catch (error: any) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: error.message || 'Impossible de supprimer l\'utilisateur', life: 3000 });
    }
};

const onSearchChange = () => {
    applyFilters();
};

const applyFilters = () => {
    if (!searchQuery.value.trim()) {
        users.value = [...allUsers.value];
        return;
    }

    const query = searchQuery.value.toLowerCase().trim();
    users.value = allUsers.value.filter(user =>
        user.username.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        user.roles.some(role => role.toLowerCase().includes(query))
    );
};

// Watch for search query changes
watch(searchQuery, () => {
    applyFilters();
});
</script>