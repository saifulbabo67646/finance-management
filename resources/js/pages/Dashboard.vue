<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { 
  DollarSign, 
  TrendingUp, 
  TrendingDown, 
  Calendar, 
  Plus, 
  CreditCard, 
  FileText, 
  Users, 
  ArrowRight, 
  ArrowUpDown 
} from 'lucide-vue-next'

const { user } = usePage()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const currentDate = ref('')
const currentTime = ref('')
const financialSummary = ref({
  currentBalance: 0,
  totalIncome: 0,
  totalExpenses: 0,
  monthlyNet: 0
})
const recentTransactions = ref([])

const hasPermission = (permission: string) => {
  return user?.role?.permissions?.some(p => p.name === permission) || 
         user?.role?.name === 'super_admin'
}

const getRoleDisplayName = (roleName: string) => {
  const roleNames = {
    'super_admin': 'Super Administrator',
    'company_admin': 'Company Administrator',
    'branch_manager': 'Branch Manager',
    'accountant': 'Accountant',
    'viewer': 'Viewer'
  }
  return roleNames[roleName] || roleName?.replace('_', ' ').toUpperCase()
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-BD', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-BD')
}

const updateTime = () => {
  const now = new Date()
  currentDate.value = now.toLocaleDateString('en-BD', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
  currentTime.value = now.toLocaleTimeString('en-BD', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const fetchFinancialSummary = async () => {
  try {
    const response = await fetch('/api/dashboard/financial-summary', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    })
    if (response.ok) {
      financialSummary.value = await response.json()
    }
  } catch (error) {
    console.error('Failed to fetch financial summary:', error)
  }
}

const fetchRecentTransactions = async () => {
  try {
    const response = await fetch('/api/dashboard/recent-transactions', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    })
    if (response.ok) {
      recentTransactions.value = await response.json()
    }
  } catch (error) {
    console.error('Failed to fetch recent transactions:', error)
  }
}

// Lifecycle
onMounted(() => {
  updateTime()
  setInterval(updateTime, 60000) // Update every minute
  
  if (hasPermission('view_dashboard')) {
    fetchFinancialSummary()
  }
  
  if (hasPermission('view_transactions')) {
    fetchRecentTransactions()
  }
})
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <!-- Welcome Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">
              Welcome back, {{ user?.name }}!
            </h1>
            <p class="text-gray-600 mt-1">
              {{ getRoleDisplayName(user?.role?.name) }} Dashboard
            </p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-500">{{ currentDate }}</p>
            <p class="text-lg font-semibold text-gray-900">{{ currentTime }}</p>
          </div>
        </div>
      </div>

      <!-- Financial Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" v-if="hasPermission('view_dashboard')">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <DollarSign class="h-8 w-8 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Current Balance</p>
              <p class="text-2xl font-bold text-gray-900">৳{{ formatCurrency(financialSummary.currentBalance) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <TrendingUp class="h-8 w-8 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Income</p>
              <p class="text-2xl font-bold text-gray-900">৳{{ formatCurrency(financialSummary.totalIncome) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <TrendingDown class="h-8 w-8 text-red-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Expenses</p>
              <p class="text-2xl font-bold text-gray-900">৳{{ formatCurrency(financialSummary.totalExpenses) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <Calendar class="h-8 w-8 text-purple-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">This Month</p>
              <p class="text-2xl font-bold text-gray-900">৳{{ formatCurrency(financialSummary.monthlyNet) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Transactions -->
      <div class="bg-white rounded-lg shadow p-6" v-if="hasPermission('view_transactions')">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Recent Transactions</h2>
        </div>
        
        <div class="space-y-3">
          <div v-for="transaction in recentTransactions" :key="transaction.id" 
               class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                <ArrowUpDown class="h-4 w-4 text-blue-600" />
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">{{ transaction.description }}</p>
                <p class="text-xs text-gray-500">{{ formatDate(transaction.date) }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium" 
                 :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                {{ transaction.type === 'income' ? '+' : '-' }}৳{{ formatCurrency(transaction.amount) }}
              </p>
            </div>
          </div>
          
          <div v-if="recentTransactions.length === 0" class="text-center py-8 text-gray-500">
            No recent transactions found
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
