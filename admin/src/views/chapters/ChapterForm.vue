<template>
  <div class="p-4 bg-surface-50 dark:bg-surface-900 rounded-lg shadow">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-xl font-semibold text-surface-800 dark:text-surface-100">
        {{ isEditMode ? 'Modifier le chapitre' : 'Importer des chapitres PGN' }}
      </h2>
      <Button 
        label="Retour aux chapitres" 
        icon="pi pi-arrow-left"
        class="p-button-outlined" 
        @click="navigateBack"
      />
    </div>

    <!-- Edit Mode Form -->
    <form v-if="isEditMode" @submit.prevent="saveChapter" class="space-y-6">
      <!-- Title Field -->
      <div class="space-y-1">
        <label for="title" class="text-sm font-medium text-surface-700 dark:text-surface-300">Titre</label>
        <InputText 
          id="title" 
          v-model.trim="chapter.title" 
          required="true" 
          autofocus 
          class="w-full"
          :class="{'p-invalid': submitted && !chapter.title}"
        />
        <small v-if="submitted && !chapter.title" class="text-red-500 text-xs">Le titre est requis.</small>
      </div>
      
      <!-- PGN Import Options -->
      <div class="space-y-1 pt-4 border-t border-surface-200 dark:border-surface-700">
        <h3 class="text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">Notation PGN</h3>
        
        <div class="mb-3">
          <div class="p-3 border border-dashed border-surface-300 dark:border-surface-600 rounded-lg">
            <FileUpload
              mode="basic"
              :auto="true"
              accept=".pgn,text/plain"
              :maxFileSize="1000000"
              chooseLabel="Importer un fichier PGN"
              class="p-button-outlined w-full"
              @select="onEditModeFileSelect"
              @error="onFileUploadError"
              :customUpload="true"
              :showUploadButton="false"
            />
            <small class="block mt-2 text-surface-500">Fichiers .pgn ou .txt, max 1MB</small>
          </div>
        </div>
        
        <!-- Raw PGN Field -->
        <Textarea 
          id="rawpgn" 
          v-model="chapter.rawpgn" 
          rows="10"
          autoResize
          class="w-full font-mono text-sm"
          placeholder="Collez votre notation PGN ici..."
        />
        <small class="text-surface-500">
          Entrez la notation PGN de la partie d'échecs. Ce texte sera utilisé pour générer les données d'affichage.
        </small>
      </div>
      
      <!-- Buttons -->
      <div class="flex justify-end space-x-3 pt-4 border-t border-surface-200 dark:border-surface-700">
        <Button 
          type="button"
          label="Annuler" 
          icon="pi pi-times" 
          class="p-button-text" 
          @click="navigateBack"
        />
        <Button 
          type="submit"
          label="Enregistrer" 
          icon="pi pi-check" 
          class="p-button-primary" 
          :loading="loading"
        />
      </div>
    </form>

    <!-- Create Mode Form with PGN Input Options -->
    <div v-else class="space-y-6">
      <!-- Input Method Selection -->
      <div class="mb-4">
        <h3 class="text-lg font-semibold text-surface-700 dark:text-surface-300 mb-2">
          Méthode d'import PGN
        </h3>
        <div class="flex gap-4">
          <div 
            class="flex-1 p-4 border rounded-lg cursor-pointer transition-colors"
            :class="{
              'border-primary-500 bg-primary-50 dark:bg-primary-900/20': pgnInputMethod === 'text',
              'border-surface-200 dark:border-surface-700 hover:border-primary-300': pgnInputMethod !== 'text'
            }"
            @click="pgnInputMethod = 'text'"
          >
            <div class="flex justify-center mb-2">
              <i class="pi pi-align-left text-xl text-primary-500"></i>
            </div>
            <div class="text-center text-surface-700 dark:text-surface-300">Copier-coller du texte PGN</div>
          </div>
          <div 
            class="flex-1 p-4 border rounded-lg cursor-pointer transition-colors"
            :class="{
              'border-primary-500 bg-primary-50 dark:bg-primary-900/20': pgnInputMethod === 'file',
              'border-surface-200 dark:border-surface-700 hover:border-primary-300': pgnInputMethod !== 'file'
            }"
            @click="pgnInputMethod = 'file'"
          >
            <div class="flex justify-center mb-2">
              <i class="pi pi-file text-xl text-primary-500"></i>
            </div>
            <div class="text-center text-surface-700 dark:text-surface-300">Charger un fichier PGN</div>
          </div>
        </div>
      </div>

      <!-- Text Input Method -->
      <div v-if="pgnInputMethod === 'text'" class="space-y-4">
        <div class="space-y-1">
          <label for="pgnText" class="text-sm font-medium text-surface-700 dark:text-surface-300">Texte PGN</label>
          <Textarea 
            id="pgnText" 
            v-model="pgnText" 
            rows="10"
            autoResize
            class="w-full font-mono text-sm"
            placeholder="Collez votre notation PGN ici. Vous pouvez inclure plusieurs parties..."
          />
          <small class="text-surface-500">
            Entrez une ou plusieurs notations PGN. Chaque partie sera automatiquement transformée en chapitre.
          </small>
        </div>
        <Button 
          label="Importer les parties" 
          icon="pi pi-upload" 
          class="p-button-primary" 
          @click="importPgn"
          :disabled="!pgnText.trim()"
          :loading="loading"
        />
      </div>

      <!-- File Input Method -->
      <div v-else-if="pgnInputMethod === 'file'" class="space-y-4">
        <div class="space-y-1">
          <label for="pgnFile" class="text-sm font-medium text-surface-700 dark:text-surface-300">Fichier PGN</label>
          <div class="p-4 border border-dashed border-surface-300 dark:border-surface-600 rounded-lg">
            <FileUpload
              mode="basic"
              :auto="true"
              accept=".pgn,text/plain"
              :maxFileSize="1000000"
              chooseLabel="Choisir un fichier PGN"
              class="p-button-outlined w-full"
              @select="onFileSelect"
              @error="onFileUploadError"
              :customUpload="true"
              :showUploadButton="false"
            />
            <small class="block mt-2 text-surface-500">Fichiers .pgn ou .txt, max 1MB</small>
          </div>
          <div v-if="selectedFileName" class="mt-2 text-sm text-primary-600 dark:text-primary-400">
            <i class="pi pi-file mr-2"></i>{{ selectedFileName }}
          </div>
        </div>
        <Button 
          label="Importer le fichier" 
          icon="pi pi-upload" 
          class="p-button-primary" 
          @click="importPgnFile"
          :disabled="!selectedFile"
          :loading="loading"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import { useChapterService, type Chapter } from '../../composables/useChapterService';
import { useCourseService } from '../../composables/useCourseService';
import { usePgnParser } from '../../composables/usePgnParser';

// PrimeVue components
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';

const toast = useToast();
const route = useRoute();
const router = useRouter();
const { 
  fetchChapterById, 
  createChapter, 
  updateChapter,
  loading: serviceLoading,
  error
} = useChapterService();
const { fetchCourseById } = useCourseService();
const { parseChapters } = usePgnParser();

// Data
const chapter = ref<Chapter>({
  title: '',
  rawpgn: '',
  pgndata: []
});
const courseId = ref<number | null>(null);
const chapterId = ref<number | null>(null);
const courseName = ref('');
const submitted = ref(false);
const loading = computed(() => serviceLoading.value);

// PGN Input
const pgnInputMethod = ref<'text' | 'file'>('text');
const pgnText = ref('');
const selectedFile = ref<File | null>(null);
const selectedFileName = ref('');

// Determine if we're in edit mode
const isEditMode = computed(() => !!chapterId.value);

// When the component mounts, check if we're editing and load data if needed
onMounted(async () => {
  // Get course ID from route
  const routeCourseId = route.params.courseId as string;
  if (routeCourseId) {
    courseId.value = parseInt(routeCourseId, 10);
    await loadCourse();
  } else {
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur', 
      detail: 'ID du cours manquant', 
      life: 3000 
    });
    navigateBack();
    return;
  }
  
  // Check if we're in edit mode
  const routeChapterId = route.params.chapterId as string;
  if (routeChapterId) {
    chapterId.value = parseInt(routeChapterId, 10);
    await loadChapter();
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

// Load chapter data if in edit mode
const loadChapter = async (): Promise<void> => {
  if (!chapterId.value) return;
  
  try {
    const data = await fetchChapterById(chapterId.value);
    chapter.value = data;
  } catch (error: any) {
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur', 
      detail: error.message || 'Impossible de charger le chapitre', 
      life: 3000 
    });
  }
};

// File upload handlers
const onFileSelect = (event: any) => {
  selectedFile.value = event.files[0];
  selectedFileName.value = event.files[0].name;
};

// Handle file uploads in edit mode
const onEditModeFileSelect = async (event: any) => {
  const file = event.files[0];
  
  try {
    // Read the file content
    const fileContent = await readFileAsText(file);
    
    // Update the chapter's PGN content
    chapter.value.rawpgn = fileContent;
    
    toast.add({ 
      severity: 'success', 
      summary: 'Succès', 
      detail: `Fichier PGN "${file.name}" importé`, 
      life: 3000 
    });
  } catch (error: any) {
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur', 
      detail: error.message || 'Impossible de lire le fichier PGN', 
      life: 3000 
    });
  }
};

const onFileUploadError = (event: any) => {
  toast.add({ 
    severity: 'error', 
    summary: 'Erreur', 
    detail: 'Erreur lors du chargement du fichier: ' + event.error, 
    life: 3000 
  });
};

// Read file as text
const readFileAsText = (file: File): Promise<string> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      if (e.target?.result) {
        resolve(e.target.result as string);
      } else {
        reject(new Error('Failed to read file'));
      }
    };
    reader.onerror = (e) => {
      reject(new Error('Error reading file'));
    };
    reader.readAsText(file);
  });
};

// Define interfaces for parsed PGN data
interface PgnChapter {
  // Original format properties from the parser
  FEN?: string;
  Title?: string;
  Number?: number;
  Moves?: any[];
  Comments?: any[];
  Variations?: any[];
  // Additional properties for our form
  title?: string;
  rawpgn?: string;
  pgndata?: any[];
}

interface ChaptersParserResult {
  chapters: PgnChapter[];
  rawChapterTexts: string[];
}

// Convert PGN chapter format to our Chapter format
const convertPgnChapterToChapter = (pgnChapter: PgnChapter, rawChapterText: string, courseId: number | null): Chapter => {
  console.log('Converting PGN chapter:', pgnChapter);
  
  // Use the Title property from the PGN parser first, then fall back to title (lowercase)
  const chapterTitle = pgnChapter.Title || pgnChapter.title || 'Chapitre sans titre';
  
  return {
    title: chapterTitle,
    rawpgn: rawChapterText,
    // Store the complete PGN data structure
    pgndata: [{
      // Include the full structured data
      FEN: pgnChapter.FEN || '',
      Title: pgnChapter.Title || '',
      Number: pgnChapter.Number || 0,
      Moves: pgnChapter.Moves || [],
      Comments: pgnChapter.Comments || [],
      Variations: pgnChapter.Variations || []
    }],
    course: courseId ? `/api/courses/${courseId}` : undefined
  };
};

// Import PGN from text
const importPgn = async (): Promise<void> => {
  if (!pgnText.value.trim()) {
    toast.add({ 
      severity: 'warn', 
      summary: 'Attention', 
      detail: 'Veuillez entrer du texte PGN', 
      life: 3000 
    });
    return;
  }

  if (!courseId.value) {
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur', 
      detail: 'ID du cours manquant', 
      life: 3000 
    });
    return;
  }

  try {
    console.log('Starting PGN parsing...');
    
    // Use the new parseChapters method that doesn't create a Course
    const result = parseChapters(pgnText.value) as ChaptersParserResult;
    
    if (!result || !result.chapters) {
      console.error('PGN parser returned null or invalid data');
      toast.add({ 
        severity: 'error', 
        summary: 'Erreur', 
        detail: 'Impossible de parser le PGN', 
        life: 3000 
      });
      return;
    }
    
    const { chapters, rawChapterTexts } = result;
    
    if (chapters.length === 0) {
      console.error('No chapters found in parsed PGN');
      toast.add({ 
        severity: 'error', 
        summary: 'Erreur', 
        detail: 'Aucun chapitre valide trouvé dans le PGN', 
        life: 3000 
      });
      return;
    }

    // Log information about the parsed chapters and raw texts
    console.log(`Found ${chapters.length} chapters and ${rawChapterTexts.length} raw texts`);
    
    // Create chapters from the parsed PGN data
    const createdChapters: Chapter[] = [];
    
    for (let i = 0; i < chapters.length; i++) {
      const parsedChapter = chapters[i];
      const rawChapterText = i < rawChapterTexts.length ? rawChapterTexts[i] : '';
      
      console.log(`Creating chapter: ${parsedChapter.title}`);
      
      // Create a new chapter entity using the mapping function
      const chapterData = convertPgnChapterToChapter(
        parsedChapter,
        rawChapterText,
        courseId.value
      );
      
      // Create the chapter in the database
      const createdChapter = await createChapter(chapterData);
      createdChapters.push(createdChapter);
      console.log(`Chapter created with ID: ${createdChapter.id}, raw PGN length: ${chapterData.rawpgn?.length || 0} chars`);
    }
    
    toast.add({ 
      severity: 'success', 
      summary: 'Succès', 
      detail: `${createdChapters.length} chapitres ont été créés`, 
      life: 3000 
    });
    
    // Navigate back to chapters list
    navigateBack();
  } catch (error: any) {
    console.error('Error processing PGN:', error);
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur', 
      detail: error.message || 'Impossible de traiter le PGN', 
      life: 3000 
    });
  }
};

// Import PGN from file - same approach as importPgn but with file input
const importPgnFile = async (): Promise<void> => {
  if (!selectedFile.value) {
    toast.add({ 
      severity: 'warn', 
      summary: 'Attention', 
      detail: 'Veuillez sélectionner un fichier', 
      life: 3000 
    });
    return;
  }

  try {
    // Read the file content
    console.log(`Reading file: ${selectedFile.value.name}`);
    const fileContent = await readFileAsText(selectedFile.value);
    console.log(`File content loaded (${fileContent.length} characters)`);
    
    // Set the text content (for debugging purposes)
    pgnText.value = fileContent;
    
    // Process the PGN directly instead of using importPgn
    if (!courseId.value) {
      toast.add({ 
        severity: 'error', 
        summary: 'Erreur', 
        detail: 'ID du cours manquant', 
        life: 3000 
      });
      return;
    }

    console.log('Starting PGN parsing from file...');
    
    // Use the new parseChapters method that doesn't create a Course
    const result = parseChapters(fileContent) as ChaptersParserResult;
    
    if (!result || !result.chapters) {
      console.error('PGN parser returned null or invalid data');
      toast.add({ 
        severity: 'error', 
        summary: 'Erreur', 
        detail: 'Impossible de parser le fichier PGN', 
        life: 3000 
      });
      return;
    }
    
    const { chapters, rawChapterTexts } = result;
    
    if (chapters.length === 0) {
      console.error('No chapters found in parsed PGN file');
      toast.add({ 
        severity: 'error', 
        summary: 'Erreur', 
        detail: 'Aucun chapitre valide trouvé dans le fichier PGN', 
        life: 3000 
      });
      return;
    }

    // Log information about the parsed chapters and raw texts
    console.log(`Found ${chapters.length} chapters and ${rawChapterTexts.length} raw texts`);
    
    // Create chapters from the parsed PGN data
    const createdChapters: Chapter[] = [];
    
    for (let i = 0; i < chapters.length; i++) {
      const parsedChapter = chapters[i];
      const rawChapterText = i < rawChapterTexts.length ? rawChapterTexts[i] : '';
      
      console.log(`Creating chapter from file: ${parsedChapter.title}`);
      
      // Create a new chapter entity using the mapping function
      const chapterData = convertPgnChapterToChapter(
        parsedChapter,
        rawChapterText,
        courseId.value
      );
      
      // Create the chapter in the database
      const createdChapter = await createChapter(chapterData);
      createdChapters.push(createdChapter);
      console.log(`Chapter created with ID: ${createdChapter.id}, raw PGN length: ${chapterData.rawpgn?.length || 0} chars`);
    }
    
    toast.add({ 
      severity: 'success', 
      summary: 'Succès', 
      detail: `${createdChapters.length} chapitres ont été créés à partir du fichier`, 
      life: 3000 
    });
    
    // Navigate back to chapters list
    navigateBack();
  } catch (error: any) {
    console.error('Error processing PGN file:', error);
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur', 
      detail: error.message || 'Impossible de traiter le fichier PGN', 
      life: 3000 
    });
  }
};

// Save the chapter (create or update) in edit mode
const saveChapter = async (): Promise<void> => {
  submitted.value = true;
  
  if (!chapter.value.title) {
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur de validation', 
      detail: 'Veuillez remplir tous les champs obligatoires', 
      life: 3000 
    });
    return;
  }
  
  try {
    // Prepare the chapter data
    const chapterData: Chapter = {
      ...chapter.value,
      course: courseId.value ? `/api/courses/${courseId.value}` : undefined
    };
    
    if (isEditMode.value) {
      // Update existing chapter
      await updateChapter(chapterData);
      toast.add({ 
        severity: 'success', 
        summary: 'Succès', 
        detail: 'Chapitre mis à jour', 
        life: 3000 
      });
    } else {
      // Create new chapter
      await createChapter(chapterData);
      toast.add({ 
        severity: 'success', 
        summary: 'Succès', 
        detail: 'Chapitre créé', 
        life: 3000 
      });
    }
    
    // Navigate back to chapters list
    navigateBack();
  } catch (error: any) {
    toast.add({ 
      severity: 'error', 
      summary: 'Erreur', 
      detail: error.message || 'Impossible de sauvegarder le chapitre', 
      life: 3000 
    });
  }
};

// Navigate back to the chapters list
const navigateBack = (): void => {
  if (courseId.value) {
    router.push({ 
      name: 'Chapters',
      params: { courseId: courseId.value }
    });
  } else {
    router.push({ name: 'Courses' });
  }
};
</script> 