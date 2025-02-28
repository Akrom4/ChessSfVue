<template>
  <div>
    <p v-if="isLoading">Loading...</p>
    <form v-else @submit.prevent="handleSubmit" class="row g-3">
      <div class="col-md-6">
        <label for="courseId" class="form-label">Course</label>
        <select
          name="courseId"
          id="courseId"
          required
          v-model="formData.courseId"
          class="form-control"
        >
          <option
            v-for="course in courses['hydra:member']"
            :key="course.id"
            :value="course.id"
          >
            {{ course.title }}
          </option>
        </select>
      </div>
      <div class="col-md-6">
        <label for="title" class="form-label">Titre</label>
        <input
          type="text"
          name="title"
          id="title"
          required
          v-model="formData.title"
          class="form-control"
        />
      </div>
      <div class="col-12">
        <label for="pgnText" class="form-label">Fichier Pgn</label>
        <textarea
          name="pgnText"
          id="pgnText"
          v-model="formData.pgnText"
          class="form-control"
        ></textarea>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary mb-2 mt-2">Ajouter</button>
      </div>
    </form>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, onMounted } from 'vue';
import { Pgn } from '../../models';

// Define a local interface for the course options
interface CourseOption {
  id: string;
  title: string;
}

export default defineComponent({
  name: 'ChapterForm',
  setup() {
    const isLoading = ref(true);
    // Use the local CourseOption interface to type the courses ref
    const courses = ref<{ 'hydra:member': CourseOption[] }>({ 'hydra:member': [] });
    const formData = reactive({
      title: '',
      pgnText: '',
      courseId: ''
    });
    const chapter = ref<any>(null);

    onMounted(() => {
      fetch('/api/courses')
        .then(response => response.json())
        .then(data => {
          courses.value = data;
          isLoading.value = false;
        })
        .catch(error => {
          console.error('Erreur d\'accès à la liste des cours  !', error);
          isLoading.value = false;
        });

      const chapterId = document.getElementById('pgn-wrapper')?.dataset.chapterId;
      if (chapterId) {
        fetch('/api/chapter/' + chapterId)
          .then(response => response.json())
          .then(data => {
            chapter.value = data;
            formData.courseId = data.courseId;
            formData.title = data.title;
            formData.pgnText = data.pgndata;
          });
      }
    });

    const handleSubmit = async () => {
      const pgn = new Pgn(formData.pgnText);
      const parsedJson = pgn.parseData();
      const data = {
        course: '/api/courses/' + formData.courseId,
        title: formData.title,
        rawpgn: formData.pgnText,
        pgndata: parsedJson,
      };

      console.log(data);

      const url = chapter.value ? `/api/chapters/${chapter.value.id}` : '/api/chapters';
      const method = chapter.value ? 'PUT' : 'POST';

      await fetch(url, {
        method: method,
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
      });
    };

    return {
      isLoading,
      courses,
      formData,
      handleSubmit
    };
  }
});
</script>

<style scoped>
/* Add your styles here */
</style>


