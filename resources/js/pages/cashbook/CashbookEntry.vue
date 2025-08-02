<template>
  <div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Cashbook Entry</h1>
        <p class="text-gray-600 mt-1">Create new financial transactions with double-entry bookkeeping</p>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <form @submit.prevent="submitTransaction" class="space-y-6">
        <!-- Transaction Header -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <Label for="transaction_date">Transaction Date</Label>
            <Input
              id="transaction_date"
              v-model="form.transaction_date"
              type="date"
              :max="today"
              required
            />
            <div v-if="errors.transaction_date" class="text-red-500 text-sm mt-1">
              {{ errors.transaction_date[0] }}
            </div>
          </div>

          <div>
            <Label for="transaction_number">Transaction Number</Label>
            <Input
              id="transaction_number"
              v-model="form.transaction_number"
              placeholder="Auto-generated"
              readonly
              class="bg-gray-50"
            />
          </div>

          <div>
            <Label for="total_amount">Total Amount</Label>
            <Input
              id="total_amount"
              :value="formatCurrency(totalAmount)"
              readonly
              class="bg-gray-50 font-semibold"
              :class="isBalanced ? 'text-green-600' : 'text-red-600'"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <Label for="description">Description</Label>
            <Input
              id="description"
              v-model="form.description"
              placeholder="Enter transaction description"
              required
            />
            <div v-if="errors.description" class="text-red-500 text-sm mt-1">
              {{ errors.description[0] }}
            </div>
          </div>

          <div>
            <Label for="notes">Notes (Optional)</Label>
            <Textarea
              id="notes"
              v-model="form.notes"
              placeholder="Additional notes or reference"
              rows="2"
            />
          </div>
        </div>

        <!-- Ledger Entries -->
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Ledger Entries</h3>
            <div class="flex space-x-2">
              <Button type="button" @click="addEntry" variant="outline" size="sm">
                <Plus class="h-4 w-4 mr-1" />
                Add Entry
              </Button>
              <Button type="button" @click="showQuickTemplates = true" variant="ghost" size="sm">
                <FileText class="h-4 w-4 mr-1" />
                Templates
              </Button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full border border-gray-200 rounded-lg">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-900">Account</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-900">Description</th>
                  <th class="px-4 py-3 text-center text-sm font-medium text-gray-900">Debit</th>
                  <th class="px-4 py-3 text-center text-sm font-medium text-gray-900">Credit</th>
                  <th class="px-4 py-3 text-center text-sm font-medium text-gray-900">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(entry, index) in form.entries" :key="index" class="border-t">
                  <td class="px-4 py-3">
                    <Select v-model="entry.account_id" required>
                      <SelectTrigger>
                        <SelectValue placeholder="Select account" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectGroup v-for="(typeAccounts, type) in groupedAccounts" :key="type">
                          <SelectLabel>{{ formatAccountType(type) }}</SelectLabel>
                          <SelectItem 
                            v-for="account in typeAccounts" 
                            :key="account.id" 
                            :value="account.id.toString()"
                          >
                            {{ account.code }} - {{ account.name }}
                          </SelectItem>
                        </SelectGroup>
                      </SelectContent>
                    </Select>
                  </td>
                  <td class="px-4 py-3">
                    <Input
                      v-model="entry.description"
                      placeholder="Entry description"
                      class="w-full"
                    />
                  </td>
                  <td class="px-4 py-3">
                    <Input
                      v-model="entry.debit_amount"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="0.00"
                      class="text-center"
                      @input="updateEntryType(index, 'debit')"
                    />
                  </td>
                  <td class="px-4 py-3">
                    <Input
                      v-model="entry.credit_amount"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="0.00"
                      class="text-center"
                      @input="updateEntryType(index, 'credit')"
                    />
                  </td>
                  <td class="px-4 py-3 text-center">
                    <Button
                      type="button"
                      @click="removeEntry(index)"
                      variant="ghost"
                      size="sm"
                      :disabled="form.entries.length <= 2"
                    >
                      <Trash2 class="h-4 w-4 text-red-500" />
                    </Button>
                  </td>
                </tr>
              </tbody>
              <tfoot class="bg-gray-50 border-t">
                <tr>
                  <td colspan="2" class="px-4 py-3 font-semibold text-gray-900">Totals:</td>
                  <td class="px-4 py-3 text-center font-semibold text-green-600">
                    ৳{{ formatCurrency(totalDebits) }}
                  </td>
                  <td class="px-4 py-3 text-center font-semibold text-red-600">
                    ৳{{ formatCurrency(totalCredits) }}
                  </td>
                  <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center">
                      <CheckCircle v-if="isBalanced" class="h-5 w-5 text-green-500" />
                      <AlertCircle v-else class="h-5 w-5 text-red-500" />
                    </div>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div v-if="!isBalanced" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
              <AlertCircle class="h-5 w-5 text-red-500 mr-2" />
              <span class="text-red-700 font-medium">Transaction is not balanced!</span>
            </div>
            <p class="text-red-600 text-sm mt-1">
              Total debits (৳{{ formatCurrency(totalDebits) }}) must equal total credits (৳{{ formatCurrency(totalCredits) }}).
              Difference: ৳{{ formatCurrency(Math.abs(totalDebits - totalCredits)) }}
            </p>
          </div>

          <div v-if="errors.entries" class="text-red-500 text-sm">
            {{ errors.entries[0] }}
          </div>
        </div>

        <!-- Submit Actions -->
        <div class="flex justify-end space-x-4 pt-6 border-t">
          <Button type="button" variant="outline" @click="resetForm">
            Reset Form
          </Button>
          <Button 
            type="submit" 
            :disabled="!isBalanced || isSubmitting || form.entries.length < 2"
            class="min-w-[120px]"
          >
            <Loader2 v-if="isSubmitting" class="h-4 w-4 mr-2 animate-spin" />
            Create Transaction
          </Button>
        </div>
      </form>
    </div>

    <!-- Quick Templates Modal -->
    <Dialog v-model:open="showQuickTemplates">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Quick Transaction Templates</DialogTitle>
          <DialogDescription>
            Select a template to quickly populate your transaction entries
          </DialogDescription>
        </DialogHeader>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div 
            v-for="template in transactionTemplates" 
            :key="template.name"
            @click="applyTemplate(template)"
            class="p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
          >
            <h4 class="font-medium text-gray-900">{{ template.name }}</h4>
            <p class="text-sm text-gray-600 mt-1">{{ template.description }}</p>
            <div class="mt-2 text-xs text-gray-500">
              <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded mr-1">
                {{ template.category }}
              </span>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { 
  Select, 
  SelectContent, 
  SelectItem, 
  SelectTrigger, 
  SelectValue,
  SelectGroup,
  SelectLabel 
} from '@/components/ui/select'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { 
  Plus, 
  Trash2, 
  CheckCircle, 
  AlertCircle, 
  FileText, 
  Loader2 
} from 'lucide-vue-next'
import { useToast } from '@/components/ui/toast/use-toast'

const { toast } = useToast()

// Reactive data
const today = new Date().toISOString().split('T')[0]
const accounts = ref([])
const isSubmitting = ref(false)
const showQuickTemplates = ref(false)
const errors = ref({})

const form = reactive({
  transaction_date: today,
  transaction_number: '',
  description: '',
  notes: '',
  entries: [
    { account_id: '', description: '', debit_amount: '', credit_amount: '', entry_type: 'debit' },
    { account_id: '', description: '', debit_amount: '', credit_amount: '', entry_type: 'credit' }
  ]
})

// Transaction templates
const transactionTemplates = [
  {
    name: 'Cash Receipt',
    description: 'Record cash received from customer',
    category: 'Income',
    entries: [
      { account_type: 'asset', account_name: 'Cash', entry_type: 'debit' },
      { account_type: 'revenue', account_name: 'Sales Revenue', entry_type: 'credit' }
    ]
  },
  {
    name: 'Cash Payment',
    description: 'Record cash payment for expenses',
    category: 'Expense',
    entries: [
      { account_type: 'expense', account_name: 'Office Expense', entry_type: 'debit' },
      { account_type: 'asset', account_name: 'Cash', entry_type: 'credit' }
    ]
  },
  {
    name: 'Bank Deposit',
    description: 'Transfer cash to bank account',
    category: 'Transfer',
    entries: [
      { account_type: 'asset', account_name: 'Bank Account', entry_type: 'debit' },
      { account_type: 'asset', account_name: 'Cash', entry_type: 'credit' }
    ]
  },
  {
    name: 'Purchase on Credit',
    description: 'Record purchase with payment due later',
    category: 'Purchase',
    entries: [
      { account_type: 'expense', account_name: 'Purchase', entry_type: 'debit' },
      { account_type: 'liability', account_name: 'Accounts Payable', entry_type: 'credit' }
    ]
  }
]

// Computed properties
const groupedAccounts = computed(() => {
  return accounts.value.reduce((groups, account) => {
    if (!groups[account.type]) {
      groups[account.type] = []
    }
    groups[account.type].push(account)
    return groups
  }, {})
})

const totalDebits = computed(() => {
  return form.entries.reduce((sum, entry) => {
    return sum + (parseFloat(entry.debit_amount) || 0)
  }, 0)
})

const totalCredits = computed(() => {
  return form.entries.reduce((sum, entry) => {
    return sum + (parseFloat(entry.credit_amount) || 0)
  }, 0)
})

const totalAmount = computed(() => {
  return Math.max(totalDebits.value, totalCredits.value)
})

const isBalanced = computed(() => {
  return Math.abs(totalDebits.value - totalCredits.value) < 0.01 && totalAmount.value > 0
})

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-BD', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0)
}

const formatAccountType = (type) => {
  const types = {
    'asset': 'Assets',
    'liability': 'Liabilities', 
    'equity': 'Equity',
    'revenue': 'Revenue',
    'expense': 'Expenses'
  }
  return types[type] || type.toUpperCase()
}

const addEntry = () => {
  form.entries.push({
    account_id: '',
    description: '',
    debit_amount: '',
    credit_amount: '',
    entry_type: 'debit'
  })
}

const removeEntry = (index) => {
  if (form.entries.length > 2) {
    form.entries.splice(index, 1)
  }
}

const updateEntryType = (index, type) => {
  const entry = form.entries[index]
  if (type === 'debit') {
    entry.credit_amount = ''
    entry.entry_type = 'debit'
  } else {
    entry.debit_amount = ''
    entry.entry_type = 'credit'
  }
}

const applyTemplate = (template) => {
  // Reset entries
  form.entries = []
  
  // Apply template entries
  template.entries.forEach(templateEntry => {
    const matchingAccount = accounts.value.find(account => 
      account.type === templateEntry.account_type &&
      account.name.toLowerCase().includes(templateEntry.account_name.toLowerCase())
    )
    
    const entry = {
      account_id: matchingAccount?.id?.toString() || '',
      description: form.description || template.description,
      debit_amount: templateEntry.entry_type === 'debit' ? '0.00' : '',
      credit_amount: templateEntry.entry_type === 'credit' ? '0.00' : '',
      entry_type: templateEntry.entry_type
    }
    
    form.entries.push(entry)
  })
  
  showQuickTemplates.value = false
  
  toast({
    title: 'Template Applied',
    description: `${template.name} template has been applied. Please fill in the amounts.`
  })
}

const resetForm = () => {
  Object.assign(form, {
    transaction_date: today,
    transaction_number: '',
    description: '',
    notes: '',
    entries: [
      { account_id: '', description: '', debit_amount: '', credit_amount: '', entry_type: 'debit' },
      { account_id: '', description: '', debit_amount: '', credit_amount: '', entry_type: 'credit' }
    ]
  })
  errors.value = {}
}

const submitTransaction = async () => {
  if (!isBalanced.value) {
    toast({
      title: 'Transaction Not Balanced',
      description: 'Please ensure total debits equal total credits',
      variant: 'destructive'
    })
    return
  }

  isSubmitting.value = true
  errors.value = {}

  try {
    // Prepare entries for API
    const entries = form.entries
      .filter(entry => entry.account_id && (entry.debit_amount || entry.credit_amount))
      .map(entry => ({
        account_id: parseInt(entry.account_id),
        entry_type: entry.debit_amount ? 'debit' : 'credit',
        amount: parseFloat(entry.debit_amount || entry.credit_amount),
        description: entry.description || form.description,
        notes: entry.notes || null
      }))

    const response = await fetch('/api/transactions', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        transaction_date: form.transaction_date,
        description: form.description,
        notes: form.notes,
        entries: entries
      })
    })

    const data = await response.json()

    if (response.ok) {
      toast({
        title: 'Success',
        description: 'Transaction created successfully'
      })
      
      resetForm()
    } else {
      if (data.errors) {
        errors.value = data.errors
      }
      
      toast({
        title: 'Error',
        description: data.message || 'Failed to create transaction',
        variant: 'destructive'
      })
    }
  } catch (error) {
    toast({
      title: 'Error',
      description: 'Network error occurred',
      variant: 'destructive'
    })
  } finally {
    isSubmitting.value = false
  }
}

const fetchAccounts = async () => {
  try {
    const response = await fetch('/api/accounts-active', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      accounts.value = await response.json()
    }
  } catch (error) {
    console.error('Failed to fetch accounts:', error)
  }
}

// Lifecycle
onMounted(() => {
  fetchAccounts()
})
</script>
