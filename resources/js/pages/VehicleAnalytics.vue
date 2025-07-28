<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Bar } from 'vue-chartjs';
import Map from '@/pages/placeholders/Map.vue';
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

const startDate = ref('');
const endDate = ref('');

const vehicleData = ref([
  { day: 'Monday', count: 320 },
  { day: 'Tuesday', count: 410 },
  { day: 'Wednesday', count: 300 },
  { day: 'Thursday', count: 500 },
  { day: 'Friday', count: 620 },
  { day: 'Saturday', count: 780 },
  { day: 'Sunday', count: 430 },
]);

const chartData = {
  labels: vehicleData.value.map((d) => d.day),
  datasets: [
    {
      label: 'Number of Vehicles per Day',
      data: vehicleData.value.map((d) => d.count),
      backgroundColor: '#3B82F6',
    },
  ],
};

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: {
        color: '#6b7280',
      },
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
</script>

<template>
  <Head title="Vehicle Analytics" />
  <AppLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-100">Vehicle Analytics</h1>
      <p class="mb-6 text-sm text-gray-600 dark:text-gray-300">
        This chart represents the number of vehicles detected each day across RoadFAI monitored roads.
      </p>

      <!-- Date Filter -->
      <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
          <input type="date" v-model="startDate" class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-800 dark:text-white" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
          <input type="date" v-model="endDate" class="mt-1 w-full rounded border px-3 py-2 dark:bg-gray-800 dark:text-white" />
        </div>
      </div>

      <div class="h-[400px] w-full rounded-xl border p-4 dark:border-gray-700 bg-white dark:bg-gray-900 mb-8">
        <Bar :data="chartData" :options="chartOptions" />
      </div>

    </div>
  </AppLayout>
</template>
