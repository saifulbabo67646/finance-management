<template>
  <div id="app">
    <!-- Show navigation for authenticated routes -->
    <Navigation v-if="showNavigation" />
    
    <!-- Main content -->
    <main :class="showNavigation ? '' : 'min-h-screen'">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Navigation from './components/Navigation.vue'

const route = useRoute()

// Show navigation for all routes except login
const showNavigation = computed(() => {
  return route.name !== 'Login'
})
</script>

<style>
/* Global styles can be added here if needed */
</style>
