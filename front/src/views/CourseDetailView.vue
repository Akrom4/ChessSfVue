<template>
    <div class="container mx-auto px-4 py-8">
        <!-- Loading state -->
        <div v-if="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[var(--color-nav-start)]">
            </div>
        </div>

        <div v-else-if="error"
            class="bg-red-100 border border-red-400 text-[var(--color-nav-start)] px-4 py-3 rounded relative mb-6">
            <span class="block sm:inline">{{ error }}</span>
        </div>

        <template v-else-if="course">
            <!-- Breadcrumb navigation -->
            <div class="flex items-center text-sm mb-6">
                <router-link to="/lessons"
                    class="text-gray-500 hover:text-[var(--color-nav-start)]">Leçons</router-link>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-700 font-medium">{{ course.title }}</span>
            </div>

            <!-- Course detail card - Header section similar to course cards -->
            <Card layout="chess" variant="bordered" elevation="md" className="mb-8 max-w-4xl mx-auto">
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
                    <h1 class="text-xl font-semibold text-gray-800">{{ course.title }}</h1>
                    <div class="text-sm text-gray-500 mt-1">
                        {{ chapters.length }} chapitre{{ chapters.length !== 1 ? 's' : '' }}
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

                <!-- Course content - Full description -->
                <div>
                    <p class="mb-4">{{ course.description || 'Aucune description disponible.' }}</p>

                    <div class="flex items-center mb-2 text-xs text-gray-500">
                        <CalendarIcon class="h-4 w-4 mr-1" />
                        <span>Créé le {{ formatDate(course.createdat) }}</span>
                    </div>

                    <div class="text-xs text-gray-500 flex items-center">
                        <DocumentTextIcon class="h-4 w-4 mr-1" />
                        <span>{{ chapters.length }} chapitre{{ chapters.length !== 1 ? 's' : '' }} disponible{{
                            chapters.length !== 1 ?
                                's' : '' }}</span>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="mt-4 flex border-t border-gray-200 pt-4">
                    <button @click="toggleFollowCourse"
                        class="flex-1 flex justify-center items-center gap-1 text-sm py-2 rounded-md" :class="isFollowing
                            ? 'text-green-600 hover:bg-green-50'
                            : 'text-blue-600 hover:bg-blue-50'">
                        <CheckIcon v-if="isFollowing" class="h-4 w-4" />
                        <PlusIcon v-else class="h-4 w-4" />
                        <span>{{ isFollowing ? 'Suivi' : 'Suivre' }}</span>
                    </button>
                    <router-link v-if="isFollowing" to="/my-learning"
                        class="flex-1 flex justify-center items-center gap-1 text-sm py-2 text-gray-700 hover:bg-gray-50 rounded-md">
                        <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                        <span>Accéder à l'espace d'apprentissage</span>
                    </router-link>
                    <button v-else disabled
                        class="flex-1 flex justify-center items-center gap-1 text-sm py-2 text-gray-400 bg-gray-50 rounded-md cursor-not-allowed">
                        <InformationCircleIcon class="h-4 w-4" />
                        <span>Suivre pour accéder au cours</span>
                    </button>
                </div>
            </Card>

            <!-- Course overview section -->
            <div class="mt-8 max-w-4xl mx-auto">
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">À propos de ce cours</h2>

                    <div class="space-y-4">
                        <div class="flex items-start">
                            <DocumentTextIcon class="h-5 w-5 text-[var(--color-nav-start)] mt-0.5 mr-3" />
                            <div>
                                <h3 class="font-medium text-gray-800">Structure du cours</h3>
                                <p class="text-gray-600">Ce cours contient {{ chapters.length }} chapitre{{
                                    chapters.length !== 1 ?
                                        's' : '' }}. Suivez ce cours pour y accéder dans votre espace d'apprentissage
                                    personnel.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <UserIcon class="h-5 w-5 text-[var(--color-nav-start)] mt-0.5 mr-3" />
                            <div>
                                <h3 class="font-medium text-gray-800">Auteur</h3>
                                <p class="text-gray-600">{{ course.author || 'Auteur non spécifié' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <AcademicCapIcon class="h-5 w-5 text-[var(--color-nav-start)] mt-0.5 mr-3" />
                            <div>
                                <h3 class="font-medium text-gray-800">Pour qui est ce cours</h3>
                                <p v-if="course.colorside && course.colorside.toString().toLowerCase() === 'w'"
                                    class="text-gray-600">Ce répertoire est destiné aux blancs.</p>
                                <p v-else-if="course.colorside && course.colorside.toString().toLowerCase() === 'b'"
                                    class="text-gray-600">Ce répertoire est destiné aux noirs.</p>
                                <p v-else class="text-gray-600">Ce répertoire est destiné aux deux côtés.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div v-else class="text-center py-12">
            <p class="text-gray-600 text-lg">Cours non trouvé.</p>
            <Button variant="primary" @click="router.push('/lessons')" class="mt-4">
                Retour à la liste des cours
            </Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import coursesService from '../services/courses.service'
import type { Course, UserCourse, Chapter } from '../services/courses.service'
import { useAuth } from '../composables/useAuth'
import { useAssets } from '../composables/useAssets'
import Card from '../components/ui/Card.vue'
import Button from '../components/ui/Button.vue'
import {
    PlusIcon,
    CheckIcon,
    CalendarIcon,
    DocumentTextIcon,
    UserIcon,
    AcademicCapIcon,
    ArrowTopRightOnSquareIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const { user } = useAuth()
const { getCourseImageUrl } = useAssets()

const courseId = computed(() => parseInt(route.params.id as string))
const course = ref<Course | null>(null)
const chapters = ref<Chapter[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const isFollowing = ref(false)
const userCourseId = ref<number | null>(null)

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

// Toggle following the course
const toggleFollowCourse = async () => {
    // Set loading state for this action
    const actionInProgress = ref(true);

    try {
        if (isFollowing.value && userCourseId.value) {
            // Unfollow course
            console.log(`Attempting to unfollow course: ${course.value?.title} (ID: ${course.value?.id})`);

            await coursesService.removeCourseFromUser(userCourseId.value);
            console.log('Course unfollowed successfully');

            isFollowing.value = false;
            userCourseId.value = null;
        } else if (course.value) {
            // Follow course
            console.log(`Attempting to follow course: ${course.value.title} (ID: ${course.value.id})`);

            const userCourse = await coursesService.addCourseToUser(course.value.id);
            console.log('Course followed successfully:', userCourse);

            isFollowing.value = true;
            userCourseId.value = userCourse.id;
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

        console.error(`Error ${isFollowing.value ? 'unfollowing' : 'following'} course:`, err);
        error.value = errorMessage;

        // Auto clear error after some time
        setTimeout(() => {
            error.value = null;
        }, 5000);
    } finally {
        actionInProgress.value = false;
    }
}

// Fetch course data
const fetchCourseData = async () => {
    loading.value = true
    error.value = null

    try {
        // Get course details
        const courseData = await coursesService.getCourse(courseId.value)
        course.value = courseData

        // Check if user is following this course
        const userCourses = await coursesService.getUserCourses()
        const userCourse = userCourses.find((uc: UserCourse) =>
            uc.courseid.id === courseId.value &&
            uc.userid.id === user.value?.id
        )

        if (userCourse) {
            isFollowing.value = true
            userCourseId.value = userCourse.id
        }

        // Get course chapters
        const chaptersData = await coursesService.getCourseChapters(courseId.value)
        chapters.value = chaptersData
    } catch (err: any) {
        error.value = err.message || 'Une erreur est survenue lors du chargement du cours.'
        console.error('Error fetching course data:', err)
    } finally {
        loading.value = false
    }
}

onMounted(fetchCourseData)
</script>

<style scoped>
/* Remove custom CSS, as we now use Tailwind classes directly */
</style>