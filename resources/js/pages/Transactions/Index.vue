<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { onMounted, reactive, ref } from 'vue';

const csrf = () => (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '';

type Branch = { id: number; name: string; code: string };
type Account = { id: number; name: string; code: string };

type Tx = {
  id: number;
  voucher_no: string;
  date: string;
  type: string;
  branch_id: number;
  amount: string;
  from_account_id: number;
  to_account_id: number;
  from_account?: Account;
  to_account?: Account;
  creator?: { id: number; name: string };
  voucher_image_url?: string | null;
};

const filters = reactive({
  date_from: '',
  date_to: '',
  branch_id: '',
  type: '',
  account_id: '',
});

const branches = ref<Branch[]>([]);
const accounts = ref<Account[]>([]);

const loading = ref(false);
const items = ref<Tx[]>([]);

async function loadBranches() {
  const res = await fetch('/api/branches', { headers: { 'Accept': 'application/json' } });
  branches.value = await res.json();
}
async function loadAccounts() {
  const res = await fetch('/api/accounts', { headers: { 'Accept': 'application/json' } });
  accounts.value = await res.json();
}

async function loadTransactions(pageUrl?: string) {
  loading.value = true;
  const params = new URLSearchParams();
  for (const [k, v] of Object.entries(filters)) {
    if (v) params.set(k, String(v));
  }
  const url = pageUrl ?? `/api/transactions?${params.toString()}`;
  const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
  const json = await res.json();
  items.value = json.data ?? [];
  loading.value = false;
}

function formatAmount(a: string | number) {
  const n = typeof a === 'string' ? parseFloat(a) : a;
  return new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
}

onMounted(async () => {
  await Promise.all([loadBranches(), loadAccounts()]);
  await loadTransactions();
});
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Transactions', href: '/transactions' }]"><div class="px-6 py-4 space-y-6">
    <div class="flex justify-end">
      <a href="/transactions/create" class="bg-blue-600 text-white px-4 py-2 rounded">Create Transaction</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
      <div>
        <label class="block text-sm mb-1">Branch</label>
        <select v-model="filters.branch_id" class="border rounded px-2 py-1 w-full">
          <option value="">All</option>
          <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.code }} - {{ b.name }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm mb-1">Type</label>
        <select v-model="filters.type" class="border rounded px-2 py-1 w-full">
          <option value="">All</option>
          <option value="cash">Cash</option>
          <option value="bank">Bank</option>
          <option value="contra">Contra</option>
          <option value="journal">Journal</option>
        </select>
      </div>
      <div>
        <label class="block text-sm mb-1">Account</label>
        <select v-model="filters.account_id" class="border rounded px-2 py-1 w-full">
          <option value="">All</option>
          <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} - {{ a.name }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm mb-1">From</label>
        <input type="date" v-model="filters.date_from" class="border rounded px-2 py-1 w-full"/>
      </div>
      <div>
        <label class="block text-sm mb-1">To</label>
        <input type="date" v-model="filters.date_to" class="border rounded px-2 py-1 w-full"/>
      </div>
      <div class="flex items-end">
        <button class="bg-gray-800 text-white px-4 py-2 rounded w-full" @click="loadTransactions()">Filter</button>
      </div>
    </div>

    <div class="overflow-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left border-b">
            <th class="px-2 py-2">Voucher</th>
            <th class="px-2 py-2">Date</th>
            <th class="px-2 py-2">Branch</th>
            <th class="px-2 py-2">From</th>
            <th class="px-2 py-2">To</th>
            <th class="px-2 py-2">Type</th>
            <th class="px-2 py-2">Created By</th>
            <th class="px-2 py-2 text-right">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="t in items" :key="t.id" class="border-b">
            <td class="px-2 py-2 font-mono">
              {{ t.voucher_no }}
              <a v-if="t.voucher_image_url" :href="t.voucher_image_url" target="_blank" class="ml-2 text-blue-600 underline">file</a>
            </td>
            <td class="px-2 py-2">{{ t.date }}</td>
            <td class="px-2 py-2">{{ branches.find(b => b.id === t.branch_id)?.code }}</td>
            <td class="px-2 py-2">{{ t.from_account?.code }} - {{ t.from_account?.name }}</td>
            <td class="px-2 py-2">{{ t.to_account?.code }} - {{ t.to_account?.name }}</td>
            <td class="px-2 py-2 capitalize">{{ t.type }}</td>
            <td class="px-2 py-2">{{ t.creator?.name }}</td>
            <td class="px-2 py-2 text-right">{{ formatAmount(t.amount) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div></AppLayout>
</template>
