<!-- MobileNavigation.vue -->
<template>
    <Disclosure as="nav" class="bg-nav-mobile fixed top-0 left-0 right-0 z-40">
        <template #default="{ open }">
            <div class="flex items-center justify-between h-16 px-4">
                <!-- Mobile Menu Toggle Button (Left) -->
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

                <!-- Logo (Center) -->
                <div class="flex justify-center items-center">
                    <img src="/images/logo/logo.png" alt="Echecs Plus" class="h-12 w-auto" />
                </div>

                <!-- User Profile (Right) -->
                <div class="relative">
                    <button @click="toggleUserMenu"
                        class="flex items-center justify-center py-2 px-3 rounded-md focus:outline-none">
                        <UserIcon class="h-8 w-8 text-custom-yellow" aria-hidden="true" />
                        <span class="ml-2 text-white">{{ userName }}</span>
                        <ChevronUpIcon v-if="isUserMenuOpen" class="ml-1 h-5 w-5 text-custom-yellow"
                            aria-hidden="true" />
                        <ChevronDownIcon v-else class="ml-1 h-5 w-5 text-custom-yellow" aria-hidden="true" />
                    </button>

                    <!-- User dropdown menu -->
                    <div v-if="isUserMenuOpen && userName !== 'Guest'"
                        class="absolute top-full right-0 w-48 mt-1 bg-nav-hover shadow-lg rounded-md overflow-hidden z-50">
                        <!-- Profile option -->
                        <router-link to="/profile"
                            class="flex items-center gap-x-2 px-4 py-2 text-sm font-medium text-white hover:bg-nav-mobile-button"
                            @click="isUserMenuOpen = false">
                            <UserCircleIcon class="h-5 w-5 text-custom-yellow" aria-hidden="true" />
                            Profil
                        </router-link>
                        <!-- Settings option -->
                        <router-link to="/settings"
                            class="flex items-center gap-x-2 px-4 py-2 text-sm font-medium text-white hover:bg-nav-mobile-button"
                            @click="isUserMenuOpen = false">
                            <CogIcon class="h-5 w-5 text-custom-yellow" aria-hidden="true" />
                            Paramètres
                        </router-link>
                        <!-- Logout option -->
                        <router-link to="/logout"
                            class="flex items-center gap-x-2 px-4 py-2 text-sm font-medium text-red-400 hover:bg-nav-mobile-button hover:text-red-300"
                            @click="isUserMenuOpen = false">
                            <LogOutIcon class="h-5 w-5" aria-hidden="true" />
                            Déconnexion
                        </router-link>
                    </div>
                </div>
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
import { ref, onMounted, onUnmounted } from 'vue'
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import {
    ChevronRightIcon,
    Bars3Icon,
    XMarkIcon,
    UserIcon,
    ChevronUpIcon,
    ChevronDownIcon
} from '@heroicons/vue/20/solid'
import {
    ArrowRightOnRectangleIcon as LogOutIcon,
    UserCircleIcon,
    CogIcon
} from '@heroicons/vue/24/outline'
import { navigation } from '@/data/navigation'

const props = defineProps<{
    userName: string;
    userRole: string;
}>();

const openDisclosureIndex = ref<number | null>(null)
function toggleDisclosure(index: number) {
    openDisclosureIndex.value = openDisclosureIndex.value === index ? null : index
}

// User menu dropdown state
const isUserMenuOpen = ref(false)
function toggleUserMenu() {
    isUserMenuOpen.value = !isUserMenuOpen.value
}

// Close menu when clicking outside
function handleClickOutside(event: MouseEvent) {
    const target = event.target as HTMLElement
    if (!target.closest('.relative')) {
        isUserMenuOpen.value = false
    }
}

// Setup click outside listener
onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>