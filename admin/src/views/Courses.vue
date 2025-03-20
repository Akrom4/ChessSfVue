<template>
  <div class="p-4 bg-surface-50 dark:bg-surface-900 rounded-lg shadow">
    <div class="flex justify-between items-center mb-4">
      <div class="relative w-full max-w-md">
        <IconField class="w-full">
          <InputIcon class="pi pi-search" />
          <InputText 
            v-model="searchQuery" 
            placeholder="Rechercher un cours..." 
            class="w-full"
            @input="onSearchChange"
          />
        </IconField>
      </div>
      <Button 
        label="Ajouter un cours" 
        icon="pi pi-plus" 
        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md shadow-sm transition-colors duration-200" 
        @click="openNewCourseDialog" 
      />
    </div>

    <div class="bg-surface-50 dark:bg-surface-900 p-1 rounded-lg shadow overflow-hidden">
      <DataTable 
        :value="courses" 
        :paginator="true" 
        :rows="10"
        :loading="loading"
        stripedRows
        responsiveLayout="scroll"
        class="border border-surface-200 dark:border-surface-700"
        dataKey="id"
        :rowHover="true"
      >
        <Column field="id" header="ID" sortable headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left"></Column>
        <Column field="title" header="Titre" sortable headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left"></Column>
        <Column field="author" header="Auteur" sortable headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left"></Column>
        <Column field="colorside" header="Côté" headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left">
          <template #body="slotProps">
            <div class="flex items-center" v-if="slotProps.data.colorside">
              <i :class="`pi pi-circle-fill mr-2 ${slotProps.data.colorside === 'W' ? 'text-white border border-gray-400 rounded-full' : 'text-black'}`"></i>
              {{ slotProps.data.colorside === 'W' ? 'Blanc' : 'Noir' }}
            </div>
            <span v-else class="text-surface-400 dark:text-surface-500">Non défini</span>
          </template>
        </Column>
        <Column field="image" header="Image" headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left">
          <template #body="slotProps">
            <img 
              v-if="slotProps.data.image" 
              :src="getImageUrl(slotProps.data.image)" 
              :alt="slotProps.data.title" 
              class="h-10 w-16 object-cover rounded"
            />
            <span v-else class="text-surface-400 dark:text-surface-500">Pas d'image</span>
          </template>
        </Column>
        <Column field="createdat" header="Créé le" sortable headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left">
          <template #body="slotProps">
            {{ formatDate(slotProps.data.createdat) }}
          </template>
        </Column>
        <Column header="Actions" headerClass="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-semibold px-4 py-3 text-left">
          <template #body="slotProps">
            <div class="flex space-x-2">
              <Button 
                icon="pi pi-eye" 
                class="p-button-rounded p-button-info p-button-sm"
                @click="viewCourse(slotProps.data)"
                v-tooltip.top="'Voir les détails'"
              />
              <Button 
                icon="pi pi-book" 
                class="p-button-rounded p-button-primary p-button-sm"
                @click="manageChapters(slotProps.data)"
                v-tooltip.top="'Gérer les chapitres'"
              />
              <Button 
                icon="pi pi-pencil" 
                class="p-button-rounded p-button-success p-button-sm"
                @click="editCourse(slotProps.data)"
                v-tooltip.top="'Modifier'"
              />
              <Button 
                icon="pi pi-trash" 
                class="p-button-rounded p-button-danger p-button-sm"
                @click="confirmDeleteCourse(slotProps.data)"
                v-tooltip.top="'Supprimer'"
              />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Course Dialog -->
    <Dialog 
      v-model:visible="courseDialog" 
      :style="{ width: '550px' }" 
      :header="course.id ? 'Modifier le cours' : 'Créer un cours'" 
      :modal="true"
      class="rounded-lg shadow-lg"
    >
      <div class="space-y-6">
        <!-- Title -->
        <div class="space-y-1">
          <label for="title" class="text-sm font-medium text-surface-700 dark:text-surface-300">Titre</label>
          <InputText 
            id="title" 
            v-model.trim="course.title" 
            required="true" 
            autofocus 
            class="w-full"
            :class="{'p-invalid': submitted && !course.title}"
          />
          <small v-if="submitted && !course.title" class="text-red-500 text-xs">Le titre est requis.</small>
        </div>
        
        <!-- Author -->
        <div class="space-y-1">
          <label for="author" class="text-sm font-medium text-surface-700 dark:text-surface-300">Auteur</label>
          <InputText 
            id="author" 
            v-model.trim="course.author" 
            class="w-full"
          />
        </div>
        
        <!-- Description -->
        <div class="space-y-1">
          <label for="description" class="text-sm font-medium text-surface-700 dark:text-surface-300">Description</label>
          <Textarea 
            id="description" 
            v-model="course.description" 
            rows="5"
            autoResize
            class="w-full"
          />
        </div>
        
        <!-- Color Side -->
        <div class="space-y-1">
          <label for="colorside" class="text-sm font-medium text-surface-700 dark:text-surface-300">Côté des pièces</label>
          <Select
            id="colorside"
            v-model="course.colorside"
            :options="chessSides"
            optionLabel="label"
            optionValue="value"
            placeholder="Sélectionner un côté"
            class="w-full"
          >
            <template #value="slotProps">
              <div class="flex items-center" v-if="slotProps.value">
                <i :class="`pi ${slotProps.value === 'W' ? 'pi-circle-fill text-white border border-gray-400 rounded-full' : 'pi-circle-fill text-black'} mr-2`"></i>
                {{ slotProps.value === 'W' ? 'Blanc' : 'Noir' }}
              </div>
              <span v-else>Sélectionner un côté</span>
            </template>
            <template #option="slotProps">
              <div class="flex items-center">
                <i :class="`pi ${slotProps.option.value === 'W' ? 'pi-circle-fill text-white border border-gray-400 rounded-full' : 'pi-circle-fill text-black'} mr-2`"></i>
                {{ slotProps.option.label }}
              </div>
            </template>
          </Select>
        </div>
        
        <!-- Difficulty Level -->
        <div class="space-y-1">
          <label for="difficulty" class="text-sm font-medium text-surface-700 dark:text-surface-300">Niveau de difficulté</label>
          <Select
            id="difficulty"
            v-model="course.difficulty"
            :options="difficultyOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Sélectionner un niveau de difficulté"
            class="w-full"
          >
            <template #value="slotProps">
              <div class="flex items-center" v-if="slotProps.value">
                <i class="pi pi-chart-line mr-2"></i>
                {{ difficultyOptions.find(opt => opt.value === slotProps.value)?.label || slotProps.value }}
              </div>
              <span v-else>Sélectionner un niveau de difficulté</span>
            </template>
            <template #option="slotProps">
              <div class="flex items-center">
                <i class="pi pi-chart-line mr-2"></i>
                {{ slotProps.option.label }}
              </div>
            </template>
          </Select>
        </div>
        
        <!-- Image Upload -->
        <div class="space-y-1">
          <label for="image" class="text-sm font-medium text-surface-700 dark:text-surface-300">Image</label>
          <div class="flex flex-col gap-2">
            <div v-if="course.image || imagePreview" class="mb-2">
              <img 
                :src="imagePreview || getImageUrl(course.image)" 
                :alt="course.title" 
                class="h-32 object-cover rounded" 
              />
            </div>
            <FileUpload
              mode="basic"
              accept="image/*"
              :maxFileSize="1000000"
              @select="onImageSelect"
              :auto="true"
              chooseLabel="Sélectionner une image"
              class="w-full"
            />
            <small class="text-surface-500">Formats acceptés: JPG, PNG, GIF. Taille max: 1MB</small>
          </div>
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
            @click="saveCourse"
          />
        </div>
      </template>
    </Dialog>

    <!-- Course View Dialog -->
    <Dialog 
      v-model:visible="viewDialog" 
      :style="{ width: '600px' }" 
      header="Détails du cours" 
      :modal="true"
      class="rounded-lg shadow-lg"
    >
      <div v-if="selectedCourse" class="space-y-4">
        <div class="flex justify-center mb-4">
          <img 
            v-if="selectedCourse.image" 
            :src="getImageUrl(selectedCourse.image)" 
            :alt="selectedCourse.title" 
            class="h-48 object-cover rounded-lg shadow-md" 
          />
          <div 
            v-else 
            class="h-48 w-full flex items-center justify-center bg-surface-100 dark:bg-surface-800 rounded-lg"
          >
            <i class="pi pi-image text-4xl text-surface-400"></i>
          </div>
        </div>
        
        <div class="flex flex-col gap-3">
          <div>
            <h3 class="text-lg font-semibold text-surface-800 dark:text-surface-100">{{ selectedCourse.title }}</h3>
            <p v-if="selectedCourse.author" class="text-sm text-surface-600 dark:text-surface-400">
              Par: {{ selectedCourse.author }}
            </p>
            <p v-if="selectedCourse.colorside" class="text-sm text-surface-600 dark:text-surface-400 flex items-center mt-1">
              <span class="mr-2">Côté:</span>
              <i :class="`pi pi-circle-fill mr-1 ${selectedCourse.colorside === 'W' ? 'text-white border border-gray-400 rounded-full' : 'text-black'}`"></i>
              {{ selectedCourse.colorside === 'W' ? 'Blanc' : 'Noir' }}
            </p>
            <p v-if="selectedCourse?.difficulty" class="text-sm text-surface-600 dark:text-surface-400 flex items-center mt-1">
              <span class="mr-2">Difficulté:</span>
              <i class="pi pi-chart-line mr-1"></i>
              {{ difficultyOptions.find(opt => opt.value === selectedCourse?.difficulty)?.label || selectedCourse?.difficulty }}
            </p>
          </div>
          
          <div class="mt-2">
            <p class="text-sm text-surface-600 dark:text-surface-400">
              <i class="pi pi-calendar mr-2"></i>
              Créé le {{ formatDate(selectedCourse.createdat) }}
            </p>
            <p v-if="selectedCourse.updatedat" class="text-sm text-surface-600 dark:text-surface-400">
              <i class="pi pi-clock mr-2"></i>
              Mis à jour le {{ formatDate(selectedCourse.updatedat) }}
            </p>
          </div>
          
          <div v-if="selectedCourse.description" class="mt-4 p-3 bg-surface-100 dark:bg-surface-800 rounded">
            <p class="text-surface-700 dark:text-surface-300 whitespace-pre-line">{{ selectedCourse.description }}</p>
          </div>
          
          <div v-if="selectedCourse.chapters && selectedCourse.chapters.length > 0" class="mt-4">
            <h4 class="text-md font-medium text-surface-800 dark:text-surface-200 mb-2">Chapitres ({{ selectedCourse.chapters.length }})</h4>
            <ul class="list-disc pl-5 space-y-1">
              <li v-for="chapter in selectedCourse.chapters" :key="chapter.id" class="text-surface-700 dark:text-surface-300">
                {{ chapter.title }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </Dialog>

    <!-- Delete Course Confirmation -->
    <Dialog 
      v-model:visible="deleteCourseDialog" 
      :style="{ width: '450px' }" 
      header="Confirmer la suppression" 
      :modal="true"
      class="rounded-lg shadow-lg"
    >
      <div class="flex items-center justify-center p-4">
        <i class="pi pi-exclamation-triangle text-yellow-500 mr-3 text-3xl" />
        <span v-if="course" class="text-surface-700 dark:text-surface-300">
          Êtes-vous sûr de vouloir supprimer le cours <b>{{ course.title }}</b> ?
        </span>
      </div>
      <template #footer>
        <div class="flex justify-end space-x-2 pt-4 border-t border-surface-200 dark:border-surface-700">
          <Button 
            label="Non" 
            icon="pi pi-times" 
            class="p-button-text" 
            @click="deleteCourseDialog = false"
          />
          <Button 
            label="Oui" 
            icon="pi pi-check" 
            class="p-button-danger" 
            @click="deleteCourse"
          />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useRouter } from 'vue-router';
import { useCourseService, type Course } from '../composables/useCourseService';
import { API_PATHS, API_BASE_URL } from '../config/api';

// PrimeVue components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Select from 'primevue/select';

const toast = useToast();
const router = useRouter();
const { 
  fetchCourses, 
  fetchCourseById,
  createCourse, 
  updateCourse,
  uploadCourseImage,
  deleteCourseById 
} = useCourseService();

// Data
const courses = ref<Course[]>([]);
const course = ref<Course>({
  id: undefined,
  title: '',
  description: '',
  author: '',
  colorside: '',
  difficulty: null,
  image: ''
});
const selectedCourse = ref<Course | null>(null);
const courseDialog = ref(false);
const viewDialog = ref(false);
const deleteCourseDialog = ref(false);
const submitted = ref(false);
const loading = ref(false);
const imageFile = ref<File | null>(null);
const imagePreview = ref<string | null>(null);

// Search functionality
const searchQuery = ref('');
const allCourses = ref<Course[]>([]);

// Chess sides
const chessSides = [
  { label: 'Blanc', value: 'W' },
  { label: 'Noir', value: 'B' }
];

// Difficulty levels
const difficultyOptions = [
  { label: 'Facile', value: 'easy' },
  { label: 'Intermédiaire', value: 'intermediate' },
  { label: 'Avancé', value: 'advanced' },
  { label: 'Expert', value: 'expert' }
];

// Lifecycle hooks
onMounted(() => {
  loadCourses();
});

// Methods
const loadCourses = async (): Promise<void> => {
  loading.value = true;
  try {
    allCourses.value = await fetchCourses();
    applyFilters();
  } catch (error: any) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: error.message || 'Impossible de charger les cours', life: 3000 });
  } finally {
    loading.value = false;
  }
};

const getImageUrl = (imagePath: string | undefined): string => {
  // Handle undefined case
  if (!imagePath) return '';
  
  // If it's already a full URL, return it
  if (imagePath.startsWith('http')) {
    return imagePath;
  }
  
  // API Platform may return paths like /api/files/... so we need to extract the filename
  if (imagePath.includes('/')) {
    const parts = imagePath.split('/');
    imagePath = parts[parts.length - 1];
  }
  
  // Use the API configuration from our config file
  return `${API_PATHS.IMAGES.COURSES}${imagePath}`;
};

const formatDate = (dateString: string | undefined): string => {
  if (!dateString) return 'N/A';
  
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date);
};

const onImageSelect = (event: any): void => {
  const file = event.files[0];
  if (file) {
    // Log file details when selected
    console.log('Image selected:', {
      name: file.name,
      type: file.type,
      size: file.size,
      lastModified: new Date(file.lastModified).toISOString()
    });
    
    // Store the file directly on the course object
    course.value.imageFile = file;
    
    // Create preview for display only
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string;
      // Verify data was loaded
      console.log('Preview created successfully, length:', 
        (e.target?.result as string)?.length || 0);
    };
    reader.onerror = (e) => {
      console.error('Error creating preview:', e);
      toast.add({ 
        severity: 'error', 
        summary: 'Erreur', 
        detail: 'Impossible de prévisualiser l\'image', 
        life: 3000 
      });
    };
    reader.readAsDataURL(file);
  } else {
    console.warn('No file selected or event.files is empty');
  }
};

const openNewCourseDialog = (): void => {
  course.value = {
    title: '',
    description: '',
    author: '',
    colorside: '',
    difficulty: null,
    image: ''
  };
  submitted.value = false;
  imagePreview.value = null;
  courseDialog.value = true;
};

const viewCourse = async (courseData: Course): Promise<void> => {
  try {
    // If we need full details for viewing, fetch the complete course
    selectedCourse.value = await fetchCourseById(courseData.id!);
    viewDialog.value = true;
  } catch (error) {
    // If fetch fails, at least show what we have
    selectedCourse.value = courseData;
    viewDialog.value = true;
  }
};

const editCourse = (courseData: Course): void => {
  course.value = { ...courseData };
  imagePreview.value = null;
  courseDialog.value = true;
};

const manageChapters = (courseData: Course): void => {
  if (courseData.id) {
    router.push({ name: 'Chapters', params: { courseId: courseData.id } });
  } else {
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur', 
      detail: 'ID du cours manquant', 
      life: 3000 
    });
  }
};

const hideDialog = (): void => {
  courseDialog.value = false;
  submitted.value = false;
};

const saveCourse = async (): Promise<void> => {
  submitted.value = true;
  
  if (!course.value.title) {
    return;
  }
  
  try {
    // Step 1: Save course data (JSON)
    // Extract the image file and keep it separate from the JSON data
    const { imageFile, ...courseData } = course.value;
    let savedCourse: Course;
    
    if (course.value.id) {
      // Update existing course (JSON data only)
      savedCourse = await updateCourse(courseData);
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Cours mis à jour', life: 3000 });
    } else {
      // Create new course (JSON data only)
      savedCourse = await createCourse(courseData);
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Cours créé', life: 3000 });
    }
    
    // Step 2: If there's an image file, upload it in a separate request
    if (imageFile && savedCourse.id) {
      try {
        // Verify the file is valid
        if (!(imageFile instanceof File)) {
          throw new Error('Invalid file object');
        }
        
        // Additional verification and debug
        if (imageFile.size === 0) {
          throw new Error('File has zero size');
        }
        
        // Log for debugging
        console.log('Uploading image for course:', savedCourse.id);
        console.log('File details:', {
          name: imageFile.name,
          type: imageFile.type,
          size: imageFile.size,
          lastModified: new Date(imageFile.lastModified).toISOString()
        });
        
        // Use the dedicated endpoint for VichUploader
        await uploadCourseImage(savedCourse.id, imageFile);
        toast.add({ 
          severity: 'info', 
          summary: 'Image téléchargée', 
          detail: 'L\'image du cours a été mise à jour avec succès', 
          life: 3000 
        });
      } catch (imageError: any) {
        // If image upload fails, the course is still saved
        console.error('Image upload failed:', imageError.response?.data || imageError.message);
        toast.add({ 
          severity: 'warn', 
          summary: 'Attention', 
          detail: 'Cours enregistré, mais l\'image n\'a pas pu être téléchargée: ' + 
                  (imageError.response?.data?.error || imageError.message), 
          life: 5000 
        });
      }
    }
    
    // Close dialog and refresh course list
    courseDialog.value = false;
    loadCourses();
  } catch (error: any) {
    console.error('Course save error:', error.response?.data || error.message);
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur', 
      detail: error.message || 'Impossible de sauvegarder le cours', 
      life: 3000 
    });
  }
};

const confirmDeleteCourse = (courseData: Course): void => {
  course.value = courseData;
  deleteCourseDialog.value = true;
};

const deleteCourse = async (): Promise<void> => {
  try {
    if (course.value.id) {
      await deleteCourseById(course.value.id);
      deleteCourseDialog.value = false;
      loadCourses();
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Cours supprimé', life: 3000 });
    }
  } catch (error: any) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: error.message || 'Impossible de supprimer le cours', life: 3000 });
  }
};

const onSearchChange = () => {
  applyFilters();
};

const applyFilters = () => {
  if (!searchQuery.value.trim()) {
    courses.value = [...allCourses.value];
    return;
  }
  
  const query = searchQuery.value.toLowerCase().trim();
  courses.value = allCourses.value.filter(course => 
    (course.title?.toLowerCase().includes(query)) || 
    (course.description?.toLowerCase().includes(query)) ||
    (course.author?.toLowerCase().includes(query))
  );
};

// Watch for search query changes
watch(searchQuery, () => {
  applyFilters();
});
</script> 