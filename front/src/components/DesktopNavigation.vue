<!-- DesktopSidebar.vue -->
<template>
    <div
        class="fixed top-0 left-0 flex flex-col h-screen w-48 border-r border-gray-300 bg-gradient-to-b from-[var(--color-nav-start)] to-[var(--color-nav-end)] px-2 text-white overflow-x-hidden">
        <div class="flex pt-4 h-16 shrink-0 justify-center items-center">
            <img class="h-16 w-auto" src="/images/logo/logo.png" alt="Echecs Plus" />
        </div>
        <nav class="flex flex-1 flex-col mt-6 overflow-y-auto overflow-x-hidden">
            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                <li class="w-full">
                    <ul role="list" class="space-y-1 w-full">
                        <li v-for="(item, index) in navigation" :key="item.name" class="w-full">
                            <template v-if="!item.children">
                                <a :href="item.href"
                                    class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold hover:bg-nav-hover text-white">
                                    <component :is="item.icon" class="h-6 w-6 shrink-0 text-custom-yellow"
                                        aria-hidden="true" />
                                    {{ item.name }}
                                </a>
                            </template>
                            <template v-else>
                                <div class="w-full">
                                    <button @click="toggleDisclosure(index)"
                                        class="flex w-full items-center gap-x-3 rounded-md p-2 text-left text-sm/6 font-semibold hover:bg-nav-hover text-white">
                                        <component :is="item.icon" class="h-6 w-6 shrink-0 text-custom-yellow"
                                            aria-hidden="true" />
                                        <span class="truncate">{{ item.name }}</span>
                                        <ChevronRightIcon
                                            :class="[(openDisclosureIndex === index) ? 'rotate-90' : '', 'ml-auto h-5 w-5 shrink-0 text-custom-yellow']"
                                            aria-hidden="true" />
                                    </button>
                                    <ul v-show="openDisclosureIndex === index" class="mt-1 px-2 w-full">
                                        <li v-for="subItem in item.children" :key="subItem.name" class="w-full">
                                            <a :href="subItem.href"
                                                class="block rounded-md py-2 pl-9 pr-2 text-sm/6 hover:bg-nav-hover text-white truncate">
                                                {{ subItem.name }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </template>
                        </li>
                        <!-- Admin Panel Link for role_admin -->
                        <li v-if="userRole === 'role_admin'" class="w-full">
                            <a href="/admin"
                                class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold hover:bg-nav-hover text-white">
                                <Cog6ToothIcon class="h-6 w-6 shrink-0 text-custom-yellow" aria-hidden="true" />
                                <span class="truncate">Administration</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- User Profile Section with Dropdown -->
                <li class="w-full mt-auto relative">
                    <!-- User dropdown menu (now above the button) -->
                    <ul v-if="isUserMenuOpen && userName !== 'Guest'"
                        class="absolute bottom-full left-0 w-full mb-1 bg-[var(--color-nav-hover)] rounded-t-md shadow-lg overflow-hidden z-10">
                        <!-- Profile option -->
                        <li class="w-full">
                            <router-link to="/profile"
                                class="flex items-center gap-x-3 py-2 px-3 text-sm/6 hover:bg-nav-hover text-white"
                                @click="isUserMenuOpen = false">
                                <UserCircleIcon class="h-5 w-5 shrink-0 text-custom-yellow" aria-hidden="true" />
                                <span class="truncate">Profil</span>
                            </router-link>
                        </li>
                        <!-- Settings option -->
                        <li class="w-full">
                            <router-link to="/settings"
                                class="flex items-center gap-x-3 py-2 px-3 text-sm/6 hover:bg-nav-hover text-white"
                                @click="isUserMenuOpen = false">
                                <CogIcon class="h-5 w-5 shrink-0 text-custom-yellow" aria-hidden="true" />
                                <span class="truncate">Paramètres</span>
                            </router-link>
                        </li>
                        <!-- Logout option -->
                        <li class="w-full">
                            <router-link to="/logout"
                                class="flex items-center gap-x-3 py-2 px-3 text-sm/6 hover:bg-nav-hover text-red-400 hover:text-red-300"
                                @click="isUserMenuOpen = false">
                                <LogOutIcon class="h-5 w-5 shrink-0" aria-hidden="true" />
                                <span class="truncate">Déconnexion</span>
                            </router-link>
                        </li>
                    </ul>

                    <!-- Username button with dropdown toggle -->
                    <button @click="toggleUserMenu"
                        class="group flex w-full gap-x-3 rounded-md p-2 text-sm/6 font-semibold hover:bg-nav-hover text-white">
                        <UserIcon class="h-6 w-6 shrink-0 text-custom-yellow" aria-hidden="true" />
                        <span class="truncate">{{ userName }}</span>
                        <ChevronUpIcon v-if="isUserMenuOpen" class="ml-auto h-5 w-5 shrink-0 text-custom-yellow"
                            aria-hidden="true" />
                        <ChevronDownIcon v-else class="ml-auto h-5 w-5 shrink-0 text-custom-yellow"
                            aria-hidden="true" />
                    </button>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { ChevronRightIcon, ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/20/solid'
import { UserIcon, Cog6ToothIcon, UserCircleIcon, CogIcon } from '@heroicons/vue/24/outline'
import { navigation } from '@/data/navigation'

// Import logout icon
import LogOutIcon from '@heroicons/vue/24/outline/ArrowRightOnRectangleIcon'

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
    if (!target.closest('.w-full.mt-auto')) {
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

// Compute display role for user-friendly format
const displayRole = computed(() => {
    if (props.userRole === 'role_admin') return 'Administrator';
    if (props.userRole === 'role_user') return 'User';
    return '';
});

// Compute user initials for avatar
const userInitials = computed(() => {
    if (!props.userName || props.userName === 'Guest') return 'G';

    // Get first letter of first and last name if it has multiple parts
    const parts = props.userName.split(/[\s@]+/);
    if (parts.length > 1) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }

    // Otherwise just return the first letter
    return props.userName[0].toUpperCase();
});
</script>