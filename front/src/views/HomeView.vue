<script setup lang="ts">
import { ref } from 'vue';
const todos = ref<string[]>([]);
const todo = ref<string>('');

const addTask = () => {
  console.log('addTask');
  todos.value.push(todo.value);
  todo.value = '';
};
</script>

<template>
  <div v-if="todos.length === 0">Vous n'avez pas de tâches</div>
  <div v-else>
    
  </div>
  <fieldset role="group">
    <input type="text" v-model="todo" />
    <button @click="addTask">Ajouter une tâche</button>
  </fieldset>
  
</template>

<style scoped>

</style>
