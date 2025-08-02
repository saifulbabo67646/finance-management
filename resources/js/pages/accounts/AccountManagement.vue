<template>
  <div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Account Management</h1>
        <p class="text-gray-600 mt-1">Manage your chart of accounts</p>
      </div>
      <Button @click="showCreateModal = true" class="flex items-center">
        <Plus class="h-4 w-4 mr-2" />
        Create Account
      </Button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <Label>Account Type</Label>
          <Select v-model="filters.type">
            <SelectTrigger>
              <SelectValue placeholder="All types" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All types</SelectItem>
              <SelectItem value="asset">Assets</SelectItem>
              <SelectItem value="liability">Liabilities</SelectItem>
              <SelectItem value="equity">Equity</SelectItem>
              <SelectItem value="revenue">Revenue</SelectItem>
              <SelectItem value="expense">Expenses</SelectItem>
            </SelectContent>
          </Select>
        </div>
        
        <div>
          <Label>Status</Label>
          <Select v-model="filters.active">
            <SelectTrigger>
              <SelectValue placeholder="All accounts" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All accounts</SelectItem>
              <SelectItem value="true">Active only</SelectItem>
              <SelectItem value="false">Inactive only</SelectItem>
            </SelectContent>
          </Select>
        </div>
        
        <div>
          <Label>Search</Label>
          <Input
            v-model="filters.search"
            placeholder="Search by name or code"
            class="w-full"
          />
        </div>
        
        <div class="flex items-end">
          <Button @click="fetchAccounts" variant="outline" class="w-full">
            <Search class="h-4 w-4 mr-2" />
            Filter
          </Button>
        </div>
      </div>
    </div>

    <!-- Accounts Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Code</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Name</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Type</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Category</th>
              <th class="px-6 py-3 text-right text-sm font-medium text-gray-900">Current Balance</th>
              <th class="px-6 py-3 text-center text-sm font-medium text-gray-900">Status</th>
              <th class="px-6 py-3 text-center text-sm font-medium text-gray-900">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="account in accounts.data" :key="account.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-mono text-gray-900">{{ account.code }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ account.name }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="getTypeColor(account.type)">
                  {{ formatAccountType(account.type) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ formatCategory(account.category) }}</td>
              <td class="px-6 py-4 text-sm text-right font-mono"
                  :class="account.current_balance >= 0 ? 'text-green-600' : 'text-red-600'">
                ৳{{ formatCurrency(account.current_balance) }}
              </td>
              <td class="px-6 py-4 text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                  {{ account.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <div class="flex justify-center space-x-2">
                  <Button @click="editAccount(account)" variant="ghost" size="sm">
                    <Edit class="h-4 w-4" />
                  </Button>
                  <Button @click="viewBalanceHistory(account)" variant="ghost" size="sm">
                    <History class="h-4 w-4" />
                  </Button>
                  <Button @click="deleteAccount(account)" variant="ghost" size="sm" class="text-red-600">
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="accounts.last_page > 1" class="px-6 py-4 border-t bg-gray-50">
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-600">
            Showing {{ accounts.from }} to {{ accounts.to }} of {{ accounts.total }} accounts
          </div>
          <div class="flex space-x-2">
            <Button 
              @click="changePage(accounts.current_page - 1)"
              :disabled="accounts.current_page <= 1"
              variant="outline" 
              size="sm"
            >
              Previous
            </Button>
            <Button 
              @click="changePage(accounts.current_page + 1)"
              :disabled="accounts.current_page >= accounts.last_page"
              variant="outline" 
              size="sm"
            >
              Next
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Account Modal -->
    <Dialog v-model:open="showCreateModal">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>{{ editingAccount ? 'Edit Account' : 'Create New Account' }}</DialogTitle>
        </DialogHeader>
        
        <form @submit.prevent="saveAccount" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label for="code">Account Code</Label>
              <Input
                id="code"
                v-model="accountForm.code"
                placeholder="e.g., 1001"
                required
              />
              <div v-if="formErrors.code" class="text-red-500 text-sm mt-1">
                {{ formErrors.code[0] }}
              </div>
            </div>
            
            <div>
              <Label for="name">Account Name</Label>
              <Input
                id="name"
                v-model="accountForm.name"
                placeholder="e.g., Cash in Hand"
                required
              />
              <div v-if="formErrors.name" class="text-red-500 text-sm mt-1">
                {{ formErrors.name[0] }}
              </div>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label for="type">Account Type</Label>
              <Select v-model="accountForm.type" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="asset">Asset</SelectItem>
                  <SelectItem value="liability">Liability</SelectItem>
                  <SelectItem value="equity">Equity</SelectItem>
                  <SelectItem value="revenue">Revenue</SelectItem>
                  <SelectItem value="expense">Expense</SelectItem>
                </SelectContent>
              </Select>
              <div v-if="formErrors.type" class="text-red-500 text-sm mt-1">
                {{ formErrors.type[0] }}
              </div>
            </div>
            
            <div>
              <Label for="category">Category</Label>
              <Select v-model="accountForm.category" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select category" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup v-if="accountForm.type === 'asset'">
                    <SelectLabel>Asset Categories</SelectLabel>
                    <SelectItem value="current_asset">Current Asset</SelectItem>
                    <SelectItem value="fixed_asset">Fixed Asset</SelectItem>
                  </SelectGroup>
                  <SelectGroup v-if="accountForm.type === 'liability'">
                    <SelectLabel>Liability Categories</SelectLabel>
                    <SelectItem value="current_liability">Current Liability</SelectItem>
                    <SelectItem value="long_term_liability">Long-term Liability</SelectItem>
                  </SelectGroup>
                  <SelectGroup v-if="accountForm.type === 'equity'">
                    <SelectLabel>Equity Categories</SelectLabel>
                    <SelectItem value="owner_equity">Owner's Equity</SelectItem>
                  </SelectGroup>
                  <SelectGroup v-if="accountForm.type === 'revenue'">
                    <SelectLabel>Revenue Categories</SelectLabel>
                    <SelectItem value="operating_revenue">Operating Revenue</SelectItem>
                    <SelectItem value="other_revenue">Other Revenue</SelectItem>
                  </SelectGroup>
                  <SelectGroup v-if="accountForm.type === 'expense'">
                    <SelectLabel>Expense Categories</SelectLabel>
                    <SelectItem value="operating_expense">Operating Expense</SelectItem>
                    <SelectItem value="other_expense">Other Expense</SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
              <div v-if="formErrors.category" class="text-red-500 text-sm mt-1">
                {{ formErrors.category[0] }}
              </div>
            </div>
          </div>
          
          <div>
            <Label for="description">Description (Optional)</Label>
            <Textarea
              id="description"
              v-model="accountForm.description"
              placeholder="Account description"
              rows="2"
            />
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label for="opening_balance">Opening Balance</Label>
              <Input
                id="opening_balance"
                v-model="accountForm.opening_balance"
                type="number"
                step="0.01"
                placeholder="0.00"
              />
            </div>
            
            <div class="flex items-center space-x-2 pt-6">
              <input
                id="is_active"
                v-model="accountForm.is_active"
                type="checkbox"
                class="rounded border-gray-300"
              />
              <Label for="is_active">Active Account</Label>
            </div>
          </div>
          
          <div class="flex justify-end space-x-4 pt-4">
            <Button type="button" variant="outline" @click="closeModal">
              Cancel
            </Button>
            <Button type="submit" :disabled="isSubmitting">
              <Loader2 v-if="isSubmitting" class="h-4 w-4 mr-2 animate-spin" />
              {{ editingAccount ? 'Update' : 'Create' }} Account
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue'
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
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { 
  Plus, 
  Search, 
  Edit, 
  Trash2, 
  History, 
  Loader2 
} from 'lucide-vue-next'
import { useToast } from '@/components/ui/toast/use-toast'

const { toast } = useToast()

// Reactive data
const accounts = ref({ data: [], current_page: 1, last_page: 1, from: 0, to: 0, total: 0 })
const showCreateModal = ref(false)
const editingAccount = ref(null)
const isSubmitting = ref(false)
const formErrors = ref({})

const filters = reactive({
  type: '',
  active: '',
  search: ''
})

const accountForm = reactive({
  code: '',
  name: '',
  type: '',
  category: '',
  description: '',
  opening_balance: '',
  is_active: true
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
    'asset': 'Asset',
    'liability': 'Liability', 
    'equity': 'Equity',
    'revenue': 'Revenue',
    'expense': 'Expense'
  }
  return types[type] || type.toUpperCase()
}

const formatCategory = (category) => {
  return category.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ')
}

const getTypeColor = (type) => {
  const colors = {
    'asset': 'bg-blue-100 text-blue-800',
    'liability': 'bg-red-100 text-red-800',
    'equity': 'bg-purple-100 text-purple-800',
    'revenue': 'bg-green-100 text-green-800',
    'expense': 'bg-orange-100 text-orange-800'
  }
  return colors[type] || 'bg-gray-100 text-gray-800'
}

const fetchAccounts = async (page = 1) => {
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: '15'
    })
    
    if (filters.type) params.append('type', filters.type)
    if (filters.active) params.append('active', filters.active)
    if (filters.search) params.append('search', filters.search)

    const response = await fetch(`/api/accounts?${params}`, {
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

const changePage = (page) => {
  fetchAccounts(page)
}

const editAccount = (account) => {
  editingAccount.value = account
  Object.assign(accountForm, {
    code: account.code,
    name: account.name,
    type: account.type,
    category: account.category,
    description: account.description || '',
    opening_balance: account.opening_balance,
    is_active: account.is_active
  })
  showCreateModal.value = true
}

const closeModal = () => {
  showCreateModal.value = false
  editingAccount.value = null
  Object.assign(accountForm, {
    code: '',
    name: '',
    type: '',
    category: '',
    description: '',
    opening_balance: '',
    is_active: true
  })
  formErrors.value = {}
}

const saveAccount = async () => {
  isSubmitting.value = true
  formErrors.value = {}

  try {
    const url = editingAccount.value 
      ? `/api/accounts/${editingAccount.value.id}`
      : '/api/accounts'
    
    const method = editingAccount.value ? 'PUT' : 'POST'

    const response = await fetch(url, {
      method,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(accountForm)
    })

    const data = await response.json()

    if (response.ok) {
      toast({
        title: 'Success',
        description: `Account ${editingAccount.value ? 'updated' : 'created'} successfully`
      })
      
      closeModal()
      fetchAccounts()
    } else {
      if (data.errors) {
        formErrors.value = data.errors
      }
      
      toast({
        title: 'Error',
        description: data.message || 'Failed to save account',
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

const deleteAccount = async (account) => {
  if (!confirm(`Are you sure you want to delete account "${account.name}"?`)) {
    return
  }

  try {
    const response = await fetch(`/api/accounts/${account.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    })

    const data = await response.json()

    if (response.ok) {
      toast({
        title: 'Success',
        description: 'Account deleted successfully'
      })
      
      fetchAccounts()
    } else {
      toast({
        title: 'Error',
        description: data.message || 'Failed to delete account',
        variant: 'destructive'
      })
    }
  } catch (error) {
    toast({
      title: 'Error',
      description: 'Network error occurred',
      variant: 'destructive'
    })
  }
}

const viewBalanceHistory = (account) => {
  // TODO: Implement balance history modal
  toast({
    title: 'Coming Soon',
    description: 'Balance history feature will be implemented soon'
  })
}

// Watchers
watch(() => accountForm.type, () => {
  accountForm.category = ''
})

// Lifecycle
onMounted(() => {
  fetchAccounts()
})
</script>
