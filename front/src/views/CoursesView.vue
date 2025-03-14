<template>
    <div class="container mx-auto px-4 py-8">
        <!-- Search and filters section -->
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
                    <input type="text" v-model="searchTerm" placeholder="Rechercher des cours..."
                        class="w-full pl-10 pr-8 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-nav-start)] focus:border-transparent" />
                    <button @click="searchTerm = ''" v-if="searchTerm"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                </div>

                <!-- Filter dropdown -->
                <div class="relative">
                    <button @click="showFilters = !showFilters"
                        class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 flex items-center">
                        <span>Filtrer</span>
                        <span class="ml-2">↓</span>
                    </button>

                    <!-- Filter dropdown menu -->
                    <div v-if="showFilters"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg z-10 p-4 border border-gray-200">
                        <h3 class="font-semibold mb-2">Difficulté</h3>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="filters.difficulty.easy" class="mr-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-2" title="Facile"></span>
                                <span>Facile</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="filters.difficulty.intermediate" class="mr-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 mr-2" title="Intermédiaire"></span>
                                <span>Intermédiaire</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="filters.difficulty.advanced" class="mr-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500 mr-2" title="Difficile"></span>
                                <span>Difficile</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="filters.difficulty.expert" class="mr-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-black mr-2" title="Expert"></span>
                                <span>Expert</span>
                            </label>
                        </div>

                        <div class="mt-4 flex justify-between">
                            <button @click="resetFilters"
                                class="text-sm px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">
                                Réinitialiser
                            </button>
                            <button @click="showFilters = false"
                                class="text-sm px-3 py-1 bg-[var(--color-nav-start)] text-white rounded hover:bg-[var(--color-nav-hover)]">
                                Appliquer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Applied filters display -->
            <div v-if="hasActiveFilters" class="flex flex-wrap gap-2">
                <div v-if="searchTerm" class="flex items-center bg-gray-100 px-3 py-1 rounded-full text-sm">
                    <span>Recherche: {{ searchTerm }}</span>
                    <button @click="searchTerm = ''" class="ml-2 text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div v-if="filters.difficulty.easy"
                    class="flex items-center bg-gray-100 px-3 py-1 rounded-full text-sm">
                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                    <span>Facile</span>
                    <button @click="filters.difficulty.easy = false"
                        class="ml-2 text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div v-if="filters.difficulty.intermediate"
                    class="flex items-center bg-gray-100 px-3 py-1 rounded-full text-sm">
                    <span class="w-2 h-2 rounded-full bg-blue-500 mr-2"></span>
                    <span>Intermédiaire</span>
                    <button @click="filters.difficulty.intermediate = false"
                        class="ml-2 text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div v-if="filters.difficulty.advanced"
                    class="flex items-center bg-gray-100 px-3 py-1 rounded-full text-sm">
                    <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>
                    <span>Difficile</span>
                    <button @click="filters.difficulty.advanced = false"
                        class="ml-2 text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div v-if="filters.difficulty.expert"
                    class="flex items-center bg-gray-100 px-3 py-1 rounded-full text-sm">
                    <span class="w-2 h-2 rounded-full bg-black mr-2"></span>
                    <span>Expert</span>
                    <button @click="filters.difficulty.expert = false"
                        class="ml-2 text-gray-500 hover:text-gray-700">✕</button>
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

        <!-- No courses message -->
        <div v-else-if="!filteredCourses.length" class="text-center py-12">
            <p class="text-gray-600 text-lg">Aucune leçon disponible pour le moment.</p>
        </div>

        <!-- Courses grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <Card v-for="course in filteredCourses" :key="course.id" layout="chess" variant="bordered" elevation="sm"
                className="h-auto">
                <!-- Course image (left side) -->
                <template #image>
                    <img v-if="course.image" :src="getCourseImageUrl(course.image)" :alt="course.title"
                        class="w-full h-full object-cover">
                    <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
                        <span class="text-gray-500">No image</span>
                    </div>
                </template>

                <!-- Indicators (difficulty) -->
                <template #indicators>
                    <!-- Hardcoded difficulty indicators -->
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500" title="Facile"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500" title="Intermédiaire"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500" title="Difficile"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-black" title="Expert"></span>
                </template>

                <!-- Title -->
                <template #title>
                    <h2 class="text-base font-semibold text-gray-800">{{ course.title }}</h2>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ course.chapters ? course.chapters.length : 0 }} chapitre{{ course.chapters &&
                            course.chapters.length !== 1 ? 's' : '' }}
                    </div>

                    <div class="text-xs text-gray-500">
                        <div v-if="course.updatedat">Mis à jour le {{ formatDate(course.updatedat) }}</div>
                    </div>
                </template>

                <!-- Color side -->
                <template #colorside>
                    <span class="text-xs text-gray-500">
                        <span v-if="course.colorside && course.colorside.toString().toLowerCase() === 'w'">Pour les
                            blancs</span>
                        <span v-else-if="course.colorside && course.colorside.toString().toLowerCase() === 'b'">Pour les
                            noirs</span>
                        <span v-else>Pour tous</span>
                    </span>
                </template>

                <!-- Author -->
                <template #author>
                    <span v-if="course.author" class="text-xs text-gray-500">Par {{ course.author }}</span>
                    <span v-else class="text-xs text-gray-500">Auteur inconnu</span>
                </template>

                <!-- Description (body slot) -->
                <div>
                    <p class="line-clamp-2 mb-2">{{ course.description || 'Aucune description disponible.' }}</p>
                </div>

                <!-- Action buttons -->
                <div class="mt-4 flex border-t border-gray-200 pt-4">
                    <button @click="toggleFollowCourse(course)"
                        class="flex-1 flex justify-center items-center gap-1 text-sm py-2 rounded-md" :class="isFollowingCourse(course)
                            ? 'text-green-600 hover:bg-green-50'
                            : 'text-blue-600 hover:bg-blue-50'">
                        <CheckIcon v-if="isFollowingCourse(course)" class="h-4 w-4" />
                        <PlusIcon v-else class="h-4 w-4" />
                        <span>{{ isFollowingCourse(course) ? 'Suivi' : 'Suivre' }}</span>
                    </button>
                    <router-link v-if="isFollowingCourse(course)" to="/my-learning"
                        class="flex-1 flex justify-center items-center gap-1 text-sm py-2 text-gray-700 hover:bg-gray-50 rounded-md">
                        <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                        <span>Apprendre</span>
                    </router-link>
                    <router-link v-else :to="`/lessons/${course.id}`"
                        class="flex-1 flex justify-center items-center gap-1 text-sm py-2 text-gray-700 hover:bg-gray-50 rounded-md">
                        <InformationCircleIcon class="h-4 w-4" />
                        <span>Détails</span>
                    </router-link>
                </div>
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import coursesService from '../services/courses.service'
import type { Course, UserCourse } from '../services/courses.service'
import { useAuth } from '../composables/useAuth'
import { useAssets } from '../composables/useAssets'
import Card from '../components/ui/Card.vue'

import {
    PlusIcon,
    CheckIcon,
    InformationCircleIcon,
    ArrowTopRightOnSquareIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const { user } = useAuth()
const { getCourseImageUrl } = useAssets()

const courses = ref<Course[]>([])
const userCourses = ref<UserCourse[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

// Search and filter state
const searchTerm = ref('')
const showFilters = ref(false)
const filters = ref({
    difficulty: {
        easy: false,
        intermediate: false,
        advanced: false,
        expert: false
    }
})

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return searchTerm.value ||
        filters.value.difficulty.easy ||
        filters.value.difficulty.intermediate ||
        filters.value.difficulty.advanced ||
        filters.value.difficulty.expert
})

// Reset all filters
const resetFilters = () => {
    searchTerm.value = ''
    filters.value.difficulty.easy = false
    filters.value.difficulty.intermediate = false
    filters.value.difficulty.advanced = false
    filters.value.difficulty.expert = false
}

// Filtered courses based on search and filters
const filteredCourses = computed(() => {
    let result = [...courses.value]

    // Apply search filter
    if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase()
        result = result.filter(course =>
            (course.title && course.title.toLowerCase().includes(term)) ||
            (course.description && course.description.toLowerCase().includes(term)) ||
            (course.author && course.author.toLowerCase().includes(term))
        )
    }

    // Apply difficulty filters (this is a simplified approach since your API might not have difficulty info yet)
    const anyDifficultyFilterActive = filters.value.difficulty.easy ||
        filters.value.difficulty.intermediate ||
        filters.value.difficulty.advanced ||
        filters.value.difficulty.expert

    if (anyDifficultyFilterActive) {
        // This is a placeholder - in a real application, you'd filter based on actual difficulty data
        // For now, we'll use a simple approach of evenly dividing courses between the difficulty levels
        result = result.filter(course => {
            // Simple mock implementation - in practice, replace with real logic when API provides difficulty
            const id = course.id
            if (filters.value.difficulty.easy && id % 4 === 0) return true
            if (filters.value.difficulty.intermediate && id % 4 === 1) return true
            if (filters.value.difficulty.advanced && id % 4 === 2) return true
            if (filters.value.difficulty.expert && id % 4 === 3) return true
            return !anyDifficultyFilterActive
        })
    }

    return result
})

// Format date for display
const formatDate = (dateString: string) => {
    if (!dateString) return 'Date inconnue'

    const date = new Date(dateString)
    return new Intl.DateTimeFormat('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(date)
}

// Check if the user is following a course
const isFollowingCourse = (course: Course) => {
    return userCourses.value.some(uc =>
        uc.courseid.id === course.id &&
        uc.userid.id === user.value?.id
    )
}

// Get the userCourse object for a course if it exists
const getUserCourse = (course: Course) => {
    return userCourses.value.find(uc =>
        uc.courseid.id === course.id &&
        uc.userid.id === user.value?.id
    )
}

// Toggle following a course
const toggleFollowCourse = async (course: Course) => {
    // Set loading state for this specific action
    const actionLoading = ref(true);
    const courseId = course.id;

    try {
        if (isFollowingCourse(course)) {
            // Unfollow course
            const userCourse = getUserCourse(course);
            if (userCourse) {
                console.log(`Attempting to unfollow course: ${course.title} (ID: ${course.id})`);

                await coursesService.removeCourseFromUser(userCourse.id);
                console.log('Course unfollowed successfully');

                // Update local state
                userCourses.value = userCourses.value.filter(uc => uc.id !== userCourse.id);
            }
        } else {
            // Follow course
            console.log(`Attempting to follow course: ${course.title} (ID: ${course.id})`);

            const newUserCourse = await coursesService.addCourseToUser(course.id);
            console.log('Course followed successfully:', newUserCourse);

            userCourses.value.push(newUserCourse);
        }
    } catch (err: any) {
        // Detailed error handling
        let errorMessage = 'Une erreur est survenue.';

        if (err.response) {
            if (err.response.status === 500) {
                errorMessage = 'Erreur serveur. Veuillez contacter l\'administrateur.';
                console.error('Server error details:', err.response.data);
            } else if (err.response.status === 415) {
                errorMessage = 'Erreur de format de données. Veuillez réessayer.';
            } else if (err.response.status === 403) {
                errorMessage = 'Vous n\'avez pas la permission de suivre ce cours.';
            } else if (err.response.data?.error) {
                errorMessage = err.response.data.error;
            } else if (err.response.data?.['hydra:description']) {
                errorMessage = err.response.data['hydra:description'];
            }
        } else if (err.message) {
            errorMessage = err.message;
        }

        console.error(`Error ${isFollowingCourse(course) ? 'unfollowing' : 'following'} course:`, err);
        error.value = errorMessage;

        // Auto clear error after some time
        setTimeout(() => {
            error.value = null;
        }, 5000);
    } finally {
        actionLoading.value = false;
    }
}

// Fetch data
const fetchData = async () => {
    loading.value = true
    error.value = null

    try {
        // Get all courses
        const coursesData = await coursesService.getAllCourses()
        console.log('Fetched courses:', coursesData);

        // Debug colorside values
        coursesData.forEach((course: Course) => {
            console.log(`Course "${course.title}" colorside: "${course.colorside}" (type: ${typeof course.colorside})`);
        });

        courses.value = coursesData

        // Get user courses
        const userCoursesData = await coursesService.getUserCourses()
        userCourses.value = userCoursesData
    } catch (err: any) {
        error.value = err.message || 'Une erreur est survenue lors du chargement des cours.'
        console.error('Error fetching data:', err)
    } finally {
        loading.value = false
    }
}

onMounted(fetchData)
</script>