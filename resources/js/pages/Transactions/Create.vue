<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { onMounted, reactive, ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const csrf = () => (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '';

type Branch = { id: number; name: string; code: string };
type Account = { id: number; name: string; code: string };

const branches = ref<Branch[]>([]);
const accounts = ref<Account[]>([]);

const today = new Date();
const yyyy = today.getFullYear();
const mm = String(today.getMonth() + 1).padStart(2, '0');
const dd = String(today.getDate()).padStart(2, '0');

const form = reactive({
  date: `${yyyy}-${mm}-${dd}`,
  branch_id: '',
  type: 'cash',
  from_account_id: '',
  to_account_id: '',
  amount: '',
  narration: '',
  bank_name: '',
  cheque_no: '',
  cheque_date: '',
});

const submitting = ref(false);
const errorMsg = ref('');

// Optional voucher image upload
const voucherImage = ref<File | null>(null);
const voucherPreview = ref<string>('');
const voucherPreviewType = ref<'image' | 'file' | ''>('');
const voucherFileName = ref<string>('');

const showBank = computed(() => form.type === 'bank');

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement;
  const file = input.files?.[0] || null;
  voucherImage.value = file;
  voucherPreview.value = '';
  voucherPreviewType.value = '';
  voucherFileName.value = '';
  if (file) {
    voucherFileName.value = file.name;
    if (file.type.startsWith('image/')) {
      voucherPreviewType.value = 'image';
      voucherPreview.value = URL.createObjectURL(file);
    } else {
      voucherPreviewType.value = 'file';
    }
  }
}

async function loadBranches() {
  const res = await fetch('/api/branches', { headers: { 'Accept': 'application/json' } });
  branches.value = await res.json();
  if (!form.branch_id && branches.value.length) {
    const ho = branches.value.find(b => b.code === 'HO');
    form.branch_id = String(ho?.id ?? branches.value[0].id);
  }
}

async function loadAccounts() {
  const res = await fetch('/api/accounts', { headers: { 'Accept': 'application/json' } });
  accounts.value = await res.json();
}

async function submit() {
  errorMsg.value = '';
  submitting.value = true;
  try {
    // Submit as multipart/form-data (supports optional file)
    const fd = new FormData();
    fd.append('date', form.date);
    fd.append('branch_id', String(form.branch_id));
    fd.append('type', form.type);
    fd.append('from_account_id', String(form.from_account_id));
    fd.append('to_account_id', String(form.to_account_id));
    fd.append('amount', String(form.amount));
    if (form.narration) fd.append('narration', form.narration);
    if (showBank.value) {
      if (form.bank_name) fd.append('bank_name', form.bank_name);
      if (form.cheque_no) fd.append('cheque_no', form.cheque_no);
      if (form.cheque_date) fd.append('cheque_date', form.cheque_date);
    }
    if (voucherImage.value) {
      fd.append('voucher_image', voucherImage.value);
    }

    const res = await fetch('/api/transactions', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf(),
      },
      body: fd,
    });
    if (!res.ok) {
      const data = await res.json().catch(() => ({}));
      throw new Error(data.message || 'Failed to create transaction');
    }
    // Redirect to list
    window.location.href = '/transactions';
  } catch (e: any) {
    errorMsg.value = e?.message ?? 'Failed';
  } finally {
    submitting.value = false;
  }
}

onMounted(async () => {
  await Promise.all([loadBranches(), loadAccounts()]);
});
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Transactions', href: '/transactions' }, { title: 'Create', href: '/transactions/create' }]"><div class="px-6 py-4 space-y-6">
    <div class="max-w-3xl bg-white dark:bg-gray-900 border rounded p-4 space-y-4">
      <h2 class="text-lg font-semibold">New Transaction</h2>

      <div v-if="errorMsg" class="text-red-600 text-sm">{{ errorMsg }}</div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm mb-1">Date</label>
          <input type="date" v-model="form.date" class="border rounded px-2 py-1 w-full" />
        </div>
        <div>
          <label class="block text-sm mb-1">Branch</label>
          <select v-model="form.branch_id" class="border rounded px-2 py-1 w-full">
            <option v-for="b in branches" :key="b.id" :value="String(b.id)">{{ b.code }} - {{ b.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm mb-1">Type</label>
          <select v-model="form.type" class="border rounded px-2 py-1 w-full">
            <option value="cash">Cash</option>
            <option value="bank">Bank</option>
            <option value="contra">Contra</option>
            <option value="journal">Journal</option>
          </select>
        </div>
        <div>
          <label class="block text-sm mb-1">Amount</label>
          <input type="number" min="0" step="0.01" v-model="form.amount" placeholder="0.00" class="border rounded px-2 py-1 w-full" />
        </div>
        <div>
          <label class="block text-sm mb-1">From Account</label>
          <select v-model="form.from_account_id" class="border rounded px-2 py-1 w-full">
            <option value="">Select...</option>
            <option v-for="a in accounts" :key="a.id" :value="String(a.id)">{{ a.code }} - {{ a.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm mb-1">To Account</label>
          <select v-model="form.to_account_id" class="border rounded px-2 py-1 w-full">
            <option value="">Select...</option>
            <option v-for="a in accounts" :key="a.id" :value="String(a.id)">{{ a.code }} - {{ a.name }}</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm mb-1">Narration</label>
          <textarea v-model="form.narration" rows="2" class="border rounded px-2 py-1 w-full" placeholder="Note..."></textarea>
        </div>
      </div>

      <div v-if="showBank" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
          <label class="block text-sm mb-1">Bank Name</label>
          <input v-model="form.bank_name" class="border rounded px-2 py-1 w-full" />
        </div>
        <div>
          <label class="block text-sm mb-1">Cheque No</label>
          <input v-model="form.cheque_no" class="border rounded px-2 py-1 w-full" />
        </div>
        <div>
          <label class="block text-sm mb-1">Cheque Date</label>
          <input type="date" v-model="form.cheque_date" class="border rounded px-2 py-1 w-full" />
        </div>
      </div>

      <div class="space-y-2">
        <label class="block text-sm mb-1">Voucher Image (optional)</label>
        <input type="file" accept="image/*,.pdf" @change="onFileChange" />
        <div v-if="voucherPreview || voucherFileName" class="mt-1">
          <img v-if="voucherPreviewType==='image'" :src="voucherPreview" class="h-24 border rounded" />
          <div v-else class="text-xs text-gray-600">Selected: {{ voucherFileName }}</div>
        </div>
        <div class="text-xs text-gray-500">Allowed: JPG, PNG, PDF. Max 2MB.</div>
      </div>

      <div class="text-xs text-gray-500">Voucher No will be generated automatically on save.</div>

      <div class="flex items-center gap-3">
        <button :disabled="submitting" class="bg-blue-600 text-white px-4 py-2 rounded" @click="submit">Save</button>
        <a href="/transactions" class="px-3 py-2">Cancel</a>
      </div>
    </div>
  </div></AppLayout>
</template>
