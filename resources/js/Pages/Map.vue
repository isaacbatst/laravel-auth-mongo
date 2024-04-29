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
    <div class="tw-flex tw-flex-col tw-justify-center tw-selection:bg-[#FF2D20] tw-selection:text-white
        tw-h-screen tw-w-screen
    ">
        <nav class=" navbar navbar-expand-md text-light py-3" style="background: rgb(31,87,170);">
            <div class="container"><a class="navbar-brand d-flex align-items-center" href="#"><span
                        class="text-light"><strong>DengueAlert.com</strong></span></a><button data-bs-toggle="collapse"
                    class="navbar-toggler" data-bs-target="#navcol-3"><span class="visually-hidden">Toggle
                        navigation</span><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navcol-3">
                    <ul class="navbar-nav mx-auto"></ul>
                    <div><span>Mapa de focos de zoonozes</span></div>
                </div>
            </div>
        </nav>
        <main id="map" class="tw-flex-1 tw-w-full"> </main>
    </div>
</template>
