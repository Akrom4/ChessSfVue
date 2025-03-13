import { ref } from 'vue'
import api from '../lib/axios'

export interface Course {
  id?: number
  title: string
  description?: string
  image?: string
  createdat?: string
  updatedat?: string
  author?: string
  colorside?: string
  imageFile?: File
  chapters?: any[]
  userCourses?: any[]
  
  // API Platform specific fields
  '@id'?: string
  '@type'?: string
}

export function useCourseService() {
  const loading = ref(false);
  const error = ref<string | null>(null);

  /**
   * Fetch all courses
   */
  const fetchCourses = async (): Promise<Course[]> => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.get('/courses');
      
      // Extract courses from "member" property
      const apiCourses = response.data.member || [];
      
      // Normalize the data to match our interface
      const normalizedCourses = apiCourses.map((course: any) => ({
        id: course.id,
        title: course.title,
        description: course.description,
        image: course.image,
        createdat: course.createdat,
        updatedat: course.updatedat,
        author: course.author,
        colorside: course.colorside,
        chapters: course.chapters || [],
        userCourses: course.userCourses || [],
        '@id': course['@id'],
        '@type': course['@type']
      }));
      
      return normalizedCourses;
    } catch (err: any) {
      error.value = err.response?.data?.detail || err.message || 'Failed to fetch courses';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Fetch a single course by ID
   */
  const fetchCourseById = async (id: number): Promise<Course> => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.get(`/courses/${id}`);
      return response.data;
    } catch (err: any) {
      error.value = err.response?.data?.detail || err.message || 'Failed to fetch course';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create a new course
   */
  const createCourse = async (course: Course): Promise<Course> => {
    loading.value = true;
    error.value = null;
    
    try {
      // Create course without image
      const courseData = { ...course };
      delete courseData.imageFile; // Remove file reference
      
      // Explicitly set content type to application/ld+json for API Platform
      const response = await api.post('/courses', courseData, {
        headers: {
          'Content-Type': 'application/ld+json',
          'Accept': 'application/ld+json'
        }
      });
      return response.data;
    } catch (err: any) {
      error.value = err.response?.data?.detail || err.message || 'Failed to create course';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Upload an image for a course
   * This uses a separate endpoint specifically designed for VichUploader
   */
  const uploadCourseImage = async (courseId: number, imageFile: File): Promise<Course> => {
    loading.value = true;
    error.value = null;
    
    try {
      // Create a new FormData object for the file upload
      const formData = new FormData();
      
      // Ensure we're using the correct field name that matches what the controller expects
      // The field name must match the property name in the Courses entity annotated with @Vich\UploadableField
      formData.append('imageFile', imageFile);
      
      // Log the file being uploaded for debugging
      console.log('Uploading file:', imageFile.name, 'size:', imageFile.size, 'type:', imageFile.type);
      
      // DEBUG: Inspect the FormData contents
      console.log('FormData entries:');
      for (const pair of formData.entries()) {
        console.log(pair[0], ':', pair[1]);
        if (pair[1] instanceof File) {
          console.log('File details:', {
            name: pair[1].name,
            type: pair[1].type,
            size: pair[1].size
          });
        }
      }
      
      // Make the API call with the FormData
      // IMPORTANT: For FormData, do NOT set Content-Type header - browser will set it with boundary
      const response = await api.post(`/courses/${courseId}/upload`, formData, {
        headers: {
          // Remove any Content-Type header to let the browser set it with the proper boundary
          // This is critical for multipart/form-data requests
        },
        // Add this to see the actual form data being sent in network requests
        onUploadProgress: (progressEvent) => {
          // Fix for TypeScript error - check if total exists
          const total = progressEvent.total || 0;
          const percentage = total > 0 ? Math.round((progressEvent.loaded * 100) / total) : 0;
          console.log('Upload progress:', percentage + '%');
        }
      });
      
      return response.data;
    } catch (err: any) {
      // Enhanced error handling with more detail
      console.error('Image upload error:', err.response?.data || err.message);
      error.value = err.response?.data?.detail || err.message || 'Failed to upload course image';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update an existing course
   */
  const updateCourse = async (course: Course): Promise<Course> => {
    if (!course.id) {
      throw new Error('Course ID is required for update');
    }
    
    loading.value = true;
    error.value = null;
    
    try {
      // Regular update without image
      const courseData = { ...course };
      delete courseData.imageFile; // Remove file reference if present
      
      // Explicitly set content type to application/ld+json for API Platform
      const response = await api.put(`/courses/${course.id}`, courseData, {
        headers: {
          'Content-Type': 'application/ld+json',
          'Accept': 'application/ld+json'
        }
      });
      return response.data;
    } catch (err: any) {
      error.value = err.response?.data?.detail || err.message || 'Failed to update course';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Delete a course by ID
   */
  const deleteCourseById = async (id: number): Promise<void> => {
    loading.value = true;
    error.value = null;
    
    try {
      await api.delete(`/courses/${id}`);
    } catch (err: any) {
      error.value = err.response?.data?.detail || err.message || 'Failed to delete course';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  return {
    loading,
    error,
    fetchCourses,
    fetchCourseById,
    createCourse,
    updateCourse,
    uploadCourseImage,
    deleteCourseById
  };
} 