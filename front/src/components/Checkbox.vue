<template>
    <label>
        <input type="checkbox" v-model="model" />
        {{ label }}
    </label>
</template>

<script setup lang="ts">
const model = defineModel<boolean>({ required: true });
defineProps<{
    label: string;
}>();
const emits = defineEmits<{
    (e: 'checked', value: boolean): void;
    (e: 'unchecked', value: boolean): void;
}>();

const onChange = (event: Event) => {
    const target = event.currentTarget as HTMLInputElement | null;
    if (target?.checked) {
        emits('checked', true);
    } else {
        emits('unchecked', false);
    }
}
</script>