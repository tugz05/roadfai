<template>
  <AppLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Backup & Restore History</h1>
      <p class="mb-6 text-sm text-gray-600 dark:text-gray-300">
        Below is the history of recent system backups and restore operations performed on the RoadFAI platform.
      </p>

      <div class="flex gap-4 mb-4">
        <button
          class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          @click="simulateBackup"
        >
          Initiate Backup
        </button>
        <button
          class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
          @click="simulateRestore"
        >
          Initiate Restore
        </button>
      </div>

      <div class="overflow-x-auto border rounded">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-100 dark:bg-gray-800">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Timestamp</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Operation</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Performed By</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">File Name</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Status</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Remarks</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="entry in history" :key="entry.id">
              <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ entry.timestamp }}</td>
              <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ entry.operation }}</td>
              <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ entry.user }}</td>
              <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ entry.filename }}</td>
              <td class="px-4 py-2 text-sm font-semibold" :class="{
                'text-green-600': entry.status === 'Success',
                'text-red-600': entry.status === 'Failed'
              }">
                {{ entry.status }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ entry.remarks }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

const history = ref([
  {
    id: 1,
    timestamp: '2025-07-26 10:15 AM',
    operation: 'Backup',
    user: 'admin',
    filename: 'roadfai_backup_2025_07_26.zip',
    status: 'Success',
    remarks: 'Automated daily backup.',
  },
  {
    id: 2,
    timestamp: '2025-07-25 11:45 PM',
    operation: 'Restore',
    user: 'sys_admin',
    filename: 'roadfai_backup_2025_07_25.zip',
    status: 'Success',
    remarks: 'Restored from last stable backup.',
  },
  {
    id: 3,
    timestamp: '2025-07-24 06:12 AM',
    operation: 'Backup',
    user: 'backup_service',
    filename: 'roadfai_backup_2025_07_24.zip',
    status: 'Success',
    remarks: 'Scheduled backup.',
  },
  {
    id: 4,
    timestamp: '2025-07-23 02:34 PM',
    operation: 'Restore',
    user: 'admin',
    filename: 'roadfai_backup_corrupt.zip',
    status: 'Failed',
    remarks: 'File corrupted. Restore aborted.',
  },
  {
    id: 5,
    timestamp: '2025-07-22 09:10 PM',
    operation: 'Backup',
    user: 'admin',
    filename: 'roadfai_backup_2025_07_22.zip',
    status: 'Success',
    remarks: 'Manual backup before system update.',
  },
]);

function simulateBackup() {
  const timestamp = new Date().toLocaleString();
  history.value.unshift({
    id: history.value.length + 1,
    timestamp,
    operation: 'Backup',
    user: 'admin',
    filename: `roadfai_backup_${Date.now()}.zip`,
    status: 'Success',
    remarks: 'Manual backup executed.',
  });
}

function simulateRestore() {
  const timestamp = new Date().toLocaleString();
  history.value.unshift({
    id: history.value.length + 1,
    timestamp,
    operation: 'Restore',
    user: 'admin',
    filename: `roadfai_restore_${Date.now()}.zip`,
    status: 'Success',
    remarks: 'Restore initiated by admin.',
  });
}
</script>
