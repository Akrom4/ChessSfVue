import { ref } from 'vue'
import api from '../lib/axios'

export interface Chapter {
  id?: number
  title?: string
  course?: any // Will contain the Course reference
  pgndata?: any[]
  rawpgn?: string
}

export function useChapterService() {
  const loading = ref(false);
  const error = ref<string | null>(null);

  /**
   * Fetch all chapters for a course
   */
  const fetchChaptersByCourse = async (courseId: number): Promise<Chapter[]> => {
    loading.value = true;
    error.value = null;
    
    try {
      // Since API Platform doesn't directly provide a "by course" endpoint,
      // we could either use a custom endpoint or filter client-side
      const response = await api.get(`/courses/${courseId}/chapters`);
      
      // Extract the member array from the Hydra collection
      if (response.data && response.data['member']) {
        return response.data.member;
      } else if (Array.isArray(response.data)) {
        return response.data;
      } else {
        console.error('Unexpected API response format:', response.data);
        return [];
      }
    } catch (err: any) {
      error.value = err.response?.data?.detail || err.message || 'Failed to fetch chapters';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Fetch a single chapter by ID
   */
  const fetchChapterById = async (id: number): Promise<Chapter> => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.get(`/chapters/${id}`);
      return response.data;
    } catch (err: any) {
      error.value = err.response?.data?.detail || err.message || 'Failed to fetch chapter';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create a new chapter
   */
  const createChapter = async (chapter: Chapter): Promise<Chapter> => {
    loading.value = true;
    error.value = null;
    
    try {
      // Prepare the chapter data for API Platform
      const chapterData = { ...chapter };
      
      // Set the content type for API Platform
      const response = await api.post('/chapters', chapterData, {
        headers: {
          'Content-Type': 'application/ld+json',
          'Accept': 'application/ld+json'
        }
      });
      return response.data;
    } catch (err: any) {
      error.value = err.response?.data?.detail || err.message || 'Failed to create chapter';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update an existing chapter
   */
  const updateChapter = async (chapter: Chapter): Promise<Chapter> => {
    if (!chapter.id) {
      throw new Error('Chapter ID is required for update');
    }
    
    loading.value = true;
    error.value = null;
    
    try {
      // Prepare the chapter data for update
      const chapterData = { ...chapter };
      
      const response = await api.put(`/chapters/${chapter.id}`, chapterData, {
        headers: {
          'Content-Type': 'application/ld+json',
          'Accept': 'application/ld+json'
        }
      });
      return response.data;
    } catch (err: any) {
      error.value = err.response?.data?.detail || err.message || 'Failed to update chapter';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Delete a chapter by ID
   */
  const deleteChapterById = async (id: number): Promise<void> => {
    loading.value = true;
    error.value = null;
    
    try {
      await api.delete(`/chapters/${id}`);
    } catch (err: any) {
      error.value = err.response?.data?.detail || err.message || 'Failed to delete chapter';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  return {
    loading,
    error,
    fetchChaptersByCourse,
    fetchChapterById,
    createChapter,
    updateChapter,
    deleteChapterById
  };
} 