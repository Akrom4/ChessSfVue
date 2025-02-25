<script setup lang="ts">
import Checkbox from '../components/Checkbox.vue';
import Button from '../components/Button.vue';
import { ref, computed, onMounted } from 'vue';
import Layout from '../components/Layout.vue';
const todos = ref<{todo: string, isDone: boolean, date: number}[]>([]);
const todo = ref<string>('');
onMounted(() => {
  fetch('https://jsonplaceholder.typicode.com/todos')
    .then(response => response.json())
    .then(data => {
      todos.value = data.map((item: any) => ({
        todo: item.title,
        isDone: item.completed,
        date: item.id
      }));
    });
});
const addTask = () => {
  todos.value.push({todo: todo.value, isDone: false, date: Date.now()});
  todo.value = '';
};

const sortedTodos = computed(() => {
  return [...todos.value].sort((a) => a.isDone ? 1 : -1);
});

</script>

<template>
  <Layout>
    <template #header>Entête</template>
    <template #aside>Sidebar</template>
    <template #main>Main</template>
    <template #footer>Footer</template>
  </Layout>
    <div v-if="todos.length === 0">Vous n'avez pas de tâches</div>
    <div v-else>
      <div v-for="todo in sortedTodos" :key="todo.date">
      <Checkbox :label="todo.todo" v-model="todo.isDone" />
      <span :class="{'line-through': todo.isDone}">{{ todo.todo }}</span>
    </div>
  </div>
  <fieldset role="group">
    <input type="text" v-model="todo" />
    <Button :disabled="todo.length === 0" @click="addTask">Ajouter une tâche</Button>
  </fieldset>
  
</template>

<style scoped>

</style>
