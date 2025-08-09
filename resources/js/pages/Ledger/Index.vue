<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { onMounted, reactive, ref } from 'vue';

type Branch = { id: number; name: string; code: string };
type Account = { id: number; name: string; code: string };

type LedgerLine = {
  id: number;
  date: string;
  voucher_no: string;
  narration?: string;
  branch?: string;
  type?: string;
  debit: string;
  credit: string;
  balance: string;
};

const branches = ref<Branch[]>([]);
const accounts = ref<Account[]>([]);
const lines = ref<LedgerLine[]>([]);
const accountMeta = ref<Account | null>(null);

const filters = reactive({
  account_id: '',
  branch_id: '',
  date_from: '',
  date_to: '',
});

const loading = ref(false);

async function loadBranches() {
  const res = await fetch('/api/branches', { headers: { Accept: 'application/json' } });
  branches.value = await res.json();
}
async function loadAccounts() {
  const res = await fetch('/api/accounts', { headers: { Accept: 'application/json' } });
  accounts.value = await res.json();
}

async function loadLedger() {
  if (!filters.account_id) {
    lines.value = [];
    accountMeta.value = null;
    return;
  }
  loading.value = true;
  const params = new URLSearchParams();
  for (const [k, v] of Object.entries(filters)) {
    if (v) params.set(k, String(v));
  }
  const res = await fetch(`/api/ledger?${params.toString()}`, { headers: { Accept: 'application/json' } });
  const json = await res.json();
  lines.value = json.lines ?? [];
  accountMeta.value = json.account ?? null;
  loading.value = false;
}

function formatAmount(a: string | number) {
  const n = typeof a === 'string' ? parseFloat(a) : a;
  return new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
}

onMounted(async () => {
  await Promise.all([loadBranches(), loadAccounts()]);
});
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Ledger', href: '/ledger' }]"><div class="px-6 py-4 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
      <div class="md:col-span-2">
        <label class="block text-sm mb-1">Account</label>
        <select v-model="filters.account_id" class="border rounded px-2 py-1 w-full">
          <option value="">Select account...</option>
          <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} - {{ a.name }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm mb-1">Branch</label>
        <select v-model="filters.branch_id" class="border rounded px-2 py-1 w-full">
          <option value="">All</option>
          <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.code }} - {{ b.name }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm mb-1">From</label>
        <input type="date" v-model="filters.date_from" class="border rounded px-2 py-1 w-full" />
      </div>
      <div>
        <label class="block text-sm mb-1">To</label>
        <input type="date" v-model="filters.date_to" class="border rounded px-2 py-1 w-full" />
      </div>
      <div class="flex items-end">
        <button class="bg-gray-800 text-white px-4 py-2 rounded w-full" @click="loadLedger">View</button>
      </div>
    </div>

    <div v-if="accountMeta" class="text-sm text-gray-600 dark:text-gray-300">
      Showing ledger for <span class="font-medium">{{ accountMeta.code }} - {{ accountMeta.name }}</span>
    </div>

    <div class="overflow-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left border-b">
            <th class="px-2 py-2">Date</th>
            <th class="px-2 py-2">Voucher</th>
            <th class="px-2 py-2">Narration</th>
            <th class="px-2 py-2">Branch</th>
            <th class="px-2 py-2 text-right">Debit</th>
            <th class="px-2 py-2 text-right">Credit</th>
            <th class="px-2 py-2 text-right">Balance</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in lines" :key="row.id" class="border-b">
            <td class="px-2 py-2">{{ row.date }}</td>
            <td class="px-2 py-2 font-mono">{{ row.voucher_no }}</td>
            <td class="px-2 py-2">{{ row.narration }}</td>
            <td class="px-2 py-2">{{ row.branch }}</td>
            <td class="px-2 py-2 text-right">{{ formatAmount(row.debit) }}</td>
            <td class="px-2 py-2 text-right">{{ formatAmount(row.credit) }}</td>
            <td class="px-2 py-2 text-right font-medium">{{ formatAmount(row.balance) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div></AppLayout>
</template>
