<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import 'leaflet'
import 'leaflet/dist/leaflet.css'
import 'leaflet.heat'

defineProps({});

function getRandomNormal(mean, stdDev) {
    let u = 0, v = 0;
    while (u === 0) u = Math.random(); // Converting [0,1) to (0,1)
    while (v === 0) v = Math.random();
    return mean + stdDev * Math.sqrt(-2.0 * Math.log(u)) * Math.cos(2.0 * Math.PI * v);
}

// Generate 1000 random coordinates with normal distribution
const nearNatal = Array.from({ length: 100 }, () => [
    getRandomNormal(-5.745, 0.04),
    getRandomNormal(-35.359, 0.04),
    100
]);

const nearMossoro = Array.from({ length: 1000 }, () => [
    getRandomNormal(-5.245, 0.05),
    getRandomNormal(-37.359, 0.05),
    100
]);

const data = [
    ...nearNatal,
    ...nearMossoro
]

onMounted(() => {
    const defaultParams = {
        lat: -8.211,
        lng: -40.897,
        zoom: 7
    };
    const params = new URLSearchParams(window.location.search);
    const lat = params.get('lat') || defaultParams.lat;
    const lng = params.get('lng') || defaultParams.lng;
    const zoom = params.get('zoom') || defaultParams.zoom;
    const map = L.map('map').setView([lat, lng], zoom);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    L.heatLayer(data, { 
        radius: 12,
        max: 100,
        minOpacity: 0.5,
    }).addTo(map);
    map.on("move", function() {
        const params = new URLSearchParams(window.location.search);
        params.set('lat', map.getCenter().lat);
        params.set('lng', map.getCenter().lng);
        params.set('zoom', map.getZoom());
        window.history.replaceState({}, '', `${window.location.pathname}?${params}`);
    });
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
