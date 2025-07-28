<template>
  <AppLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Historical Road Usage Data</h1>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
          <label class="block text-sm font-medium">Filter by Zone</label>
          <select v-model="selectedZone" class="w-full rounded border px-3 py-2">
            <option value="All">All</option>
            <option v-for="zone in zoneOptions" :key="zone" :value="zone">{{ zone }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium">From</label>
          <input type="date" v-model="dateFrom" class="w-full rounded border px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm font-medium">To</label>
          <input type="date" v-model="dateTo" class="w-full rounded border px-3 py-2" />
        </div>
      </div>

      <div class="flex justify-between mb-4">
        <div>
          <button @click="exportCSV" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Export CSV</button>
          <button @click="printPage" class="ml-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Print</button>
        </div>
        <div>
          <input v-model="searchTerm" type="text" placeholder="Search..." class="px-3 py-2 border rounded" />
        </div>
      </div>

      <div class="h-[300px] mb-6">
        <Line :data="chartData" :options="chartOptions" />
      </div>

      <div class="overflow-auto border rounded">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2">Date</th>
              <th class="px-4 py-2">Zone</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Vehicle Count</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="entry in paginatedData" :key="entry.id" class="border-t">
              <td class="px-4 py-2">{{ entry.date }}</td>
              <td class="px-4 py-2">{{ entry.zone }}</td>
              <td class="px-4 py-2">{{ entry.status }}</td>
              <td class="px-4 py-2">{{ entry.vehicleCount }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex justify-between items-center">
        <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1 bg-gray-300 rounded disabled:opacity-50">Prev</button>
        <span>Page {{ currentPage }} of {{ totalPages }}</span>
        <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1 bg-gray-300 rounded disabled:opacity-50">Next</button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
} from 'chart.js';
ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale);

const zoneOptions = ['North', 'South', 'East', 'West'];
const selectedZone = ref('All');
const dateFrom = ref('2024-01-01');
const dateTo = ref('2024-12-31');
const searchTerm = ref('');
const currentPage = ref(1);
const perPage = 10;

const historicalData = ref([
  ...Array.from({ length: 30 }).map((_, i) => ({
    id: i + 1,
    date: `2024-07-${(i % 30 + 1).toString().padStart(2, '0')}`,
    zone: zoneOptions[i % zoneOptions.length],
    status: ['OK', 'Needs Maintenance', 'Offline'][i % 3],
    vehicleCount: Math.floor(Math.random() * 500 + 100),
  }))
]);

const filteredData = computed(() => {
  return historicalData.value.filter(entry => {
    const inZone = selectedZone.value === 'All' || entry.zone === selectedZone.value;
    const inDate = entry.date >= dateFrom.value && entry.date <= dateTo.value;
    const matchesSearch = entry.zone.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      entry.status.toLowerCase().includes(searchTerm.value.toLowerCase());
    return inZone && inDate && matchesSearch;
  });
});

const totalPages = computed(() => Math.ceil(filteredData.value.length / perPage));
const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredData.value.slice(start, start + perPage);
});

function prevPage() {
  if (currentPage.value > 1) currentPage.value--;
}
function nextPage() {
  if (currentPage.value < totalPages.value) currentPage.value++;
}

function exportCSV() {
  const rows = [
    ['Date', 'Zone', 'Status', 'Vehicle Count'],
    ...filteredData.value.map(e => [e.date, e.zone, e.status, e.vehicleCount])
  ];
  const csv = rows.map(row => row.join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', 'historical_data.csv');
  link.click();
}

function printPage() {
  window.print();
}

const chartData = computed(() => {
  const labels = [...new Set(filteredData.value.map(e => e.date))];
  const data = labels.map(label => {
    const group = filteredData.value.filter(e => e.date === label);
    return group.reduce((sum, entry) => sum + entry.vehicleCount, 0);
  });
  return {
    labels,
    datasets: [
      {
        label: 'Vehicle Count Over Time',
        data,
        borderColor: '#3b82f6',
        tension: 0.3,
        fill: false,
      },
    ],
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: { color: '#374151' },
    },
  },
  scales: {
    x: {
      ticks: { color: '#374151' },
      grid: { color: '#e5e7eb' },
    },
    y: {
      ticks: { color: '#374151' },
      grid: { color: '#e5e7eb' },
    },
  },
};
</script>

<style>
@media print {
  button, select, input {
    display: none !important;
  }
  .h-[300px], .overflow-auto, .mt-4 {
    page-break-before: always;
  }
}
</style>
