<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import mapboxgl from 'mapbox-gl';
import Map from '@/pages/placeholders/Map.vue'
import { Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

mapboxgl.accessToken = 'pk.eyJ1Ijoibm9vYnR1Z3oiLCJhIjoiY21kZHdvaDZ3MDkxMDJscHFjbTNzbWNkciJ9.pUXsJVW32G9zpHypFNT1-g';

const mapContainer = ref<HTMLDivElement | null>(null);
let map: mapboxgl.Map | null = null;

const selectedStatus = ref('All');
const selectedZone = ref('All');
const activeZone = ref('');

const statusOptions = ['All', 'OK', 'Needs Maintenance', 'Offline'];
const zoneOptions = ['All', 'North', 'South', 'East', 'West'];

const dummyZoneData = ref([
  { zone: 'North', status: 'OK', color: '#10b981', risk: 'Low Traffic', data: [120, 140, 160], coordinates: [125.539, 8.945] },
  { zone: 'South', status: 'Needs Maintenance', color: '#f59e0b', risk: 'Moderate Congestion', data: [220, 200, 180], coordinates: [125.539, 8.925] },
  { zone: 'East', status: 'Offline', color: '#ef4444', risk: 'Heavy Traffic & Road Damage', data: [320, 350, 370], coordinates: [125.559, 8.945] },
  { zone: 'West', status: 'OK', color: '#3b82f6', risk: 'Normal Operation', data: [90, 100, 95], coordinates: [125.519, 8.945] },
]);

const chartData = ref({
  labels: ['7AM', '12NN', '5PM'],
  datasets: [
    {
      label: 'Vehicle Count by Time (Selected Zone)',
      data: [],
      backgroundColor: '#6366f1',
    },
  ],
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: { color: '#6b7 280' },
    },
  },
  scales: {
    x: {
      ticks: { color: '#6b7280' },
      grid: { color: '#e5e7eb' },
    },
    y: {
      ticks: { color: '#6b7280' },
      grid: { color: '#e5e7eb' },
    },
  },
};

function onZoneClick(zone: string) {
  activeZone.value = zone;
  const zoneObj = dummyZoneData.value.find(z => z.zone === zone);
  if (zoneObj) {
    chartData.value.datasets[0].data = zoneObj.data;
  }
}

function exportReport() {
  const zoneObj = dummyZoneData.value.find(z => z.zone === activeZone.value);
  if (!zoneObj) return;

  const csvContent = `Time,Vehicle Count\n` +
    chartData.value.labels.map((label, i) => `${label},${zoneObj.data[i]}`).join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.setAttribute('href', url);
  link.setAttribute('download', `${activeZone.value}_vehicle_report.csv`);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

onMounted(() => {
  if (!mapContainer.value) return;
  map = new mapboxgl.Map({
    container: mapContainer.value,
    style:'mapbox://styles/mapbox/satellite-streets-v11',
    center: [125.539, 8.945],
    zoom: 13,
  });
  map.addControl(new mapboxgl.NavigationControl());

  dummyZoneData.value.forEach(zone => {
    const el = document.createElement('div');
    el.className = 'marker';
    el.style.backgroundColor = zone.color;
    el.style.width = '20px';
    el.style.height = '20px';
    el.style.borderRadius = '50%';
    el.style.border = '2px solid white';
    el.style.boxShadow = '0 0 0 2px rgba(0,0,0,0.2)';
    el.style.cursor = 'pointer';
    el.title = `Zone: ${zone.zone}`;
    el.onclick = () => onZoneClick(zone.zone);

    new mapboxgl.Marker(el).setLngLat(zone.coordinates).addTo(map!);
  });
});

onBeforeUnmount(() => {
  map?.remove();
  map = null;
});
</script>

<template>
  <Head title="Map View" />
  <AppLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-100">Interactive Map View</h1>

      <!-- Map container -->
      <!-- <div ref="mapContainer" class="w-full h-[500px] rounded-lg border mb-6">

      </div> -->
        <div class="relative min-h-[400px] flex-1 rounded-xl border p-2 border-sidebar-border/70 dark:border-sidebar-border">
       <Map
  :kml-layers="[
    { url: '/kml/butuan/bxu_brgy_boundary.kml', color: '#4094f7' },         // blue
    { url: '/kml/butuan/bxu_waterways.kml', color: '#e76f51' }, // orange
    { url: '/kml/butuan/bxu_roadnetworks.kml', color: '#43aa8b' },   // green

  ]"
/>
      </div>
      <!-- Filters -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filter by Road Status</label>
          <select v-model="selectedStatus" class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-800 dark:text-white">
            <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filter by Zone</label>
          <select v-model="selectedZone" class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-800 dark:text-white">
            <option v-for="zone in zoneOptions" :key="zone" :value="zone">{{ zone }}</option>
          </select>
        </div>
      </div>

      <!-- Dummy Interactive Zones -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div
          v-for="zone in dummyZoneData.filter(z => (selectedStatus === 'All' || z.status === selectedStatus) && (selectedZone === 'All' || z.zone === selectedZone))"
          :key="zone.zone"
          class="rounded-lg p-4 text-white transition transform hover:scale-105 cursor-pointer"
          :style="{ backgroundColor: zone.color }"
          @click="onZoneClick(zone.zone)"
        >
          <h2 class="text-lg font-semibold">Zone: {{ zone.zone }}</h2>
          <p class="text-sm">Status: {{ zone.status }}</p>
          <p class="text-sm">AI Insight: {{ zone.risk }}</p>
        </div>
      </div>

      <!-- Dynamic Chart -->
      <div v-if="activeZone" class="h-[300px] w-full rounded-xl border p-4 dark:border-gray-700 bg-white dark:bg-gray-900">
        <div class="flex justify-between items-center mb-2">
          <h2 class="text-md font-semibold text-gray-800 dark:text-gray-100">Analytics for {{ activeZone }} Zone</h2>
          <button @click="exportReport" class="text-sm px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
            Export CSV
          </button>
        </div>
        <Bar :data="chartData" :options="chartOptions" />
      </div>
    </div>
  </AppLayout>
</template>
