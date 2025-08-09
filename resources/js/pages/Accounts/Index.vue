<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { onMounted, reactive, ref, computed } from 'vue';

const csrf = () => (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '';

type Account = {
  id: number;
  code: string;
  name: string;
  category: 'asset'|'liability'|'equity'|'income'|'expense';
  is_bank: boolean;
  is_active: boolean;
};

const filters = reactive({
  category: '',
  is_bank: '',
  active: '1',
});

const loading = ref(false);
const accounts = ref<Account[]>([]);

async function loadAccounts() {
  loading.value = true;
  const params = new URLSearchParams();
  if (filters.category) params.set('category', filters.category);
  if (filters.is_bank) params.set('is_bank', filters.is_bank);
  if (filters.active) params.set('active', filters.active);
  const res = await fetch(`/api/accounts?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
  accounts.value = await res.json();
  loading.value = false;
}

const form = reactive({
  code: '',
  name: '',
  category: 'asset',
  is_bank: false,
});

async function createAccount() {
  const res = await fetch('/api/accounts', {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf(),
    },
    body: JSON.stringify(form),
  });
  if (res.ok) {
    form.code = '';
    form.name = '';
    form.category = 'asset';
    form.is_bank = false;
    await loadAccounts();
  } else {
    const err = await res.json().catch(() => ({}));
    alert('Failed to create account' + (err.message ? `: ${err.message}` : ''));
  }
}

async function toggleActive(acc: Account) {
  const res = await fetch(`/api/accounts/${acc.id}/toggle`, {
    method: 'PATCH',
    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
  });
  if (res.ok) {
    await loadAccounts();
  }
}

onMounted(loadAccounts);
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Accounts', href: '/accounts' }]"><div class="px-6 py-4 space-y-6">
    <div class="flex items-end gap-3">
      <div>
        <label class="block text-sm mb-1">Category</label>
        <select v-model="filters.category" class="border rounded px-2 py-1">
          <option value="">All</option>
          <option value="asset">Asset</option>
          <option value="liability">Liability</option>
          <option value="equity">Equity</option>
          <option value="income">Income</option>
          <option value="expense">Expense</option>
        </select>
      </div>
      <div>
        <label class="block text-sm mb-1">Is Bank</label>
        <select v-model="filters.is_bank" class="border rounded px-2 py-1">
          <option value="">All</option>
          <option value="1">Yes</option>
          <option value="0">No</option>
        </select>
      </div>
      <div>
        <label class="block text-sm mb-1">Active</label>
        <select v-model="filters.active" class="border rounded px-2 py-1">
          <option value="">All</option>
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>
      <button class="ml-auto bg-gray-800 text-white px-4 py-2 rounded" @click="loadAccounts">Filter</button>
    </div>

    <div class="bg-white dark:bg-gray-900 border rounded p-4 space-y-3">
      <h3 class="font-semibold">Create Account</h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input v-model="form.code" placeholder="Code" class="border rounded px-2 py-1" />
        <input v-model="form.name" placeholder="Name" class="border rounded px-2 py-1" />
        <select v-model="form.category" class="border rounded px-2 py-1">
          <option value="asset">Asset</option>
          <option value="liability">Liability</option>
          <option value="equity">Equity</option>
          <option value="income">Income</option>
          <option value="expense">Expense</option>
        </select>
        <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="form.is_bank" /> <span>Is Bank</span></label>
      </div>
      <div>
        <button class="bg-blue-600 text-white px-4 py-2 rounded" @click="createAccount">Save</button>
      </div>
    </div>

    <div class="overflow-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left border-b">
            <th class="px-2 py-2">Code</th>
            <th class="px-2 py-2">Name</th>
            <th class="px-2 py-2">Category</th>
            <th class="px-2 py-2">Bank</th>
            <th class="px-2 py-2">Active</th>
            <th class="px-2 py-2">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="acc in accounts" :key="acc.id" class="border-b">
            <td class="px-2 py-2 font-mono">{{ acc.code }}</td>
            <td class="px-2 py-2">{{ acc.name }}</td>
            <td class="px-2 py-2">{{ acc.category }}</td>
            <td class="px-2 py-2">
              <span class="px-2 py-1 rounded text-xs" :class="acc.is_bank ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'">
                {{ acc.is_bank ? 'Yes' : 'No' }}
              </span>
            </td>
            <td class="px-2 py-2">
              <span class="px-2 py-1 rounded text-xs" :class="acc.is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'">
                {{ acc.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-2 py-2">
              <button class="text-blue-600" @click="toggleActive(acc)">Toggle</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div></AppLayout>
</template>
