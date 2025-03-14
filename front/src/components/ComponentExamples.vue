<template>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Component Examples</h1>

        <!-- Button Examples -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Button Component</h2>

            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Variants</h3>
                    <div class="flex flex-wrap gap-4">
                        <Button variant="primary">Primary</Button>
                        <Button variant="secondary">Secondary</Button>
                        <Button variant="outline">Outline</Button>
                        <Button variant="danger">Danger</Button>
                        <Button variant="success">Success</Button>
                        <Button variant="text">Text</Button>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Sizes</h3>
                    <div class="flex flex-wrap items-center gap-4">
                        <Button size="sm">Small</Button>
                        <Button size="md">Medium</Button>
                        <Button size="lg">Large</Button>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">States</h3>
                    <div class="flex flex-wrap gap-4">
                        <Button>Normal</Button>
                        <Button disabled>Disabled</Button>
                        <Button fullWidth class="max-w-xs">Full Width</Button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Card Examples -->
        <section>
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Card Component</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Default Card -->
                <Card>
                    <template #header>
                        <h3 class="text-xl font-bold text-blue-800">Default Card</h3>
                    </template>

                    <p class="text-gray-600 mb-4">
                        This is a default card with header and footer. It uses the default elevation.
                    </p>

                    <template #footer>
                        <div class="flex justify-end">
                            <Button variant="primary" size="sm">Action</Button>
                        </div>
                    </template>
                </Card>

                <!-- Primary Card with Image -->
                <Card variant="primary">
                    <template #image>
                        <img src="https://via.placeholder.com/400x200/4F46E5/FFFFFF?text=Card+Image" alt="Card image"
                            class="w-full h-full object-cover" />
                    </template>

                    <template #header>
                        <h3 class="text-xl font-bold text-blue-800">Primary Card</h3>
                    </template>

                    <p class="text-gray-700 mb-4">
                        This is a primary card with a header, image, and footer. It has a blue theme.
                    </p>

                    <template #footer>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-blue-700">Last updated: Today</span>
                            <Button variant="secondary" size="sm">Learn More</Button>
                        </div>
                    </template>
                </Card>

                <!-- Bordered Card -->
                <Card variant="bordered" elevation="none">
                    <template #header>
                        <h3 class="text-xl font-bold text-gray-800">Bordered Card</h3>
                    </template>

                    <p class="text-gray-600 mb-4">
                        This is a bordered card with no default shadow. It will show a shadow on hover.
                    </p>

                    <template #footer>
                        <div class="flex justify-end">
                            <Button variant="outline" size="sm">Details</Button>
                        </div>
                    </template>
                </Card>

                <!-- Flat Card -->
                <Card variant="flat" elevation="sm" :hover="false">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Flat Card</h3>

                    <p class="text-gray-600">
                        This is a flat card with a small shadow. It has no header or footer and won't change on hover.
                    </p>
                </Card>

                <!-- Content-only Card -->
                <Card elevation="lg">
                    <h3 class="text-xl font-bold text-blue-800 mb-2">Featured Content</h3>
                    <p class="text-gray-600 mb-4">
                        This card only has content, with a large shadow elevation for emphasis.
                    </p>
                    <div class="flex gap-2">
                        <Button variant="primary" size="sm">Primary</Button>
                        <Button variant="outline" size="sm">Secondary</Button>
                    </div>
                </Card>
            </div>
        </section>
    </div>
</template>

<script setup lang="ts">
import Button from './ui/Button.vue'
import Card from './ui/Card.vue'
</script>