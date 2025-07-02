<template>
  <CICITLayout>
    <Head title="Verificar Certificado - CICIT" />

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <h1 class="text-3xl font-bold mb-6 text-center">Verificar Certificado</h1>

            <div class="max-w-md mx-auto">
              <p class="text-gray-600 mb-6 text-center">
                Ingrese el código de verificación de su certificado para validar su autenticidad.
              </p>

              <form @submit.prevent="verificarCertificado" class="space-y-4">
                <div>
                  <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">
                    Código de Verificación
                  </label>
                  <input
                    v-model="form.codigo"
                    type="text"
                    id="codigo"
                    placeholder="Ej: CERT-2025-001234"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required
                  >
                </div>

                <button
                  type="submit"
                  :disabled="loading"
                  class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                >
                  <span v-if="loading">Verificando...</span>
                  <span v-else>Verificar Certificado</span>
                </button>
              </form>

              <div v-if="resultado" class="mt-6 p-4 rounded-md" :class="resultado.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg v-if="resultado.success" class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm" :class="resultado.success ? 'text-green-800' : 'text-red-800'">
                      {{ resultado.message }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="mt-8 text-center">
                <p class="text-sm text-gray-500">
                  ¿Problemas con la verificación?
                  <Link :href="route('contacto')" class="text-blue-600 hover:text-blue-700">
                    Contáctanos
                  </Link>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CICITLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import CICITLayout from '@/layouts/CICITLayout.vue'

interface VerificationResult {
  success: boolean
  message: string
}

const form = ref({
  codigo: ''
})

const loading = ref(false)
const resultado = ref<VerificationResult | null>(null)

const verificarCertificado = async () => {
  loading.value = true
  resultado.value = null

  try {
    const response = await fetch('/certificados/verificar', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(form.value)
    })

    const data = await response.json()
    resultado.value = data as VerificationResult
  } catch {
    resultado.value = {
      success: false,
      message: 'Error al verificar el certificado. Intente nuevamente.'
    }
  } finally {
    loading.value = false
  }
}
</script>
