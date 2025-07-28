<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Map from '@/pages/placeholders/Map.vue'

// Filters
const selectedBarangay = ref('All');
const barangayOptions = ['All', 'Mabini', 'San Vicente', 'Bonbon'];

// Dummy sensor data
const sensorNodes = ref([
  { id: 1, location: 'Purok 1, Barangay Mabini', status: 'OK', lastUpdated: '2025-07-26 12:30 PM', barangay: 'Mabini' },
  { id: 2, location: 'Highway, Barangay San Vicente', status: 'Needs Maintenance', lastUpdated: '2025-07-26 11:00 AM', barangay: 'San Vicente' },
  { id: 3, location: 'Near Public Market, Barangay Bonbon', status: 'Offline', lastUpdated: '2025-07-25 4:45 PM', barangay: 'Bonbon' },
  { id: 4, location: 'Corner Road, Barangay Mabini', status: 'OK', lastUpdated: '2025-07-26 12:00 PM', barangay: 'Mabini' },
  { id: 5, location: 'Elementary School, Barangay San Vicente', status: 'OK', lastUpdated: '2025-07-26 10:15 AM', barangay: 'San Vicente' },
  { id: 6, location: 'Crossing, Barangay Bonbon', status: 'Needs Maintenance', lastUpdated: '2025-07-26 09:30 AM', barangay: 'Bonbon' },
]);

// Polling setup
const interval = ref<number | null>(null);
onMounted(() => {
  interval.value = setInterval(() => {
    console.log('Polling sensor data...');
    // TODO: Replace with API fetch
  }, 10000);
});
onBeforeUnmount(() => {
  if (interval.value) clearInterval(interval.value);
});

// Filtering
const filteredSensors = computed(() => {
  if (selectedBarangay.value === 'All') return sensorNodes.value;
  return sensorNodes.value.filter(s => s.barangay === selectedBarangay.value);
});

// Sorting
const sortKey = ref('lastUpdated');
const sortAsc = ref(false);
function toggleSort(key: string) {
  if (sortKey.value === key) sortAsc.value = !sortAsc.value;
  else {
    sortKey.value = key;
    sortAsc.value = true;
  }
}
const sortedSensors = computed(() => {
  return [...filteredSensors.value].sort((a, b) => {
    const valA = a[sortKey.value];
    const valB = b[sortKey.value];
    if (valA < valB) return sortAsc.value ? -1 : 1;
    if (valA > valB) return sortAsc.value ? 1 : -1;
    return 0;
  });
});

// Pagination
const currentPage = ref(1);
const pageSize = 6;
const totalPages = computed(() => Math.ceil(sortedSensors.value.length / pageSize));
const paginatedSensors = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return sortedSensors.value.slice(start, start + pageSize);
});
</script>

<template>
  <Head title="Live Road Monitoring" />
  <AppLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-100">Live Road Monitoring</h1>
        <div class="relative min-h-[400px] flex-1 rounded-xl border p-2 border-sidebar-border/70 dark:border-sidebar-border">
        <Map
                        :landuse-kml-url="'/kml/landuse_KML.kml'"
                        :roads-kml-url="'/kml/TNDG_ROADNETWORKS_KML.kml'"
                            />
      </div>
      <div class="mb-4">
        <label for="barangay" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300 mt-4">Filter by Barangay</label>
        <select
          id="barangay"
          v-model="selectedBarangay"
          class="w-full max-w-xs rounded border px-4 py-2 dark:bg-gray-800 dark:text-white"
        >
          <option v-for="barangay in barangayOptions" :key="barangay" :value="barangay">{{ barangay }}</option>
        </select>
      </div>

      <div class="overflow-x-auto rounded-lg border dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-100 dark:bg-gray-800">
            <tr>
              <th @click="toggleSort('location')" class="px-4 py-2 cursor-pointer text-left text-sm font-semibold text-gray-700 dark:text-gray-200">Location</th>
              <th @click="toggleSort('status')" class="px-4 py-2 cursor-pointer text-left text-sm font-semibold text-gray-700 dark:text-gray-200">Status</th>
              <th @click="toggleSort('lastUpdated')" class="px-4 py-2 cursor-pointer text-left text-sm font-semibold text-gray-700 dark:text-gray-200">Last Updated</th>
              <th @click="toggleSort('barangay')" class="px-4 py-2 cursor-pointer text-left text-sm font-semibold text-gray-700 dark:text-gray-200">Barangay</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="sensor in paginatedSensors" :key="sensor.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ sensor.location }}</td>
              <td class="px-4 py-3 text-sm">
                <span
                  :class="{
                    'text-green-600 font-semibold': sensor.status === 'OK',
                    'text-yellow-600 font-semibold': sensor.status === 'Needs Maintenance',
                    'text-red-600 font-semibold': sensor.status === 'Offline',
                  }"
                >
                  {{ sensor.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ sensor.lastUpdated }}</td>
              <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ sensor.barangay }}</td>
              <td class="px-4 py-3 text-sm space-x-2">
                <button class="px-2 py-1 rounded bg-blue-500 text-white text-xs">View</button>
                <button class="px-2 py-1 rounded bg-yellow-500 text-white text-xs">Edit</button>
                <button class="px-2 py-1 rounded bg-red-500 text-white text-xs">Deactivate</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex justify-between items-center">
        <button
          :disabled="currentPage === 1"
          @click="currentPage--"
          class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 disabled:opacity-50"
        >Previous</button>
        <span class="text-sm dark:text-gray-300">Page {{ currentPage }} of {{ totalPages }}</span>
        <button
          :disabled="currentPage === totalPages"
          @click="currentPage++"
          class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 disabled:opacity-50"
        >Next</button>
      </div>
    </div>
  </AppLayout>
</template>
