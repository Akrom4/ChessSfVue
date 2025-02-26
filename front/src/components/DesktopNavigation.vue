<!-- DesktopSidebar.vue -->
<template>
    <div
        class="flex flex-col h-screen w-48 overflow-y-auto border-r border-gray-300 bg-gradient-to-b from-[var(--color-nav-start)] to-[var(--color-nav-end)] px-6 text-white">
        <!-- Centered Logo -->
        <div class="flex pt-4 h-16 shrink-0 justify-center items-center">
            <img class="h-16 w-auto" src="/images/logo/logo.png" alt="Echecs Plus" />
        </div>
        <nav class="flex flex-1 flex-col mt-6">
            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                <li>
                    <ul role="list" class="-mx-2 space-y-1">
                        <li v-for="(item, index) in navigation" :key="item.name">
                            <template v-if="!item.children">
                                <a :href="item.href"
                                    class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold hover:bg-[var(--color-nav-hover)] text-white">
                                    <component :is="item.icon" class="h-6 w-6 shrink-0 text-custom-yellow"
                                        aria-hidden="true" />
                                    {{ item.name }}
                                </a>
                            </template>
                            <template v-else>
                                <div>
                                    <button @click="toggleDisclosure(index)"
                                        class="flex w-full items-center gap-x-3 rounded-md p-2 text-left text-sm/6 font-semibold hover:bg-nav-hover text-white">
                                        <component :is="item.icon" class="h-6 w-6 shrink-0 text-custom-yellow"
                                            aria-hidden="true" />
                                        {{ item.name }}
                                        <ChevronRightIcon
                                            :class="[(openDisclosureIndex === index) ? 'rotate-90' : '', 'ml-auto h-5 w-5 shrink-0 text-custom-yellow']"
                                            aria-hidden="true" />
                                    </button>
                                    <ul v-show="openDisclosureIndex === index" class="mt-1 px-2">
                                        <li v-for="subItem in item.children" :key="subItem.name">
                                            <a :href="subItem.href"
                                                class="block rounded-md py-2 pl-9 pr-2 text-sm/6 hover:bg-nav-hover text-white">
                                                {{ subItem.name }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </template>
                        </li>
                        <!-- Admin Panel Link for role_admin -->
                        <li v-if="userRole === 'role_admin'">
                            <a href="/admin"
                                class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold hover:bg-[var(--color-nav-hover)] text-white">
                                <Cog6ToothIcon class="h-6 w-6 shrink-0 text-custom-yellow" aria-hidden="true" />
                                Administration
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- User Profile Section -->
                <li class="-mx-6 mt-auto">
                    <a href="#"
                        class="flex items-center gap-x-4 px-6 py-3 text-sm/6 font-semibold text-white hover:bg-[var(--color-nav-hover)]">
                        <UserIcon class="h-8 w-8 text-custom-yellow" aria-hidden="true" />
                        <span class="sr-only">Your profile</span>
                        <span aria-hidden="true">{{ userName }}</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { ChevronRightIcon } from '@heroicons/vue/20/solid'
import { UserIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline'
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