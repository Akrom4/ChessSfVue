<!-- MobileNavigation.vue -->
<template>
    <Disclosure as="nav" class="bg-nav-mobile">
        <template #default="{ open }">
            <div class="flex items-center justify-between h-16 px-4">
                <!-- Logo -->
                <div class="flex justify-center items-center">
                    <img src="/images/logo/logo.png" alt="Echecs Plus" class="h-12 w-auto" />
                </div>
                <!-- User Profile Centered in Top Bar -->
                <div class="flex justify-center items-center">
                    <UserIcon class="h-8 w-8 text-custom-yellow" aria-hidden="true" />
                    <span class="ml-2 text-white">{{ userName }}</span>
                </div>
                <!-- Mobile Menu Toggle Button -->
                <DisclosureButton
                    class="p-2 rounded-md text-white bg-nav-mobile-button focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                    <span class="sr-only">Open mobile menu</span>
                    <template v-if="open">
                        <XMarkIcon class="h-6 w-6" />
                    </template>
                    <template v-else>
                        <Bars3Icon class="h-6 w-6" />
                    </template>
                </DisclosureButton>
            </div>
            <!-- Mobile Menu Panel -->
            <DisclosurePanel class="bg-nav-mobile">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <ul>
                        <li v-for="(item, index) in navigation" :key="item.name">
                            <template v-if="!item.children">
                                <a :href="item.href"
                                    class="text-white block px-3 py-2 rounded-md text-base font-medium">
                                    {{ item.name }}
                                </a>
                            </template>
                            <template v-else>
                                <button @click="toggleDisclosure(index)"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-800 focus:outline-none">
                                    <span>{{ item.name }}</span>
                                    <ChevronRightIcon
                                        :class="[(openDisclosureIndex === index) ? 'rotate-90 transform' : '', 'h-5 w-5']" />
                                </button>
                                <div v-show="openDisclosureIndex === index" class="space-y-1 pl-5">
                                    <a v-for="subItem in item.children" :key="subItem.name" :href="subItem.href"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-800">
                                        {{ subItem.name }}
                                    </a>
                                </div>
                            </template>
                        </li>
                        <li v-if="userRole === 'role_admin'">
                            <a href="/admin"
                                class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-800">
                                Administration
                            </a>
                        </li>
                    </ul>
                </div>
            </DisclosurePanel>
        </template>
    </Disclosure>
</template>

<script setup lang="ts">
import { ref, defineProps } from 'vue'
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { ChevronRightIcon, Bars3Icon, XMarkIcon, UserIcon } from '@heroicons/vue/20/solid'
import { navigation } from '@/data/navigation'

const props = defineProps<{
    userName: string;
    userRole: string;
}>();

const openDisclosureIndex = ref<number | null>(null)
function toggleDisclosure(index: number) {
    openDisclosureIndex.value = openDisclosureIndex.value === index ? null : index
}
</script>