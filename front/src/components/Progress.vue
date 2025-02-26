<script setup lang="ts">
defineProps<{
    current: number;
    total: number;
}>();
</script>

<template>
    <div>
        Étape {{ current + 1 > total ? current : current + 1 }} sur {{ total + 1 }}
    </div>
    <div class="progress">
        <progress :value="current" :max="total"/>
    </div>
</template>
