<template>
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb navigation -->
        <div class="flex items-center text-sm mb-6">
            <router-link to="/my-lessons" class="text-gray-500 hover:text-[var(--color-nav-start)]">Mes
                leçons</router-link>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-700">{{ course?.title || 'Détails du cours' }}</span>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-700 font-medium">Chapitres</span>
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

        <!-- Course and Chapter content -->
        <div v-else-if="course">
            <!-- Course header -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <div class="md:flex">
                    <!-- Course image -->
                    <div class="md:w-1/3 lg:w-1/4 h-48 md:h-auto">
                        <img v-if="course.image" :src="getCourseImageUrl(course.image)" :alt="course.title"
                            class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
                            <span class="text-gray-500">No image</span>
                        </div>
                    </div>

                    <!-- Course info -->
                    <div class="p-6 flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ course.title }}</h1>
                                <div class="flex items-center text-gray-600 mb-4">
                                    <span v-if="course.author" class="flex items-center mr-4">
                                        <UserIcon class="h-4 w-4 mr-1" />
                                        {{ course.author }}
                                    </span>
                                    <span class="flex items-center">
                                        <DocumentTextIcon class="h-4 w-4 mr-1" />
                                        {{ chapters.length }} chapitre{{ chapters.length !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                                <p class="text-gray-600 mb-4">{{ course.description }}</p>
                            </div>
                            <div class="flex flex-col items-end">
                                <div class="flex" v-if="userCourse">
                                    <div class="flex items-center justify-center bg-blue-100 rounded-full w-16 h-16">
                                        <span class="text-sm font-bold text-blue-700">
                                            {{ Math.round(userCourse.completionPercentage || 0) }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chapters list -->
            <div class="my-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Liste des chapitres</h2>

                <div v-if="!chapters.length" class="text-center py-8 bg-gray-50 rounded-lg">
                    <p class="text-gray-600">Ce cours ne contient pas encore de chapitres.</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="(chapter, index) in chapters" :key="chapter.id"
                        class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-all">
                        <div class="flex items-center p-4">
                            <!-- Chapter number circle -->
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-full bg-[var(--color-nav-start)] text-white flex items-center justify-center mr-4">
                                <span class="font-semibold">{{ index + 1 }}</span>
                            </div>

                            <!-- Chapter content -->
                            <div class="flex-grow">
                                <h3 class="text-lg font-semibold text-gray-800">{{ chapter.title }}</h3>
                                <p class="text-gray-600 text-sm">
                                    {{ chapter.title || 'Aucun titre disponible' }}
                                </p>
                            </div>

                            <!-- Completed indicator and action button -->
                            <div class="flex flex-col items-end ml-4">
                                <div v-if="isChapterCompleted(chapter)" class="text-green-600 flex items-center mb-2">
                                    <CheckCircleIcon class="h-5 w-5 mr-1" />
                                    <span class="text-sm">Complété</span>
                                </div>
                                <button
                                    class="px-4 py-2 bg-[var(--color-nav-start)] text-white rounded-md hover:bg-[var(--color-nav-hover)] text-sm"
                                    @click="startChapter(chapter)">
                                    <span v-if="isChapterCompleted(chapter)">Réviser</span>
                                    <span v-else>Commencer</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course not found -->
        <div v-else class="text-center py-12">
            <p class="text-gray-600 text-lg">Cours non trouvé.</p>
            <button
                class="mt-4 px-4 py-2 bg-[var(--color-nav-start)] text-white rounded-md hover:bg-[var(--color-nav-hover)]"
                @click="router.push('/my-lessons')">
                Retour à mes leçons
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import coursesService from '../services/courses.service';
import type { Course, UserCourse, Chapter } from '../services/courses.service';
import { useAssets } from '../composables/useAssets';
import { useAuth } from '../composables/useAuth';
import { DocumentTextIcon, UserIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

const route = useRoute();
const router = useRouter();
const { user } = useAuth();
const { getCourseImageUrl } = useAssets();

// State
const courseId = computed(() => {
    const id = route.params.id;
    if (!id) return null;
    const parsedId = parseInt(id.toString());
    return isNaN(parsedId) ? null : parsedId;
});
const course = ref<Course | null>(null);
const chapters = ref<Chapter[]>([]);
const userCourse = ref<UserCourse | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);
const isRedirecting = ref(false);

// Check if a chapter is completed
const isChapterCompleted = (chapter: Chapter) => {
    if (!userCourse.value || !userCourse.value.completedChapters) {
        return false;
    }
    return userCourse.value.completedChapters.includes(String(chapter.id));
};

// Start a chapter (navigate to chapter content/learning view)
const startChapter = (chapter: Chapter) => {
    router.push({
        name: 'ChapterView',
        params: {
            courseId: courseId.value,
            chapterId: chapter.id
        }
    });
};

// Fetch course and chapter data
const fetchData = async () => {
    // Skip fetching if we don't have a valid course ID
    if (!courseId.value || isNaN(courseId.value) || isRedirecting.value) {
        console.error('Invalid course ID or redirect in progress, cannot fetch course data:', route.params.id);
        error.value = 'ID de cours invalide';
        loading.value = false;
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        // Get course details
        const courseData = await coursesService.getCourse(courseId.value);
        course.value = courseData;

        // Use our new endpoint to check if following
        const followStatus = await coursesService.getFollowingStatus(courseId.value);

        if (followStatus.following) {
            // Get chapters using our specialized endpoint
            const chaptersData = await coursesService.getCourseChapters(courseId.value);
            chapters.value = chaptersData;

            // Get user course details
            const userCourses = await coursesService.getUserCourses();
            const foundUserCourse = userCourses.find((uc: UserCourse) =>
                uc.courseid.id === courseId.value
            );

            if (foundUserCourse) {
                userCourse.value = foundUserCourse;
            }
        } else {
            // User is not following this course, redirect to my-lessons
            error.value = "Vous devez suivre ce cours pour accéder à ses chapitres.";
            isRedirecting.value = true; // Mark that we're redirecting
            setTimeout(() => {
                router.push('/my-lessons');
            }, 2000);
            return;
        }
    } catch (err: any) {
        // Only set error if we're not redirecting
        if (!isRedirecting.value) {
            error.value = err.message || 'Une erreur est survenue lors du chargement du cours.';
            console.error('Error fetching course data:', err);
        }
    } finally {
        loading.value = false;
    }
};

// Reset redirect flag when component is unmounted
onMounted(() => {
    isRedirecting.value = false;
    fetchData();
});

// Also reset the redirect flag when route changes
watch(() => route.params.id, (newId, oldId) => {
    if (newId !== oldId) {
        isRedirecting.value = false;

        // Only fetch if we have a valid ID - skip when navigating away (newId is undefined)
        if (newId && !isNaN(Number(newId))) {
            fetchData();
        } else {
            // Reset state when navigating away
            course.value = null;
            chapters.value = [];
            userCourse.value = null;
            error.value = null;
        }
    }
});

// Redirect to lessons page if course ID is invalid
watch(courseId, (newId) => {
    if (newId === null) {
        console.error('Invalid course ID, redirecting to my lessons page');
        error.value = 'ID de cours invalide';
        isRedirecting.value = true;
        setTimeout(() => {
            router.push('/my-lessons');
        }, 1000);
    }
}, { immediate: true });
</script>