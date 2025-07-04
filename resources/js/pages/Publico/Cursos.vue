<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <Head title="Cursos Disponibles - CICIT" />

    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Cursos Disponibles</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-300">Explora nuestra oferta completa de cursos de certificación</p>
          </div>
          <Link 
            :href="route('home')" 
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Volver al Inicio
          </Link>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Cursos disponibles -->
      <div v-if="cursosDisponibles && cursosDisponibles.length > 0">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="curso in cursosDisponibles"
            :key="curso.id"
            class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-shadow duration-300"
          >
            <!-- Imagen del curso -->
            <div class="h-48 bg-gradient-to-br from-blue-400 to-purple-500 relative">
              <img
                v-if="curso.logo_url"
                :src="curso.logo_url"
                :alt="curso.nombre"
                class="w-full h-full object-cover"
              />
              <div class="absolute inset-0 bg-black bg-opacity-20"></div>
              <div class="absolute top-4 right-4 bg-white dark:bg-gray-800 px-3 py-1 rounded-full text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ curso.nivel }}
              </div>
            </div>

            <!-- Contenido del curso -->
            <div class="p-6">
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                {{ curso.nombre }}
              </h3>
              <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-3">
                {{ curso.descripcion }}
              </p>

              <!-- Información del curso -->
              <div class="space-y-2 mb-4">
                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  Instructor: {{ curso.tutor }}
                </div>
                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  {{ curso.duracion_horas }} horas
                </div>
                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"></path>
                  </svg>
                  Inicio: {{ curso.fecha_inicio }}
                </div>
                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  {{ curso.cupos_disponibles }} cupos disponibles
                </div>
              </div>

              <!-- Botón de preinscripción -->
              <div class="flex justify-between items-center">
                <Link
                  :href="`/preinscripcion/${curso.id}`"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white text-sm font-medium rounded-md transition-colors"
                >
                  Preinscribirse
                </Link>
                <span class="text-xs text-gray-400 dark:text-gray-500">
                  Aula: {{ curso.aula }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mensaje cuando no hay cursos -->
      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay cursos disponibles</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Actualmente no hay cursos disponibles para preinscripción. Vuelve pronto para ver nuevas ofertas.
        </p>
        <div class="mt-6">
          <Link
            :href="route('contacto')"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors"
          >
            Contáctanos para más información
          </Link>
        </div>
      </div>

      <!-- Call to action -->
      <div class="mt-16 bg-blue-600 dark:bg-blue-700 rounded-lg px-6 py-12 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">
          ¿No encuentras lo que buscas?
        </h2>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
          Contáctanos para conocer sobre próximos cursos o solicitar capacitación personalizada para tu organización.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Link
            :href="route('contacto')"
            class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50 dark:bg-gray-100 dark:hover:bg-gray-200 transition-colors"
          >
            Contáctanos
          </Link>
          <Link
            :href="route('home')"
            class="inline-flex items-center px-8 py-3 border-2 border-white text-base font-medium rounded-md text-white hover:bg-white hover:text-blue-600 transition-colors"
          >
            Volver al Inicio
          </Link>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center">
          <p class="text-gray-600 dark:text-gray-400">
            Centro Integral de Certificación e Innovación Tecnológica - Universidad Autónoma Gabriel René Moreno
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

// Definir el tipo para los cursos
interface Curso {
  id: number;
  nombre: string;
  descripcion: string;
  duracion_horas: number;
  nivel: string;
  logo_url?: string;
  tutor: string;
  fecha_inicio: string;
  fecha_fin: string;
  cupos_disponibles: number;
  aula: string;
}

// Props
defineProps<{
  cursosDisponibles: Curso[];
}>();
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
