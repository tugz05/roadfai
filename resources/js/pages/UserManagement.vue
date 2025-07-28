<template>
  <AppLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">User Management</h1>
      <p class="mb-6 text-sm text-gray-600 dark:text-gray-300">
        Manage access and roles of personnel who use the RoadFAI platform.
      </p>

      <div class="overflow-x-auto border rounded">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-100 dark:bg-gray-800">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Name</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Email</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Role</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Status</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="user in paginatedUsers" :key="user.id">
              <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ user.name }}</td>
              <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ user.email }}</td>
              <td class="px-4 py-2 text-sm">{{ user.role }}</td>
              <td class="px-4 py-2 text-sm" :class="{
                'text-green-600': user.status === 'Active',
                'text-red-600': user.status === 'Inactive'
              }">
                {{ user.status }}
              </td>
              <td class="px-4 py-2 text-sm">
                <button class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 mr-2">
                  Edit
                </button>
                <button class="px-3 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">
                  Deactivate
                </button>
              </td>
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
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

const roles = ['Administrator', 'Engineer', 'Viewer'];
const statuses = ['Active', 'Inactive'];

const users = ref(Array.from({ length: 50 }, (_, i) => ({
  id: i + 1,
  name: `User ${i + 1}`,
  email: `user${i + 1}@roadfai.com`,
  role: roles[i % roles.length],
  status: statuses[i % statuses.length],
})));

const currentPage = ref(1);
const perPage = 10;
const totalPages = computed(() => Math.ceil(users.value.length / perPage));
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return users.value.slice(start, start + perPage);
});
function prevPage() {
  if (currentPage.value > 1) currentPage.value--;
}
function nextPage() {
  if (currentPage.value < totalPages.value) currentPage.value++;
}
</script>
