<template>
    <div class="grid grid-cols-2 border-2 border-red-500 min-h-screen">
        <header v-if="$slots.header" class="col-span-2">            
            <slot name="header"></slot>
        </header>
        <aside class="bg-gray-200 p-4">
            <slot name="aside"></slot>
        </aside>
        <main class="bg-white p-4">
            <slot name="main"></slot>
        </main>
        <footer class="col-span-2">
            <slot name="footer"></slot>
        </footer>
    </div>
</template>