<template>
    <div class="container mx-auto px-4 py-8">
        <!-- Search bar -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <!-- Search input -->
                <div class="relative md:max-w-md flex-grow">
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path
                                d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                        </svg>
                    </span>
                    <input type="text" v-model="searchTerm" placeholder="Rechercher dans vos leçons suivies..."
                        class="w-full pl-10 pr-8 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-nav-start)] focus:border-transparent" />
                    <button @click="searchTerm = ''" v-if="searchTerm"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Applied filters display -->
            <div v-if="searchTerm" class="flex flex-wrap gap-2">
                <div class="flex items-center bg-gray-100 px-3 py-1 rounded-full text-sm">
                    <span>Recherche: {{ searchTerm }}</span>
                    <button @click="searchTerm = ''" class="ml-2 text-gray-500 hover:text-gray-700">✕</button>
                </div>
            </div>
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

        <!-- No followed courses message -->
        <div v-else-if="!userCourses.length" class="text-center py-12 bg-gray-50 rounded-lg p-8">
            <div class="mx-auto h-48 mb-4 opacity-70 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="120" height="120" fill="none"
                    stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
                    class="text-gray-400">
                    <!-- Chess Knight SVG path -->
                    <path
                        d="M8,16L10,16L11,15L11,13C11,13 11,12 10,11C9,10 9,9 9,9C11,9 12,10 13,10C14,10 14,9 14,7C15,7 15,6 15,5C15,4 14,3 14,3C14,3 13.5,5 12,5C12,5 10.5,5 10,3C10,3 9,5 8,6C7,7 5,7 5,10C5,13 8,13 8,15C8,16 8,16 8,16Z"
                        fill="currentColor"></path>
                    <path
                        d="M8,16L10,16L11,15L11,13C11,13 11,12 10,11C9,10 9,9 9,9C11,9 12,10 13,10C14,10 14,9 14,7C15,7 15,6 15,5C15,4 14,3 14,3C14,3 13.5,5 12,5C12,5 10.5,5 10,3C10,3 9,5 8,6C7,7 5,7 5,10C5,13 8,13 8,15C8,16 8,16 8,16Z"
                        stroke="currentColor" stroke-width="0.5"></path>
                    <path d="M7,21V17H17V21" stroke="currentColor" fill="none"></path>
                    <path d="M5,21H19" stroke="currentColor" fill="none"></path>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-700 mb-2">Votre bibliothèque d'échecs est vide !</h2>
            <p class="text-gray-600 mb-6">Vous n'avez pas encore de leçons dans votre espace d'apprentissage.
                Les grands maîtres ont tous commencé quelque part !</p>
            <router-link to="/lessons"
                class="inline-flex items-center px-4 py-2 bg-[var(--color-nav-start)] text-white rounded-md hover:bg-[var(--color-nav-hover)] shadow-sm">
                <span class="mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                </span>
                Découvrir les leçons
            </router-link>
        </div>

        <!-- No results found for search term -->
        <div v-else-if="userCourses.length && !filteredCourses.length"
            class="text-center py-12 bg-gray-50 rounded-lg p-8">
            <div class="mx-auto h-24 mb-4 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-16 h-16 text-gray-400">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-700 mb-2">Aucun résultat trouvé</h2>
            <p class="text-gray-600 mb-6">Aucun cours ne correspond à votre recherche "{{ searchTerm }}".</p>
            <button @click="searchTerm = ''"
                class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 shadow-sm">
                Effacer la recherche
            </button>
        </div>

        <!-- Courses grid -->
        <div v-else>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800">{{ filteredCourses.length }} cours suivi{{
                    filteredCourses.length > 1 ? 's' : '' }}</h2>
                <div>
                    <button @click="viewMode = 'grid'" class="px-2 py-1 rounded-l-md"
                        :class="viewMode === 'grid' ? 'bg-gray-200' : 'bg-gray-100'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                    </button>
                    <button @click="viewMode = 'list'" class="px-2 py-1 rounded-r-md"
                        :class="viewMode === 'list' ? 'bg-gray-200' : 'bg-gray-100'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Grid View -->
            <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Card v-for="userCourse in filteredCourses" :key="userCourse.id" layout="chess" variant="bordered"
                    elevation="sm" className="h-auto">
                    <!-- Course image (left side) -->
                    <template #image>
                        <img v-if="userCourse.courseid.image" :src="getCourseImageUrl(userCourse.courseid.image)"
                            :alt="userCourse.courseid.title" class="w-full h-full object-cover">
                        <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
                            <span class="text-gray-500">No image</span>
                        </div>
                    </template>

                    <!-- Indicators -->
                    <template #indicators>
                        <!-- Progress indicator -->
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-xs font-semibold text-blue-700">
                                    {{ Math.round(userCourse.completionPercentage || 0) }}%
                                </span>
                            </div>

                            <!-- Difficulty indicator -->
                            <div class="mt-2 mb-1">
                                <template v-if="userCourse.courseid.difficulty">
                                    <!-- Easy -->
                                    <span v-if="userCourse.courseid.difficulty === 'easy'"
                                        class="w-2.5 h-2.5 rounded-full bg-green-500 block" title="Facile"></span>
                                    <!-- Intermediate -->
                                    <span v-else-if="userCourse.courseid.difficulty === 'intermediate'"
                                        class="w-2.5 h-2.5 rounded-full bg-blue-500 block" title="Intermédiaire"></span>
                                    <!-- Advanced -->
                                    <span v-else-if="userCourse.courseid.difficulty === 'advanced'"
                                        class="w-2.5 h-2.5 rounded-full bg-red-500 block" title="Difficile"></span>
                                    <!-- Expert -->
                                    <span v-else-if="userCourse.courseid.difficulty === 'expert'"
                                        class="w-2.5 h-2.5 rounded-full bg-black block" title="Expert"></span>
                                </template>
                            </div>

                            <button
                                class="mt-1 text-xs text-gray-500 hover:text-red-500 flex items-center transition-colors"
                                title="Ne plus suivre" @click.prevent="removeCourse(userCourse)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-3 h-3 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="hidden sm:inline text-[10px]">Retirer</span>
                            </button>
                        </div>
                    </template>

                    <!-- Title -->
                    <template #title>
                        <h2 class="text-base font-semibold text-gray-800">{{ userCourse.courseid.title }}</h2>
                        <div class="flex items-center text-xs text-gray-500 mt-1">
                            <span class="flex items-center">
                                <DocumentTextIcon class="h-3 w-3 mr-1" />
                                {{ getChapterText(userCourse.courseid) }}
                            </span>
                        </div>
                    </template>

                    <!-- Color side -->
                    <template #colorside>
                        <span class="text-xs text-gray-500">
                            <span
                                v-if="userCourse.courseid.colorside && userCourse.courseid.colorside.toString().toLowerCase() === 'w'">Pour
                                les
                                blancs</span>
                            <span
                                v-else-if="userCourse.courseid.colorside && userCourse.courseid.colorside.toString().toLowerCase() === 'b'">Pour
                                les
                                noirs</span>
                            <span v-else>Pour tous</span>
                        </span>
                    </template>

                    <!-- Author -->
                    <template #author>
                        <span v-if="userCourse.courseid.author" class="text-xs text-gray-500">Par {{
                            userCourse.courseid.author }}</span>
                        <span v-else class="text-xs text-gray-500">Auteur inconnu</span>
                    </template>

                    <!-- Description -->
                    <div>
                        <div class="mb-3">
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="bg-blue-600 h-1.5 rounded-full"
                                    :style="{ width: `${Math.round(userCourse.completionPercentage || 0)}%` }"></div>
                            </div>
                        </div>
                        <p class="line-clamp-2 mb-2">{{ userCourse.courseid.description ||
                            "Aucune description disponible" }}</p>
                    </div>

                    <!-- Action buttons -->
                    <div class="mt-4 grid grid-cols-3 gap-1 border-t border-gray-200 pt-4">
                        <router-link :to="`/my-lessons/${userCourse.courseid.id}/chapters`"
                            class="flex justify-center items-center gap-1 text-sm py-2 rounded-md text-[var(--color-nav-start)] hover:bg-[var(--color-light-pgn)]">
                            <DocumentTextIcon class="h-4 w-4" />
                            <span class="hidden sm:inline">Chapitres</span>
                        </router-link>
                        <button
                            class="flex justify-center items-center gap-1 text-sm py-2 rounded-md text-gray-600 hover:bg-gray-100">
                            <AcademicCapIcon class="h-4 w-4" />
                            <span class="hidden sm:inline">Test</span>
                        </button>
                        <button
                            class="flex justify-center items-center gap-1 text-sm py-2 rounded-md text-green-600 hover:bg-green-50">
                            <PlayIcon class="h-4 w-4" />
                            <span class="hidden sm:inline">Continuer</span>
                        </button>
                    </div>
                </Card>
            </div>

            <!-- List View -->
            <div v-else class="space-y-4">
                <div v-for="userCourse in filteredCourses" :key="userCourse.id"
                    class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row">
                        <!-- Image -->
                        <div class="md:w-48 h-40 md:h-auto flex-shrink-0">
                            <img v-if="userCourse.courseid.image" :src="getCourseImageUrl(userCourse.courseid.image)"
                                :alt="userCourse.courseid.title" class="w-full h-full object-cover">
                            <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
                                <span class="text-gray-500">No image</span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-4 flex-grow">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800">{{ userCourse.courseid.title }}</h2>
                                    <div class="flex items-center text-xs text-gray-500 mt-1">
                                        <span class="flex items-center">
                                            <DocumentTextIcon class="h-3 w-3 mr-1" />
                                            {{ getChapterText(userCourse.courseid) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center shrink-0 ml-2">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="flex flex-col items-center justify-center bg-blue-100 rounded-full w-10 h-10 md:w-12 md:h-12">
                                            <span class="text-xs md:text-sm font-bold text-blue-700">
                                                {{ Math.round(userCourse.completionPercentage || 0) }}%
                                            </span>
                                        </div>

                                        <!-- Difficulty indicator -->
                                        <div class="mt-2 mb-1">
                                            <template v-if="userCourse.courseid.difficulty">
                                                <!-- Easy -->
                                                <span v-if="userCourse.courseid.difficulty === 'easy'"
                                                    class="w-2.5 h-2.5 rounded-full bg-green-500 block"
                                                    title="Facile"></span>
                                                <!-- Intermediate -->
                                                <span v-else-if="userCourse.courseid.difficulty === 'intermediate'"
                                                    class="w-2.5 h-2.5 rounded-full bg-blue-500 block"
                                                    title="Intermédiaire"></span>
                                                <!-- Advanced -->
                                                <span v-else-if="userCourse.courseid.difficulty === 'advanced'"
                                                    class="w-2.5 h-2.5 rounded-full bg-red-500 block"
                                                    title="Difficile"></span>
                                                <!-- Expert -->
                                                <span v-else-if="userCourse.courseid.difficulty === 'expert'"
                                                    class="w-2.5 h-2.5 rounded-full bg-black block"
                                                    title="Expert"></span>
                                            </template>
                                        </div>

                                        <button
                                            class="mt-1 text-xs text-gray-500 hover:text-red-500 flex items-center transition-colors"
                                            title="Ne plus suivre" @click.prevent="removeCourse(userCourse)">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-3 h-3 mr-1">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            <span class="text-[10px]">Retirer</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                {{ userCourse.courseid.description || 'Aucune description disponible' }}
                            </p>

                            <div class="mb-4">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full"
                                        :style="{ width: `${Math.round(userCourse.completionPercentage || 0)}%` }">
                                    </div>
                                </div>
                            </div>

                            <!-- Action buttons -->
                            <div class="flex space-x-2">
                                <router-link :to="`/my-lessons/${userCourse.courseid.id}/chapters`"
                                    class="flex justify-center items-center gap-1 px-4 py-2 text-sm rounded-md border border-[var(--color-nav-start)] text-[var(--color-nav-start)] hover:bg-[var(--color-light-pgn)]">
                                    <DocumentTextIcon class="h-4 w-4" />
                                    <span>Chapitres</span>
                                </router-link>
                                <button
                                    class="flex justify-center items-center gap-1 px-4 py-2 text-sm rounded-md border border-gray-300 text-gray-600 hover:bg-gray-50">
                                    <AcademicCapIcon class="h-4 w-4" />
                                    <span>Test</span>
                                </button>
                                <button
                                    class="flex justify-center items-center gap-1 px-4 py-2 text-sm rounded-md bg-green-600 text-white hover:bg-green-700">
                                    <PlayIcon class="h-4 w-4" />
                                    <span>Continuer</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, onActivated } from 'vue';
import { useRouter } from 'vue-router';
import coursesService from '../services/courses.service';
import type { UserCourse, Course } from '../services/courses.service';
import { useAssets } from '../composables/useAssets';
import Card from '../components/ui/Card.vue';
import {
    DocumentTextIcon,
    AcademicCapIcon,
    PlayIcon
} from '@heroicons/vue/24/outline';

const router = useRouter();
const { getCourseImageUrl } = useAssets();

// State
const userCourses = ref<UserCourse[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const viewMode = ref<'grid' | 'list'>('grid');
const searchTerm = ref('');

// Helper function to get chapter text with pluralization
const getChapterText = (course: Course): string => {
    const count = course.chapters ? course.chapters.length : 0;
    return `${count} chapitre${count !== 1 ? 's' : ''}`;
};

// Format date
const formatDate = (dateString: string): string => {
    if (!dateString) return 'Date inconnue';

    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(date);
};

// Remove a course from followed courses
const removeCourse = async (userCourse: UserCourse) => {
    if (!confirm('Êtes-vous sûr de vouloir retirer ce cours de vos leçons suivies ?')) {
        return;
    }

    try {
        await coursesService.removeCourseFromUser(userCourse.id);
        userCourses.value = userCourses.value.filter(uc => uc.id !== userCourse.id);
    } catch (err: any) {
        error.value = err.message || 'Une erreur est survenue lors de la suppression du cours.';
        console.error('Error removing course:', err);

        // Auto clear error after some time
        setTimeout(() => {
            error.value = null;
        }, 5000);
    }
};

// Fetch user courses
const fetchUserCourses = async () => {
    loading.value = true;
    error.value = null;

    try {
        const userCoursesData = await coursesService.getUserCourses();
        userCourses.value = userCoursesData;
        console.log('Fetched user courses:', userCoursesData);
    } catch (err: any) {
        error.value = err.message || 'Une erreur est survenue lors du chargement de vos cours.';
        console.error('Error fetching user courses:', err);
    } finally {
        loading.value = false;
    }
};

// Filtered courses based on search term
const filteredCourses = computed(() => {
    if (!searchTerm.value) {
        return userCourses.value;
    }

    const term = searchTerm.value.toLowerCase();
    return userCourses.value.filter(userCourse => {
        const course = userCourse.courseid;
        return (course.title && course.title.toLowerCase().includes(term)) ||
            (course.description && course.description.toLowerCase().includes(term)) ||
            (course.author && course.author.toLowerCase().includes(term));
    });
});

// Fetch data when the component is activated (coming back to this route)
onActivated(() => {
    console.log('MyLessonsView activated - refreshing data')
    fetchUserCourses()
})

onMounted(fetchUserCourses);
</script>