<template>
    <button :class="[
        'px-4 py-2 rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2',
        sizeClasses,
        variantClasses,
        disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
        fullWidth ? 'w-full' : '',
        className
    ]" :disabled="disabled" @click="$emit('click', $event)">
        <slot></slot>
    </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value: string) => ['primary', 'secondary', 'outline', 'danger', 'success', 'text'].includes(value)
    },
    size: {
        type: String,
        default: 'md',
        validator: (value: string) => ['sm', 'md', 'lg'].includes(value)
    },
    disabled: {
        type: Boolean,
        default: false
    },
    fullWidth: {
        type: Boolean,
        default: false
    },
    className: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['click'])

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'bg-[var(--color-nav-start)] hover:bg-[var(--color-nav-hover)] text-white focus:ring-[var(--color-nav-start)]'
        case 'secondary':
            return 'bg-[var(--color-light-pgn)] hover:bg-[var(--color-nav-end)] text-[var(--color-nav-start)] focus:ring-[var(--color-nav-hover)]'
        case 'outline':
            return 'bg-transparent hover:bg-[var(--color-light-pgn)] text-[var(--color-nav-start)] border border-[var(--color-nav-start)] focus:ring-[var(--color-nav-start)]'
        case 'danger':
            return 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500'
        case 'success':
            return 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500'
        case 'text':
            return 'bg-transparent hover:bg-[var(--color-light-pgn)] text-[var(--color-nav-start)] focus:ring-[var(--color-nav-start)]'
        default:
            return 'bg-[var(--color-nav-start)] hover:bg-[var(--color-nav-hover)] text-white focus:ring-[var(--color-nav-start)]'
    }
})

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'text-xs px-2.5 py-1.5'
        case 'md':
            return 'text-sm px-4 py-2'
        case 'lg':
            return 'text-base px-6 py-3'
        default:
            return 'text-sm px-4 py-2'
    }
})
</script>