<template>
    <div id="chess-view">
        <!-- Breadcrumb navigation -->
        <div class="w-full flex items-center text-sm mb-6 px-4">
            <router-link to="/my-lessons" class="text-gray-500 hover:text-[var(--color-nav-start)]">
                Mes leçons
            </router-link>
            <span class="mx-2 text-gray-400">/</span>
            <template v-if="courseId">
                <router-link :to="{
                    name: 'Chapters',
                    params: { id: courseId }
                }" class="text-gray-500 hover:text-[var(--color-nav-start)]">
                    {{ courseTitle || 'Chapitres' }}
                </router-link>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-700 font-medium">{{ chapterTitle || 'Chapitre' }}</span>
            </template>
            <template v-else>
                <span class="text-gray-700 font-medium">Chapitres</span>
            </template>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[var(--color-nav-start)]">
            </div>
        </div>

        <!-- Error message -->
        <div v-else-if="error"
            class="bg-red-100 border border-red-400 text-[var(--color-nav-start)] px-4 py-3 rounded relative mb-6">
            <span class="block sm:inline">{{ error }}</span>
        </div>

        <!-- Use the chessboard component when data is loaded -->
        <div v-else class="chess-app-wrapper">
            <ChessApp :chapterData="chapterData"
                :key="`chess-${route.params.courseId}-${route.params.chapterId}-${Date.now()}`" />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ChessApp from '../chess/ChessApp.vue';
import coursesService from '../services/courses.service';

const route = useRoute();
const router = useRouter();

// State
const loading = ref(true);
const error = ref(null);
const chapterData = ref(null);
const courseTitle = ref(null);
const chapterTitle = ref(null);

// Get the chapter and course IDs from the route
const courseId = computed(() => {
    const id = parseInt(route.params.courseId);
    return isNaN(id) ? null : id;
});
const chapterId = computed(() => {
    const id = parseInt(route.params.chapterId);
    return isNaN(id) ? null : id;
});

// Fetch chapter data
const fetchChapterData = async () => {
    console.log('Fetching data for course:', courseId.value, 'chapter:', chapterId.value);

    if (!courseId.value || !chapterId.value) {
        error.value = 'Paramètres de chapitre manquants ou invalides';
        loading.value = false;
        return;
    }

    loading.value = true;
    error.value = null;
    chapterData.value = null; // Reset data before fetching new data

    try {
        // Get course details for the breadcrumb
        const courseData = await coursesService.getCourse(courseId.value);
        courseTitle.value = courseData.title;

        // Verify following status
        const followStatus = await coursesService.getFollowingStatus(courseId.value);
        if (!followStatus.following) {
            error.value = 'Vous devez suivre ce cours pour accéder à ce chapitre.';
            setTimeout(() => {
                router.push('/my-lessons');
            }, 2000);
            return;
        }

        // Fetch the single chapter directly instead of loading all chapters
        const chapter = await coursesService.getChapter(courseId.value, chapterId.value);

        if (!chapter) {
            error.value = 'Chapitre non trouvé';
            loading.value = false;
            return;
        }

        // Set chapter title for breadcrumb
        chapterTitle.value = chapter.title || `Chapitre ${chapterId.value}`;

        // Process chapter data for ChessApp
        chapterData.value = {
            FEN: chapter.pgndata?.[0]?.FEN || "",
            Moves: chapter.pgndata?.[0]?.Moves || [],
            Title: chapter.title || "Chapitre sans titre",
            Number: chapter.id,
            Comments: chapter.pgndata?.[0]?.Comments || [],
            Variations: chapter.pgndata?.[0]?.Variations || []
        };

        console.log('Successfully set new chapter data:', chapterData.value?.Title);
    } catch (err) {
        console.error('Error fetching chapter data:', err);

        // Set generic error message (auth errors will be handled by interceptor and router guard)
        error.value = err.message || 'Une erreur est survenue lors du chargement du chapitre.';
    } finally {
        loading.value = false;
    }
};

onMounted(fetchChapterData);

// Watch directly the route object for changes
watch(() => route.params, (newParams) => {
    console.log('Route params changed:', newParams);
    fetchChapterData();
}, { deep: true });
</script>

<style scoped>
#chess-view {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
    padding: 1rem;
    width: 100%;
    min-height: 100%;
}

.chess-app-wrapper {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

/* Mobile responsive styling */
@media (max-width: 768px) {
    #chess-view {
        padding: 0.5rem;
    }

    .chess-app-wrapper {
        width: 100%;
        aspect-ratio: 1/1;
        max-width: 80vmin;
        margin: 0 auto;
    }

    :deep(.chess-app-wrapper #chessboard) {
        width: 100% !important;
        height: 100% !important;
        max-width: 100%;
    }

    :deep(.chess-app-wrapper #chess-app) {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    :deep(.chess-app-wrapper) {
        max-width: 95vmin;
    }
}
</style>
