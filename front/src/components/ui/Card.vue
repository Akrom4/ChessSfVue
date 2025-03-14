<template>
    <div :class="[
        'overflow-hidden rounded-lg transition-shadow',
        variantClasses,
        elevationClasses,
        className
    ]">
        <!-- Chess Card Layout (compact horizontal) -->
        <div v-if="layout === 'chess'" class="flex flex-col">
            <!-- First horizontal part -->
            <div class="flex">
                <!-- Left side - Image thumbnail -->
                <div v-if="$slots.image" class="w-1/3 relative">
                    <div class="pb-[100%] relative">
                        <div class="absolute inset-0">
                            <slot name="image"></slot>
                        </div>
                    </div>
                </div>

                <!-- Right side - Title and difficulty indicators -->
                <div class="w-2/3 p-3 relative flex">
                    <!-- Title area -->
                    <div class="flex-grow pr-10">
                        <slot name="title"></slot>
                    </div>

                    <!-- Difficulty indicators (right side, vertical) -->
                    <div v-if="$slots.indicators" class="flex flex-col space-y-1.5">
                        <slot name="indicators"></slot>
                    </div>

                    <!-- Add button (top right) -->
                    <div v-if="$slots.action" class="absolute top-2 right-2 z-10">
                        <slot name="action"></slot>
                    </div>
                </div>
            </div>

            <!-- Separator line -->
            <div class="border-t border-gray-200"></div>

            <!-- Second horizontal part - Info and description -->
            <div class="p-3">
                <div class="flex justify-between mb-2">
                    <!-- Color side info (top left) -->
                    <div v-if="$slots.colorside" class="text-xs font-medium">
                        <slot name="colorside"></slot>
                    </div>

                    <!-- Author info (top right) -->
                    <div v-if="$slots.author" class="text-xs text-gray-500">
                        <slot name="author"></slot>
                    </div>
                </div>

                <!-- Description -->
                <div class="text-sm">
                    <slot></slot>
                </div>
            </div>
        </div>

        <!-- Split Layout (Image left, Metadata right) -->
        <div v-else-if="layout === 'split'" class="flex flex-col sm:flex-row">
            <!-- Left side - Image -->
            <div v-if="$slots.image" :class="[
                'relative overflow-hidden sm:w-1/2',
                splitImageAspectRatioClass
            ]">
                <div class="absolute inset-0 flex items-center justify-center">
                    <slot name="image"></slot>
                </div>
            </div>

            <!-- Right side - Metadata -->
            <div v-if="$slots.metadata" class="sm:w-1/2 p-4 flex flex-col justify-center" :class="metadataClasses">
                <slot name="metadata"></slot>
            </div>

            <!-- Card Body below the split layout -->
            <div v-if="$slots.default" class="p-6 w-full" :class="bodyClasses">
                <slot></slot>
            </div>

            <!-- Card Footer -->
            <div v-if="$slots.footer" class="px-6 py-4 border-t w-full" :class="footerClasses">
                <slot name="footer"></slot>
            </div>
        </div>

        <!-- Standard Layout (Full-width sections) -->
        <div v-else>
            <!-- Card Image (optional) -->
            <div v-if="$slots.image" :class="[
                'relative overflow-hidden',
                imageAspectRatioClass
            ]">
                <div class="absolute inset-0 flex items-center justify-center">
                    <slot name="image"></slot>
                </div>
            </div>

            <!-- Card Header (optional) -->
            <div v-if="$slots.header" class="px-6 py-4 border-b" :class="headerClasses">
                <slot name="header"></slot>
            </div>

            <!-- Card Body -->
            <div class="p-6" :class="bodyClasses">
                <slot></slot>
            </div>

            <!-- Card Footer (optional) -->
            <div v-if="$slots.footer" class="px-6 py-4 border-t" :class="footerClasses">
                <slot name="footer"></slot>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (value: string) => ['default', 'primary', 'bordered', 'flat'].includes(value)
    },
    elevation: {
        type: String,
        default: 'md',
        validator: (value: string) => ['none', 'sm', 'md', 'lg'].includes(value)
    },
    hover: {
        type: Boolean,
        default: true
    },
    className: {
        type: String,
        default: ''
    },
    imageAspectRatio: {
        type: String,
        default: '4/3',
        validator: (value: string) => ['4/3', '16/9', '1/1'].includes(value)
    },
    layout: {
        type: String,
        default: 'standard',
        validator: (value: string) => ['standard', 'split', 'chess'].includes(value)
    }
})

const imageAspectRatioClass = computed(() => {
    switch (props.imageAspectRatio) {
        case '4/3':
            return 'pb-[75%]' // 3/4 = 0.75 = 75%
        case '16/9':
            return 'pb-[56.25%]' // 9/16 = 0.5625 = 56.25%
        case '1/1':
            return 'pb-[100%]' // 1/1 = 1 = 100%
        default:
            return 'pb-[75%]' // Default to 4/3
    }
})

const splitImageAspectRatioClass = computed(() => {
    // For split layout, we use a square aspect ratio on mobile 
    // and custom aspect ratios for desktop where the image is half-width
    switch (props.imageAspectRatio) {
        case '4/3':
            return 'pb-[100%] sm:pb-[150%]' // Make it taller on desktop when half-width
        case '16/9':
            return 'pb-[100%] sm:pb-[112.5%]' // 16/9 but with half-width
        case '1/1':
            return 'pb-[100%]' // Always square
        default:
            return 'pb-[100%] sm:pb-[150%]' // Default to 4/3 adjusted for half-width
    }
})

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'default':
            return 'bg-white'
        case 'primary':
            return 'bg-[var(--color-light-pgn)]'
        case 'bordered':
            return 'bg-white border border-gray-200'
        case 'flat':
            return 'bg-gray-50'
        default:
            return 'bg-white'
    }
})

const elevationClasses = computed(() => {
    if (props.variant === 'bordered' || props.variant === 'flat') {
        return props.hover ? 'hover:shadow-md' : ''
    }

    switch (props.elevation) {
        case 'none':
            return props.hover ? 'hover:shadow-sm' : ''
        case 'sm':
            return `shadow-sm ${props.hover ? 'hover:shadow' : ''}`
        case 'md':
            return `shadow ${props.hover ? 'hover:shadow-md' : ''}`
        case 'lg':
            return `shadow-md ${props.hover ? 'hover:shadow-lg' : ''}`
        default:
            return `shadow ${props.hover ? 'hover:shadow-md' : ''}`
    }
})

const headerClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'bg-white border-[var(--color-nav-hover)]'
        default:
            return 'bg-white border-gray-200'
    }
})

const bodyClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'bg-[var(--color-light-pgn)]'
        default:
            return ''
    }
})

const metadataClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'bg-[var(--color-light-pgn)]'
        default:
            return 'bg-gray-50'
    }
})

const footerClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'bg-[var(--color-light-pgn)] border-[var(--color-nav-hover)]'
        default:
            return 'bg-gray-50 border-gray-200'
    }
})
</script>