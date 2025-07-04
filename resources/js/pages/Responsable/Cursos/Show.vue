<template>
  <ResponsableLayout>
    <Head :title="`Curso: ${curso.nombre}`" />

    <div class="py-6">
      <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ curso.nombre }}</h1>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Detalles completos del curso
              </p>
            </div>
            <div class="flex space-x-3">
              <Link
                :href="route('responsable.cursos.edit', curso.id)"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
              >
                <Edit class="w-4 h-4 mr-2" />
                Editar
              </Link>
              <Link
                :href="route('dashboard')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                @click="goBackToCursos"
              >
                <ArrowLeft class="w-4 h-4 mr-2" />
                Volver
              </Link>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Información Principal -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Información Básica -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white">Información Básica</h3>
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      curso.activo
                        ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                        : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'
                    ]"
                  >
                    {{ curso.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Descripción</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ curso.descripcion || 'Sin descripción' }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Duración</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ curso.duracion_horas }} horas</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nivel</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ curso.nivel || 'No especificado' }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Aula</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ curso.aula || 'No especificada' }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Tutor</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ curso.tutor?.nombre }} {{ curso.tutor?.apellido }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Gestión</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ curso.gestion?.nombre }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Inicio</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(curso.fecha_inicio) }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Fin</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(curso.fecha_fin) }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Precios -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Precios por Tipo de Participante</h3>
                <div class="overflow-hidden">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                          Código
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                          Tipo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                          Precio
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="precio in curso.precios" :key="precio.tipo_participante_id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                          {{ precio.tipo_participante?.codigo }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                          {{ precio.tipo_participante?.descripcion }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                          {{ formatCurrency(precio.precio) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Inscripciones -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Inscripciones</h3>
                <div v-if="curso.inscripciones && curso.inscripciones.length > 0" class="overflow-hidden">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                          Participante
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                          Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                          Fecha de Inscripción
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="inscripcion in curso.inscripciones" :key="inscripcion.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                          {{ inscripcion.participante?.nombre }} {{ inscripcion.participante?.apellido }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span
                            :class="[
                              'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                              getEstadoClasses(inscripcion.estado)
                            ]"
                          >
                            {{ inscripcion.estado }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                          {{ formatDate(inscripcion.created_at) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div v-else class="text-center py-4">
                  <p class="text-sm text-gray-500 dark:text-gray-400">No hay inscripciones registradas</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Logo -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Logo del Curso</h3>
                <div class="text-center">
                  <img
                    :src="curso.logo_url || '/images/default-course.svg'"
                    :alt="curso.nombre"
                    class="w-full h-40 object-cover rounded-lg"
                    @error="(e) => (e.target as HTMLImageElement).src = '/images/default-course.svg'"
                  />
                </div>
              </div>
            </div>

            <!-- Estadísticas -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Estadísticas</h3>
                <div class="space-y-4">
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Cupos Totales</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ curso.cupos_totales }}</span>
                  </div>

                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Cupos Ocupados</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ curso.cupos_ocupados || 0 }}</span>
                  </div>

                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Cupos Disponibles</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ estadisticas.cupos_disponibles }}</span>
                  </div>

                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">% Ocupación</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ estadisticas.porcentaje_ocupacion }}%</span>
                  </div>

                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Preinscripciones</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ estadisticas.total_preinscripciones }}</span>
                  </div>

                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Pendientes</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ estadisticas.preinscripciones_pendientes }}</span>
                  </div>

                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Tareas</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ estadisticas.total_tareas }}</span>
                  </div>

                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Promedio Asistencia</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ estadisticas.promedio_asistencia }}%</span>
                  </div>

                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Ingresos Generados</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(estadisticas.ingresos_generados) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Progress Bar -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Ocupación de Cupos</h3>
                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                  <div
                    class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                    :style="{ width: `${estadisticas.porcentaje_ocupacion}%` }"
                  ></div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                  {{ curso.cupos_ocupados || 0 }} de {{ curso.cupos_totales }} cupos ocupados
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </ResponsableLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import ResponsableLayout from '@/layouts/CICITLayout.vue'
import { ArrowLeft, Edit } from 'lucide-vue-next'

// Types
interface Curso {
  id: number
  nombre: string
  descripcion?: string
  duracion_horas: number
  nivel?: string
  logo_url?: string
  aula?: string
  cupos_totales: number
  cupos_ocupados: number
  fecha_inicio: string
  fecha_fin: string
  activo: boolean
  tutor?: {
    nombre: string
    apellido: string
  }
  gestion?: {
    nombre: string
  }
  precios: Array<{
    tipo_participante_id: number
    precio: number
    tipo_participante?: {
      codigo: string
      descripcion: string
    }
  }>
  inscripciones?: Array<{
    id: number
    estado: string
    created_at: string
    participante?: {
      nombre: string
      apellido: string
    }
  }>
}

interface Estadisticas {
  cupos_disponibles: number
  porcentaje_ocupacion: number
  total_preinscripciones: number
  preinscripciones_pendientes: number
  inscripciones_activas: number
  total_tareas: number
  promedio_asistencia: number
  ingresos_generados: number
}

// Props
defineProps<{
  curso: Curso
  estadisticas: Estadisticas
}>()

// Methods
const goBackToCursos = (e: Event) => {
  e.preventDefault()
  // Navegar al dashboard y activar la sección de cursos
  window.location.href = '/dashboard'
  // Usar localStorage para indicar que debe activar la sección de cursos
  localStorage.setItem('activeDashboardSection', 'cursos')
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB'
  }).format(amount)
}

const getEstadoClasses = (estado: string) => {
  switch (estado) {
    case 'ACTIVO':
      return 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
    case 'COMPLETADO':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100'
    case 'RETIRADO':
      return 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100'
  }
}
</script>
