<script setup lang="ts">

import { ref, onMounted } from 'vue';
import Quiz from '../components/Quiz.vue';

const quiz = ref(null);
const state = ref('loading');

onMounted(() => {
    fetch('quiz.json')
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Failed to fetch quiz');
        })
        .then(data => {
            quiz.value = data;
            state.value = 'idle';
        })
        .catch(error => {
            console.error('Error fetching quiz:', error);
            state.value = 'error';
        });
});

</script>

<template>
    <div class="container mt-2">
        <div v-if="state === 'error'">
            <h1>Error fetching quiz</h1>
        </div>
        <div :aria-busy="state === 'loading'">
            <Quiz :quiz="quiz" v-if="quiz" />
        </div>
    </div>
</template>
