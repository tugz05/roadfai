<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  Car, RadioTower, AlertTriangle, Clock, MapPin, Wrench, Users,
  Settings, FileText, ActivitySquare,
} from 'lucide-vue-next'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale,
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

// Utilities
const numberFormat = (n: number) => n > 999 ? n.toLocaleString() : n.toString()
const totalSensors = 25

// Reactive card states
const totalVehicles = ref(1245)
const activeSensors = ref(22)
const offlineSensors = ref(totalSensors - activeSensors.value)
const dashboardCards = ref([
  {
    title: 'Total Vehicles Today', value: totalVehicles, icon: Car, href: '/vehicle_analytics', bg: 'bg-blue-100 dark:bg-blue-900', iconColor: 'text-blue-700 dark:text-blue-300', isNumeric: true,
  },
  {
    title: 'Active Sensors', value: activeSensors, icon: RadioTower, href: '/road_monitoring', bg: 'bg-green-100 dark:bg-green-900', iconColor: 'text-green-700 dark:text-green-300', isNumeric: true,
  },
  {
    title: 'Offline Sensors', value: offlineSensors, icon: ActivitySquare, href: '/road_monitoring', bg: 'bg-gray-100 dark:bg-gray-800', iconColor: 'text-gray-700 dark:text-gray-300', isNumeric: true,
  },
  {
    title: 'Alerts Triggered', value: 3, icon: AlertTriangle, href: '/alerts', bg: 'bg-red-100 dark:bg-red-900', iconColor: 'text-red-700 dark:text-red-300', isNumeric: true,
  },
  {
    title: 'Pending Maintenance', value: 5, icon: Wrench, href: '/maintenance', bg: 'bg-yellow-100 dark:bg-yellow-900', iconColor: 'text-yellow-700 dark:text-yellow-300', isNumeric: true,
  },
  {
    title: 'Barangays Covered', value: 12, icon: MapPin, href: '/map_view', bg: 'bg-indigo-100 dark:bg-indigo-900', iconColor: 'text-indigo-700 dark:text-indigo-300', isNumeric: true,
  },
  {
    title: 'Historical Logs', value: '7 days', icon: Clock, href: '/history', bg: 'bg-teal-100 dark:bg-teal-900', iconColor: 'text-teal-700 dark:text-teal-300', isNumeric: false,
  },
  {
    title: 'Reports Generated', value: 4, icon: FileText, href: '/reports', bg: 'bg-purple-100 dark:bg-purple-900', iconColor: 'text-purple-700 dark:text-purple-300', isNumeric: true,
  },
  {
    title: 'Users', value: 50, icon: Users, href: '/users', bg: 'bg-orange-100 dark:bg-orange-900', iconColor: 'text-orange-700 dark:text-orange-300', isNumeric: true,
  },
  {
    title: 'Config Status', value: 'Updated', icon: Settings, href: '/settings', bg: 'bg-sky-100 dark:bg-sky-900', iconColor: 'text-sky-700 dark:text-sky-300', isNumeric: false,
  },
])

let interval: ReturnType<typeof setInterval> | null = null

onMounted(() => {
  interval = setInterval(() => {
    // Increment total vehicles
    totalVehicles.value += Math.floor(Math.random() * 10) + 1

    // Simulate Active/Offline Sensors changing, but always sum to totalSensors
    let newActive = activeSensors.value + Math.floor(Math.random() * 3) - 1 // -1, 0, or +1
    newActive = Math.max(15, Math.min(25, newActive))
    activeSensors.value = newActive
    offlineSensors.value = totalSensors - newActive

    // Update dashboardCards
    dashboardCards.value = dashboardCards.value.map(card => {
      if (card.title === 'Total Vehicles Today') return { ...card, value: totalVehicles }
      if (card.title === 'Active Sensors') return { ...card, value: activeSensors }
      if (card.title === 'Offline Sensors') return { ...card, value: offlineSensors }
      return card
    })
  }, 1700)
})

onBeforeUnmount(() => {
  if (interval) clearInterval(interval)
})

// Chart.js dummy data
const chartData = {
  labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
  datasets: [
    {
      label: 'Vehicles',
      data: [230, 420, 510, 390, 610, 770, 520],
      backgroundColor: '#3B82F6',
    },
  ],
}
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: { color: '#6b7280' },
    },
  },
  scales: {
    x: { ticks: { color: '#6b7280' }, grid: { color: '#e5e7eb' } },
    y: { ticks: { color: '#6b7280' }, grid: { color: '#e5e7eb' } },
  },
}
</script>

<template>
  <Head title="Dashboard" />
  <AppLayout>
    <div class="grid grid-cols-2 gap-4 p-6 sm:grid-cols-3 lg:grid-cols-5">
      <div
        v-for="(card, index) in dashboardCards"
        :key="index"
        class="aspect-square cursor-pointer rounded-xl border p-4 shadow-sm hover:shadow-md transition dark:border-sidebar-border flex flex-col justify-center items-center text-center"
        @click="$inertia.visit(card.href)"
      >
        <div :class="['rounded-full p-6 mb-4', card.bg]">
          <component :is="card.icon" class="h-6 w-6" :class="card.iconColor" />
        </div>
        <h3 class="text-lg font-medium text-gray-600 dark:text-gray-400">{{ card.title }}</h3>
        <p class="text-5xl font-bold text-gray-900 dark:text-white">
          <template v-if="card.isNumeric">{{ numberFormat(card.value) }}</template>
          <template v-else>{{ card.value }}</template>
        </p>
      </div>
      <div class="col-span-full mt-6 rounded-xl border p-6 shadow-md dark:border-sidebar-border bg-white dark:bg-gray-900">
        <h2 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-300">Vehicle Count (Last 7 Days)</h2>
        <div class="h-72 w-full">
          <Bar :data="chartData" :options="chartOptions" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
