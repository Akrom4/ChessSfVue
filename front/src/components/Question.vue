<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import type { Question } from '../types/quizTypes';

const props = defineProps<{
    question: Question;
}>();

// Define the emit function
const emit = defineEmits(['answer']);

const selectedChoice = ref<string | null>(null);
const shuffledChoices = ref<string[]>([]);

// Function to shuffle an array
function shuffleArray(array: string[]) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}

// Shuffle choices when the component is mounted or when the question changes
const shuffleChoices = () => {
    if (props.question) {
        shuffledChoices.value = shuffleArray([...props.question.choices]);
    }
};

onMounted(shuffleChoices);
watch(() => props.question, shuffleChoices);

const confirmAnswer = () => {
    if (selectedChoice.value !== null) {
        emit('answer', selectedChoice.value);
    }
};
</script>   

<template>
    <div>
        <h2>{{ question.question }}</h2>
        <ul>
            <li v-for="choice in shuffledChoices" :key="choice">
                <label>
                    <input type="radio" :value="choice" v-model="selectedChoice" />
                    {{ choice }}
                </label>
            </li>   
        </ul>
        <button @click="confirmAnswer" :disabled="!selectedChoice">Next</button>
    </div>
</template>
