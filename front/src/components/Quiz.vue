<script setup lang="ts">
import type { Quiz } from '../types/quizTypes';
import { ref, computed } from 'vue';
import Progress from './Progress.vue';
import Question from './Question.vue';

const props = defineProps<{
    quiz: Quiz | null;
}>();

const step = ref(0);
const score = ref(0);
const currentQuestion = computed(() => props.quiz?.questions[step.value]);

const handleAnswer = (choice: string) => {
    if (choice === currentQuestion.value?.correct_answer) {
        score.value++;
    }
    step.value++;
};

const isQuizFinished = computed(() => step.value >= (props.quiz?.questions.length ?? 0));
</script>

<template>
    <div>
        <h1>{{ quiz?.title }}</h1>
        <Progress :current="step" :total="(quiz?.questions?.length ?? 0) - 1" />
        <div v-if="!isQuizFinished">
            <Question :key="step" v-if="currentQuestion" :question="currentQuestion" @answer="handleAnswer" />
        </div>
        <div v-else>
            <h2>Quiz Finished!</h2>
            <p>Your score: {{ score }} / {{ quiz?.questions.length }}</p>
        </div>
    </div>
</template>
