<template>
  <nav class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex">
          <!-- Logo -->
          <div class="flex-shrink-0 flex items-center">
            <router-link to="/dashboard" class="text-xl font-bold text-gray-900">
              Finance Manager
            </router-link>
          </div>
          
          <!-- Navigation Links -->
          <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
            <router-link
              to="/dashboard"
              class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors"
              active-class="border-blue-500 text-gray-900"
            >
              <LayoutDashboard class="w-4 h-4 inline mr-2" />
              Dashboard
            </router-link>
            
            <router-link
              v-if="hasPermission('create_transactions')"
              to="/cashbook"
              class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors"
              active-class="border-blue-500 text-gray-900"
            >
              <BookOpen class="w-4 h-4 inline mr-2" />
              Cashbook Entry
            </router-link>
            
            <router-link
              v-if="hasPermission('manage_accounts')"
              to="/accounts"
              class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors"
              active-class="border-blue-500 text-gray-900"
            >
              <Building2 class="w-4 h-4 inline mr-2" />
              Accounts
            </router-link>
            
            <router-link
              v-if="hasPermission('manage_users')"
              to="/users"
              class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors"
              active-class="border-blue-500 text-gray-900"
            >
              <Users class="w-4 h-4 inline mr-2" />
              Users
            </router-link>
          </div>
        </div>
        
        <!-- User Menu -->
        <div class="hidden sm:ml-6 sm:flex sm:items-center">
          <div class="ml-3 relative">
            <div class="flex items-center space-x-4">
              <span class="text-sm text-gray-700">{{ user?.name }}</span>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ user?.role?.name }}
              </span>
              <button
                @click="logout"
                class="text-gray-500 hover:text-gray-700 p-2 rounded-md transition-colors"
                title="Logout"
              >
                <LogOut class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
        
        <!-- Mobile menu button -->
        <div class="sm:hidden flex items-center">
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
          >
            <Menu v-if="!mobileMenuOpen" class="block h-6 w-6" />
            <X v-else class="block h-6 w-6" />
          </button>
        </div>
      </div>
    </div>
    
    <!-- Mobile menu -->
    <div v-if="mobileMenuOpen" class="sm:hidden">
      <div class="pt-2 pb-3 space-y-1">
        <router-link
          to="/dashboard"
          class="text-gray-600 hover:text-gray-900 hover:bg-gray-50 block px-3 py-2 text-base font-medium transition-colors"
          active-class="text-blue-600 bg-blue-50"
          @click="mobileMenuOpen = false"
        >
          <LayoutDashboard class="w-4 h-4 inline mr-2" />
          Dashboard
        </router-link>
        
        <router-link
          v-if="hasPermission('create_transactions')"
          to="/cashbook"
          class="text-gray-600 hover:text-gray-900 hover:bg-gray-50 block px-3 py-2 text-base font-medium transition-colors"
          active-class="text-blue-600 bg-blue-50"
          @click="mobileMenuOpen = false"
        >
          <BookOpen class="w-4 h-4 inline mr-2" />
          Cashbook Entry
        </router-link>
        
        <router-link
          v-if="hasPermission('manage_accounts')"
          to="/accounts"
          class="text-gray-600 hover:text-gray-900 hover:bg-gray-50 block px-3 py-2 text-base font-medium transition-colors"
          active-class="text-blue-600 bg-blue-50"
          @click="mobileMenuOpen = false"
        >
          <Building2 class="w-4 h-4 inline mr-2" />
          Accounts
        </router-link>
        
        <router-link
          v-if="hasPermission('manage_users')"
          to="/users"
          class="text-gray-600 hover:text-gray-900 hover:bg-gray-50 block px-3 py-2 text-base font-medium transition-colors"
          active-class="text-blue-600 bg-blue-50"
          @click="mobileMenuOpen = false"
        >
          <Users class="w-4 h-4 inline mr-2" />
          Users
        </router-link>
      </div>
      
      <!-- Mobile user info -->
      <div class="pt-4 pb-3 border-t border-gray-200">
        <div class="flex items-center px-4">
          <div class="flex-shrink-0">
            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
              <span class="text-sm font-medium text-gray-700">{{ user?.name?.charAt(0) }}</span>
            </div>
          </div>
          <div class="ml-3">
            <div class="text-base font-medium text-gray-800">{{ user?.name }}</div>
            <div class="text-sm font-medium text-gray-500">{{ user?.role?.name }}</div>
          </div>
          <div class="ml-auto">
            <button
              @click="logout"
              class="text-gray-500 hover:text-gray-700 p-2 rounded-md transition-colors"
              title="Logout"
            >
              <LogOut class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  LayoutDashboard, 
  BookOpen, 
  Building2, 
  Users, 
  LogOut, 
  Menu, 
  X 
} from 'lucide-vue-next'

const router = useRouter()
const mobileMenuOpen = ref(false)
const user = ref(null)

// Fetch user data on component mount
onMounted(async () => {
  await fetchUser()
})

const fetchUser = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) return

    const response = await fetch('/api/user', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (response.ok) {
      user.value = await response.json()
    }
  } catch (error) {
    console.error('Failed to fetch user:', error)
  }
}

const hasPermission = (permission) => {
  return user.value?.role?.permissions?.some(p => p.name === permission) || false
}

const logout = async () => {
  try {
    const token = localStorage.getItem('token')
    
    await fetch('/api/logout', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    localStorage.removeItem('token')
    router.push('/login')
  }
}
</script>
