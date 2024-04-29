<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';

defineProps({});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}

onMounted(() => {
    const map = L.map('map').setView([-8.211, -40.897], 7);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
})
</script>

<template>

    <Head title="Welcome" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50
        h-screen flex flex-col   
    ">
        <header>
            <div class="flex items-center justify-between bg-white dark:bg-black
                p-4 shadow-md
            ">
                <h1 class="text-xl font-bold">Map</h1>
            </div>
        </header>
        <main class="flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white
            flex-1
        ">
                <div id="map" class="w-full h-full"></div>
        </main>
    </div>
</template>
