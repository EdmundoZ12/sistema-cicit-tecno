<template>
  <CICITLayout>
    <Head title="Contacto - CICIT" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <h1 class="text-3xl font-bold mb-6">Contacto</h1>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <div>
                <h2 class="text-2xl font-semibold mb-4">Información de Contacto</h2>

                <div class="space-y-4">
                  <div>
                    <h3 class="font-semibold text-gray-800">Dirección</h3>
                    <p class="text-gray-600">
                      Universidad Autónoma Gabriel René Moreno<br>
                      Santa Cruz de la Sierra, Bolivia
                    </p>
                  </div>

                  <div>
                    <h3 class="font-semibold text-gray-800">Teléfono</h3>
                    <p class="text-gray-600">+591 3 336-4000</p>
                  </div>

                  <div>
                    <h3 class="font-semibold text-gray-800">Email</h3>
                    <p class="text-gray-600">cicit@uagrm.edu.bo</p>
                  </div>

                  <div>
                    <h3 class="font-semibold text-gray-800">Horarios de Atención</h3>
                    <p class="text-gray-600">
                      Lunes a Viernes: 8:00 - 12:00 y 14:00 - 18:00<br>
                      Sábados: 8:00 - 12:00
                    </p>
                  </div>
                </div>
              </div>

              <div>
                <h2 class="text-2xl font-semibold mb-4">Envíanos un Mensaje</h2>

                <form class="space-y-4">
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input
                      type="text"
                      id="name"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                  </div>

                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input
                      type="email"
                      id="email"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                  </div>

                  <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">Asunto</label>
                    <input
                      type="text"
                      id="subject"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                  </div>

                  <div>
                    <label for="message" class="block text-sm font-medium text-gray-700">Mensaje</label>
                    <textarea
                      id="message"
                      rows="4"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    ></textarea>
                  </div>

                  <button
                    type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                  >
                    Enviar Mensaje
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CICITLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import CICITLayout from '@/layouts/CICITLayout.vue'
</script>
