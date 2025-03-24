<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow">
      <div class="container mx-auto px-4 py-4">
        <h1 class="text-2xl font-bold text-gray-800">
          <router-link to="/">Comment System</router-link>
        </h1>
      </div>
    </header>
    
    <main class="container mx-auto py-6 px-60">
      <router-view></router-view>
    </main>
    
    <footer class="bg-white shadow-inner mt-8">
      <div class="container mx-auto px-4 py-4 text-center text-gray-600">
        &copy; {{ new Date().getFullYear() }} Comment System
      </div>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'App'
};
</script>
