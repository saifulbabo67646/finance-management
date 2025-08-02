<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
      <Button @click="showCreateModal = true" class="bg-blue-600 hover:bg-blue-700">
        <Plus class="w-4 h-4 mr-2" />
        Add User
      </Button>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                User
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Role
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Created
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in users.data" :key="user.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                      <User class="h-5 w-5 text-gray-600" />
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="getRoleBadgeClass(user.role?.name)">
                  {{ user.role?.name?.replace('_', ' ').toUpperCase() }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                  {{ user.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(user.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <Button variant="ghost" size="sm" @click="editUser(user)" class="mr-2">
                  <Edit class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="sm" @click="deleteUser(user)" 
                        :disabled="user.role?.name === 'super_admin'"
                        class="text-red-600 hover:text-red-900">
                  <Trash2 class="w-4 h-4" />
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex justify-between items-center">
      <div class="text-sm text-gray-700">
        Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
      </div>
      <div class="flex space-x-2">
        <Button v-for="link in users.links" :key="link.label" 
                @click="changePage(link.url)"
                :disabled="!link.url"
                :variant="link.active ? 'default' : 'outline'"
                size="sm"
                v-html="link.label">
        </Button>
      </div>
    </div>

    <!-- Create/Edit User Modal -->
    <Dialog :open="showCreateModal || showEditModal" @update:open="closeModal">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>{{ showEditModal ? 'Edit User' : 'Create New User' }}</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <Label for="name">Full Name</Label>
            <Input id="name" v-model="form.name" required />
          </div>
          <div>
            <Label for="email">Email</Label>
            <Input id="email" type="email" v-model="form.email" required />
          </div>
          <div v-if="!showEditModal">
            <Label for="password">Password</Label>
            <Input id="password" type="password" v-model="form.password" required />
          </div>
          <div v-if="showEditModal">
            <Label for="new_password">New Password (leave blank to keep current)</Label>
            <Input id="new_password" type="password" v-model="form.password" />
          </div>
          <div>
            <Label for="role">Role</Label>
            <Select v-model="form.role_id">
              <SelectTrigger>
                <SelectValue placeholder="Select a role" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="role in roles" :key="role.id" :value="role.id.toString()">
                  {{ role.name.replace('_', ' ').toUpperCase() }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex items-center space-x-2">
            <Checkbox id="is_active" v-model:checked="form.is_active" />
            <Label for="is_active">Active</Label>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
            <Button type="submit" :disabled="loading">
              {{ loading ? 'Saving...' : (showEditModal ? 'Update' : 'Create') }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Checkbox } from '@/components/ui/checkbox'
import { Plus, User, Edit, Trash2 } from 'lucide-vue-next'
import { useToast } from '@/components/ui/toast/use-toast'

const { toast } = useToast()

// Reactive data
const users = ref({ data: [], links: [], from: 0, to: 0, total: 0 })
const roles = ref([])
const showCreateModal = ref(false)
const showEditModal = ref(false)
const loading = ref(false)
const currentUser = ref(null)

const form = reactive({
  name: '',
  email: '',
  password: '',
  role_id: '',
  is_active: true
})

// Methods
const fetchUsers = async (url = '/api/users') => {
  try {
    const response = await fetch(url, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    })
    if (response.ok) {
      users.value = await response.json()
    }
  } catch (error) {
    toast({
      title: 'Error',
      description: 'Failed to fetch users',
      variant: 'destructive'
    })
  }
}

const fetchRoles = async () => {
  try {
    const response = await fetch('/api/roles', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    })
    if (response.ok) {
      roles.value = await response.json()
    }
  } catch (error) {
    toast({
      title: 'Error',
      description: 'Failed to fetch roles',
      variant: 'destructive'
    })
  }
}

const submitForm = async () => {
  loading.value = true
  try {
    const url = showEditModal.value ? `/api/users/${currentUser.value.id}` : '/api/users'
    const method = showEditModal.value ? 'PUT' : 'POST'
    
    const response = await fetch(url, {
      method,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(form)
    })

    if (response.ok) {
      toast({
        title: 'Success',
        description: `User ${showEditModal.value ? 'updated' : 'created'} successfully`
      })
      closeModal()
      fetchUsers()
    } else {
      const error = await response.json()
      toast({
        title: 'Error',
        description: error.message || 'Failed to save user',
        variant: 'destructive'
      })
    }
  } catch (error) {
    toast({
      title: 'Error',
      description: 'Failed to save user',
      variant: 'destructive'
    })
  } finally {
    loading.value = false
  }
}

const editUser = (user) => {
  currentUser.value = user
  form.name = user.name
  form.email = user.email
  form.password = ''
  form.role_id = user.role_id?.toString() || ''
  form.is_active = user.is_active
  showEditModal.value = true
}

const deleteUser = async (user) => {
  if (!confirm(`Are you sure you want to delete ${user.name}?`)) return
  
  try {
    const response = await fetch(`/api/users/${user.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    })

    if (response.ok) {
      toast({
        title: 'Success',
        description: 'User deleted successfully'
      })
      fetchUsers()
    }
  } catch (error) {
    toast({
      title: 'Error',
      description: 'Failed to delete user',
      variant: 'destructive'
    })
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  currentUser.value = null
  Object.assign(form, {
    name: '',
    email: '',
    password: '',
    role_id: '',
    is_active: true
  })
}

const changePage = (url) => {
  if (url) fetchUsers(url)
}

const getRoleBadgeClass = (roleName) => {
  const classes = {
    'super_admin': 'bg-purple-100 text-purple-800',
    'company_admin': 'bg-blue-100 text-blue-800',
    'branch_manager': 'bg-green-100 text-green-800',
    'accountant': 'bg-yellow-100 text-yellow-800',
    'viewer': 'bg-gray-100 text-gray-800'
  }
  return classes[roleName] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

// Lifecycle
onMounted(() => {
  fetchUsers()
  fetchRoles()
})
</script>
