import api from '../api'
import { useAuth } from '../composables/useAuth'

export interface Course {
  id: number
  title: string
  description: string | null
  image: string | null
  createdat: string
  updatedat: string | null
  chapters: string[]
  userCourses: UserCourse[]
  author: string | null
  colorside: string | null
  difficulty: 'easy' | 'intermediate' | 'advanced' | 'expert' | null
}

export interface Chapter {
  id: number
  title: string | null
  course: string | null
  pgndata: string[] | null
  rawpgn: string | null
}

export interface UserCourse {
  id: number
  userid: { id: number; username: string }
  courseid: Course
  completedChapters: string[] | null
  completionPercentage: number | null
  createdAt: string | null
  updatedAt: string | null
}

class CoursesService {
  async getAllCourses() {
    try {
      const response = await api.get('/courses')
      return response.data.member || []
    } catch (error) {
      console.error('Error fetching courses:', error)
      return []
    }
  }

  async getUserCourses() {
    try {
      const response = await api.get('/user_courses')
      return response.data.member || []
    } catch (error) {
      console.error('Error fetching user courses:', error)
      throw error
    }
  }

  async addCourseToUser(courseId: number) {
    try {
      // Get current user ID
      const auth = useAuth();
      const userId = auth.user.value?.id;
      
      if (!userId) {
        throw new Error('User not authenticated');
      }
      
      // API Platform expects data in a specific format with a context
      const requestData = {
        "@context": "/api/contexts/UserCourses",
        "@type": "UserCourses",
        "courseid": `/api/courses/${courseId}`,
        "userid": `/api/users/${userId}`  // This line adds the user ID
      };
      
      console.log('Adding course to user with payload:', requestData);
      const response = await api.post('/user_courses', requestData);
      return response.data;
    } catch (error) {
      console.error('Error adding course to user:', error);
      throw error;
    }
  }

  async removeCourseFromUser(userCourseId: number) {
    try {
      console.log(`Attempting to remove user course with ID: ${userCourseId}`);
      // Set specific headers for DELETE request
      const config = {
        headers: {
          'Content-Type': 'application/ld+json',
          'Accept': 'application/ld+json'
        }
      };
      
      await api.delete(`/user_courses/${userCourseId}`, config);
      console.log(`Successfully removed user course with ID: ${userCourseId}`);
      return true;
    } catch (error: any) {
      // More detailed error handling
      if (error.response) {
        console.error(`Error ${error.response.status} removing course from user:`, error.response.data);
        
        // Handle specific status codes
        if (error.response.status === 500) {
          console.error('Server error occurred - check backend logs for details');
        }
      } else {
        console.error('Error removing course from user:', error);
      }
      throw error;
    }
  }

  async getCourse(courseId: number) {
    try {
      const response = await api.get(`/courses/${courseId}`)
      return response.data
    } catch (error) {
      console.error(`Error fetching course ${courseId}:`, error)
      throw error
    }
  }

  async getCourseChapters(courseId: number) {
    try {
      // Use our new custom endpoint instead of /courses/${courseId}/chapters
      const response = await api.get(`/courses/${courseId}/my-chapters`);
      console.log('Response data chapters :', response.data);
      return response.data.chapters || []; // The chapters are in the 'chapters' property
    } catch (error) {
      console.error(`Error fetching chapters for course ${courseId}:`, error);
      return [];
    }
  }

  async getChapter(courseId: number, chapterId: number) {
    try {
      // Direct API endpoint for a single chapter
      const response = await api.get(`/courses/${courseId}/chapters/${chapterId}`);
      return response.data;
    } catch (error) {
      console.error(`Error fetching chapter ${chapterId} for course ${courseId}:`, error);
      throw error;
    }
  }

  async getFollowingStatus(courseId: number) {
    try {
      const response = await api.get(`/courses/${courseId}/following`);
      return response.data; // Contains {following: true/false, userCourseId: number}
    } catch (error) {
      console.error(`Error checking course follow status:`, error);
      return { following: false };
    }
  }
}

export default new CoursesService()