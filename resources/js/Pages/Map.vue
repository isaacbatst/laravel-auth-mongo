<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import 'leaflet'
import 'leaflet/dist/leaflet.css'
import 'leaflet.heat'

const props = defineProps({
    locations: {
        type: Array,
        required: true
    }
});

// const data = [
//     ...nearNatal,
//     ...nearMossoro
// ]

console.log(props.locations)

const data = props.locations.map(location => [
    location.lat,
    location.lng,
    100
]);

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
