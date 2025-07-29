<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import RoadMonitoringPlaceHolder from './placeholders/RoadMonitoringPlaceHolder.vue'
import * as toGeoJSON from '@tmcw/togeojson'

const kmlLayers = [
  { url: '/kml/butuan/bxu_brgy_boundary.kml', color: '#e76f51' },
  { url: '/kml/butuan/bxu_roadnetworks.kml', color: '#43aa8b' },
  { url: '/kml/butuan/bxu_waterways.kml', color: '#4094f7' },
];

const roadRows = ref<any[]>([])
const loading = ref(true)
const highlightFeature = ref(null)

// Fake AI/sensor analysis generator for demo (replace with real data if available)
function randomStatus() {
  const arr = ["Hazard", "Needs Maintenance", "OK", "Offline"]
  return arr[Math.floor(Math.random() * arr.length)]
}
function randomAI(status: string) {
  if (status === "Hazard") return "Major cracks, urgent repair needed";
  if (status === "Needs Maintenance") return "Minor issues detected, monitor closely ";
  if (status === "Offline") return "Sensor offline, no data";
  return "Road is in good condition";
}

onMounted(async () => {
  // Fetch KML, parse to GeoJSON, build table
  const kmlRes = await fetch('/kml/butuan/bxu_roadnetworks.kml')
  const kmlText = await kmlRes.text()
  const { DOMParser } = await import('xmldom')
  const doc = new DOMParser().parseFromString(kmlText, 'text/xml')
  const geojson = toGeoJSON.kml(doc)
  // Only handle LineString features for roads
  let ctr = 0
  roadRows.value = geojson.features
    .filter(f => f.geometry?.type === 'LineString')
    .slice(0, 12) // Limit for demo
    .map((f: any) => {
      ctr++
      const props = f.properties || {}
      const coords = f.geometry.coordinates
      // Middle point of road line for marker
      const center = coords[Math.floor(coords.length/2)]
      // Fake details from KML props
      const name =  props.ROAD_NAME || props.DESCRIPTIO || `Road ${ctr+1}`
      const barangay = props.BARANGAY || "Butuan City"
      const roadType = props.typeroad || "Unknown"
      const status = randomStatus()
      return {
        id: ctr,
        roadName: name,
        roadType,
        center,
        location: props.LOCATION || barangay,
        status,
        lastUpdated: "2025-07-28 " + (9+ctr)+":15",
        barangay,
        sensor: { cracks: Math.floor(Math.random()*12), traffic: ["Light","Moderate","Heavy"][ctr%3], temp: (33+Math.random()*6).toFixed(1) },
        aiAnalysis: randomAI(status),
        photos: [
          { name: `${name} Main`, img: `/images/${(ctr%5)+1}.jpg` }
        ],
        _raw: f
      }
    })
  loading.value = false
})

const sortedFeatures = computed(() =>
  [...roadRows.value].sort((a, b) => {
    const order = { "Hazard": 1, "Needs Maintenance": 2, "Offline": 3, "OK": 4 }
    return order[a.status] - order[b.status]
  })
)
function handleViewRow(row: any) {
  highlightFeature.value = row
}
</script>

<template>
  <Head title="Live Road Monitoring" />
  <AppLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-100">Live Road Monitoring</h1>
      <div class="relative min-h-[600px] flex-1 rounded-xl border p-2 border-sidebar-border/70 dark:border-sidebar-border">
        <RoadMonitoringPlaceHolder
          :kml-layers="kmlLayers"
          :features="sortedFeatures"
          :highlight-feature="highlightFeature"
        />
      </div>
      <!-- Road Status Color Legend -->
<div class="flex flex-wrap gap-5 items-center mb-6 mt-6">
  <div class="flex items-center space-x-2">
    <span class="text-sm font-semibold text-black-700">Legends: </span>
  </div>
   <div class="flex items-center space-x-2">
    <span class="inline-block w-5 h-5 rounded-full bg-green-500 border-2 border-white"></span>
    <span class="text-sm font-semibold text-green-700">OK (Passable)</span>
  </div>
  <div class="flex items-center space-x-2">
    <span class="inline-block w-5 h-5 rounded-full bg-yellow-400 border-2 border-white"></span>
    <span class="text-sm font-semibold text-yellow-700">Needs Maintenance</span>
  </div>
  <div class="flex items-center space-x-2">
    <span class="inline-block w-5 h-5 rounded-full animate-pulse bg-red-600 border-2 border-white"></span>
    <span class="text-sm font-semibold text-red-700">Needs Urgent Repair</span>
  </div>
  <div class="flex items-center space-x-2">
    <span class="inline-block w-5 h-5 rounded-full bg-gray-400 border-2 border-white"></span>
    <span class="text-sm font-semibold text-gray-700">Offline/No Data</span>
  </div>
</div>

      <div v-if="loading" class="my-6 text-center text-blue-500">Loading roads...</div>
      <div v-else class="overflow-x-auto rounded-lg border dark:border-gray-700 mt-4">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-100 dark:bg-gray-800">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-semibold">Road Name</th>
              <th class="px-4 py-2 text-left text-sm font-semibold">Road Type</th>
              <th class="px-4 py-2 text-left text-sm font-semibold">Location</th>
              <th class="px-4 py-2 text-left text-sm font-semibold">Status</th>
              <th class="px-4 py-2 text-left text-sm font-semibold">Last Updated</th>
              <th class="px-4 py-2 text-left text-sm font-semibold">Barangay</th>
              <th class="px-4 py-2 text-left text-sm font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in sortedFeatures"
              :key="row.id"
              :class="{
                'bg-red-50 animate-blink-hazard': row.status === 'Hazard',
                'bg-yellow-50': row.status === 'Needs Maintenance',
                'bg-gray-100': row.status === 'Offline',
                'bg-green-50': row.status === 'OK'
              }"
            >
              <td class="px-4 py-3 text-sm">{{ row.roadName }}</td>
              <td class="px-4 py-3 text-sm">{{ row.roadType }}</td>
              <td class="px-4 py-3 text-sm">{{ row.location }}</td>
              <td class="px-4 py-3 text-sm font-bold">
                <span
                  :class="{
                    'text-red-600': row.status === 'Hazard',
                    'text-yellow-600': row.status === 'Needs Maintenance',
                    'text-gray-500': row.status === 'Offline',
                    'text-green-600': row.status === 'OK'
                  }"
                >{{ row.status }}</span>
              </td>
              <td class="px-4 py-3 text-sm">{{ row.lastUpdated }}</td>
              <td class="px-4 py-3 text-sm">{{ row.barangay }}</td>
              <td class="px-4 py-3 text-sm space-x-2">
                <button
                  class="px-2 py-1 rounded bg-blue-500 text-white text-xs"
                  @click="handleViewRow(row)"
                >View</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<style>
@keyframes blink-hazard { 0%,100% { background: #fee2e2; } 50% { background: #fecaca; } }
.animate-blink-hazard { animation: blink-hazard 1s linear infinite; }
</style>
