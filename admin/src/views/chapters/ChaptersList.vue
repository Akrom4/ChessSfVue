<template>
  <div class="p-4 bg-surface-50 dark:bg-surface-900 rounded-lg shadow">
    <div class="flex justify-between items-center mb-4">
      <div class="flex items-center">
        <h2 class="text-xl font-semibold text-surface-800 dark:text-surface-100">
          Chapitres du cours: {{ courseName }}
        </h2>
      </div>
      <div class="flex items-center space-x-2">
        <Button 
          label="Retour aux cours" 
          icon="pi pi-arrow-left" 
          class="p-button-outlined"
          @click="navigateBackToCourses"
        />
        <Button 
          label="Ajouter un chapitre" 
          icon="pi pi-plus" 
          class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md shadow-sm transition-colors duration-200" 
          @click="createNewChapter"
        />
      </div>
    </div>

    <div class="bg-surface-50 dark:bg-surface-900 p-1 rounded-lg shadow overflow-hidden">
      <DataTable 
        :value="chapters" 
        :paginator="true" 
        :rows="10"
        :loading="loading"
        stripedRows
        responsiveLayout="scroll"
        class="border border-surface-200 dark:border-surface-700"
        dataKey="id"
        :rowHover="true"
        emptyMessage="Aucun chapitre trouvé pour ce cours"
      >
        <Column field="id" header="ID" sortable headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left"></Column>
        <Column field="title" header="Titre" sortable headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left"></Column>
        <Column header="Actions" headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left">
          <template #body="slotProps">
            <div class="flex space-x-2">
              <Button 
                icon="pi pi-eye" 
                class="p-button-rounded p-button-info p-button-sm"
                @click="viewChapter(slotProps.data)"
                v-tooltip.top="'Voir les détails'"
              />
              <Button 
                icon="pi pi-pencil" 
                class="p-button-rounded p-button-success p-button-sm"
                @click="editChapter(slotProps.data)"
                v-tooltip.top="'Modifier'"
              />
              <Button 
                icon="pi pi-trash" 
                class="p-button-rounded p-button-danger p-button-sm"
                @click="confirmDeleteChapter(slotProps.data)"
                v-tooltip.top="'Supprimer'"
              />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Chapter View Dialog -->
    <Dialog 
      v-model:visible="viewDialog" 
      :style="{ width: '600px' }" 
      header="Détails du chapitre" 
      :modal="true"
      class="rounded-lg shadow-lg"
    >
      <div v-if="selectedChapter" class="space-y-4">
        <div class="flex flex-col gap-3">
          <div>
            <h3 class="text-lg font-semibold text-surface-800 dark:text-surface-100">{{ selectedChapter.title }}</h3>
          </div>
          
          <div v-if="selectedChapter.rawpgn" class="mt-4 p-3 bg-surface-100 dark:bg-surface-800 rounded">
            <h4 class="text-md font-medium text-surface-800 dark:text-surface-200 mb-2">PGN:</h4>
            <p class="text-surface-700 dark:text-surface-300 whitespace-pre-wrap font-mono text-sm">{{ selectedChapter.rawpgn }}</p>
          </div>
        </div>
      </div>
    </Dialog>

    <!-- Delete Chapter Confirmation -->
    <Dialog 
      v-model:visible="deleteChapterDialog" 
      :style="{ width: '450px' }" 
      header="Confirmer la suppression" 
      :modal="true"
      class="rounded-lg shadow-lg"
    >
      <div class="flex items-center justify-center p-4">
        <i class="pi pi-exclamation-triangle text-yellow-500 mr-3 text-3xl" />
        <span v-if="selectedChapter" class="text-surface-700 dark:text-surface-300">
          Êtes-vous sûr de vouloir supprimer le chapitre <b>{{ selectedChapter.title }}</b> ?
        </span>
      </div>
      <template #footer>
        <div class="flex justify-end space-x-2 pt-4 border-t border-surface-200 dark:border-surface-700">
          <Button 
            label="Non" 
            icon="pi pi-times" 
            class="p-button-text" 
            @click="deleteChapterDialog = false"
          />
          <Button 
            label="Oui" 
            icon="pi pi-check" 
            class="p-button-danger" 
            @click="deleteChapter"
          />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import { useChapterService, type Chapter } from '../../composables/useChapterService';
import { useCourseService } from '../../composables/useCourseService';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';

const toast = useToast();
const route = useRoute();
const router = useRouter();
const { 
  fetchChaptersByCourse, 
  fetchChapterById, 
  deleteChapterById,
  loading: chapterLoading
} = useChapterService();
const { fetchCourseById } = useCourseService();

// Data
const chapters = ref<Chapter[]>([]);
const selectedChapter = ref<Chapter | null>(null);
const courseName = ref('');
const courseId = ref<number | null>(null);
const viewDialog = ref(false);
const deleteChapterDialog = ref(false);
const loading = ref(false);

// When the component mounts, load chapters for the course
onMounted(async () => {
  const routeCourseId = route.params.courseId as string;
  if (routeCourseId) {
    courseId.value = parseInt(routeCourseId, 10);
    await loadCourse();
    await loadChapters();
  } else {
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur', 
      detail: 'ID du cours manquant', 
      life: 3000 
    });
    navigateBackToCourses();
  }
});

// Load course details to get the name
const loadCourse = async (): Promise<void> => {
  if (!courseId.value) return;
  
  try {
    const course = await fetchCourseById(courseId.value);
    courseName.value = course.title;
  } catch (error: any) {
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur', 
      detail: error.message || 'Impossible de charger les informations du cours', 
      life: 3000 
    });
  }
};

// Load chapters for the current course
const loadChapters = async (): Promise<void> => {
  if (!courseId.value) return;
  
  loading.value = true;
  try {
    const data = await fetchChaptersByCourse(courseId.value);
    console.log('Chapters data received:', data);
    // Make sure we've got an array
    chapters.value = Array.isArray(data) ? data : [];
    console.log('Chapters array set to:', chapters.value);
  } catch (error: any) {
    console.error('Error loading chapters:', error);
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur', 
      detail: error.message || 'Impossible de charger les chapitres', 
      life: 3000 
    });
  } finally {
    loading.value = false;
  }
};

// View chapter details
const viewChapter = async (chapter: Chapter): Promise<void> => {
  try {
    // Get full details if needed
    selectedChapter.value = await fetchChapterById(chapter.id!);
    viewDialog.value = true;
  } catch (error) {
    // If fetch fails, at least show what we have
    selectedChapter.value = chapter;
    viewDialog.value = true;
  }
};

// Edit a chapter
const editChapter = (chapter: Chapter): void => {
  if (chapter.id && courseId.value) {
    router.push({ 
      name: 'ChapterEdit', 
      params: { 
        courseId: courseId.value,
        chapterId: chapter.id
      }
    });
  }
};

// Create a new chapter
const createNewChapter = (): void => {
  if (courseId.value) {
    router.push({ 
      name: 'ChapterCreate', 
      params: { courseId: courseId.value }
    });
  }
};

// Confirm chapter deletion
const confirmDeleteChapter = (chapter: Chapter): void => {
  selectedChapter.value = chapter;
  deleteChapterDialog.value = true;
};

// Delete chapter
const deleteChapter = async (): Promise<void> => {
  if (!selectedChapter.value?.id) return;
  
  try {
    await deleteChapterById(selectedChapter.value.id);
    deleteChapterDialog.value = false;
    toast.add({ 
      severity: 'success', 
      summary: 'Succès', 
      detail: 'Chapitre supprimé', 
      life: 3000 
    });
    await loadChapters();
  } catch (error: any) {
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur', 
      detail: error.message || 'Impossible de supprimer le chapitre', 
      life: 3000 
    });
  }
};

// Navigate back to courses list
const navigateBackToCourses = (): void => {
  router.push({ name: 'Courses' });
};
</script> 