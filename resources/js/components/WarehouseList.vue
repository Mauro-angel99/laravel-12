<script setup>
import { ref, onMounted } from 'vue'

const loading = ref(false)

// Carica i dati iniziali
onMounted(() => {
  // Pagina vuota per ora
});

</script>

<template>
  <div class="bg-white shadow rounded-lg p-3">
    <div class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">Magazzino</h3>
      <p class="mt-1 text-sm text-gray-500">
        Questa pagina è in costruzione.
      </p>
    </div>
  </div>
</template>
