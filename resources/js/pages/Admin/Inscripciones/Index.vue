<template>
  <AdminLayout page-title="Gestión de Inscripciones">
    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                  Total Inscripciones
                </dt>
                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ stats.total_inscripciones || 0 }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                  Inscripciones Activas
                </dt>
                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ stats.inscripciones_activas || 0 }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4h6m-4 4h4m-4 4h4"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                  Inscripciones Hoy
                </dt>
                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ stats.inscripciones_hoy || 0 }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Filtros</h3>
        <form @submit.prevent="filtrar" class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar</label>
            <input
              id="search"
              v-model="form.search"
              type="text"
              placeholder="Nombre, apellido, carnet, curso..."
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label for="estado" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
            <select
              id="estado"
              v-model="form.estado"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Todos los estados</option>
              <option value="INSCRITO">Inscrito</option>
              <option value="CANCELADO">Cancelado</option>
              <option value="COMPLETADO">Completado</option>
            </select>
          </div>
          <div class="flex items-end space-x-2">
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
              Filtrar
            </button>
            <button
              type="button"
              @click="limpiarFiltros"
              class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
            >
              Limpiar
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabla de Inscripciones -->
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
      <div v-if="inscripciones.data.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay inscripciones</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No se encontraron inscripciones con los filtros aplicados.</p>
      </div>

      <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
        <li v-for="inscripcion in inscripciones.data" :key="inscripcion.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
          <div class="px-4 py-4 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-medium text-blue-600 dark:text-blue-400 truncate">
                    {{ inscripcion.participante.nombre }} {{ inscripcion.participante.apellido }}
                  </p>
                  <div class="ml-2 flex-shrink-0 flex">
                    <p :class="getEstadoClasses(inscripcion.estado)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                      {{ inscripcion.estado }}
                    </p>
                  </div>
                </div>
                <div class="mt-2">
                  <div class="sm:flex">
                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                      <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                      CI: {{ inscripcion.participante.carnet }}
                    </div>
                    <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400 sm:mt-0 sm:ml-6">
                      <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                      </svg>
                      {{ inscripcion.curso.nombre }}
                    </div>
                    <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400 sm:mt-0 sm:ml-6">
                      <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4h6m-4 4h4m-4 4h4"></path>
                      </svg>
                      {{ formatDate(inscripcion.fecha_inscripcion) }}
                    </div>
                  </div>
                  <div v-if="inscripcion.preinscripcion" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Preinscripción ID: {{ inscripcion.preinscripcion.id }}
                  </div>
                </div>
              </div>
              <div class="ml-4 flex-shrink-0">
                <button class="inline-flex items-center px-3 py-1 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                  Ver detalles
                  <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </li>
      </ul>

      <!-- Paginación -->
      <div v-if="inscripciones.data.length > 0" class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
          <Link
            v-if="inscripciones.prev_page_url"
            :href="inscripciones.prev_page_url"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            Anterior
          </Link>
          <Link
            v-if="inscripciones.next_page_url"
            :href="inscripciones.next_page_url"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            Siguiente
          </Link>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Mostrando
              <span class="font-medium">{{ inscripciones.from }}</span>
              a
              <span class="font-medium">{{ inscripciones.to }}</span>
              de
              <span class="font-medium">{{ inscripciones.total }}</span>
              resultados
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <Link
                v-if="inscripciones.prev_page_url"
                :href="inscripciones.prev_page_url"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              >
                <span class="sr-only">Anterior</span>
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
              </Link>
              <Link
                v-if="inscripciones.next_page_url"
                :href="inscripciones.next_page_url"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              >
                <span class="sr-only">Siguiente</span>
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
              </Link>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

// Interfaces
interface TipoParticipante {
  id: number
  nombre: string
}

interface Participante {
  id: number
  nombre: string
  apellido: string
  carnet: string
  tipo_participante: TipoParticipante
}

interface Tutor {
  id: number
  nombre: string
  apellido: string
}

interface Curso {
  id: number
  nombre: string
  tutor: Tutor
}

interface Preinscripcion {
  id: number
}

interface Inscripcion {
  id: number
  fecha_inscripcion: string
  estado: string
  participante: Participante
  curso: Curso
  preinscripcion?: Preinscripcion
}

interface PaginatedInscripciones {
  data: Inscripcion[]
  current_page: number
  from: number
  to: number
  total: number
  prev_page_url: string | null
  next_page_url: string | null
}

interface Stats {
  total_inscripciones: number
  inscripciones_activas: number
  inscripciones_hoy: number
}

// Props
interface Props {
  inscripciones: PaginatedInscripciones
  stats: Stats
  filters: {
    search?: string
    estado?: string
    curso_id?: string
  }
}

const props = defineProps<Props>()

// Reactive data
const form = reactive({
  search: props.filters.search || '',
  estado: props.filters.estado || '',
  curso_id: props.filters.curso_id || ''
})

// Methods
const filtrar = () => {
  router.get(route('admin.inscripciones.index'), {
    search: form.search || undefined,
    estado: form.estado || undefined,
    curso_id: form.curso_id || undefined
  }, {
    preserveState: true,
    replace: true
  })
}

const limpiarFiltros = () => {
  form.search = ''
  form.estado = ''
  form.curso_id = ''
  router.get(route('admin.inscripciones.index'), {}, {
    preserveState: true,
    replace: true
  })
}

const getEstadoClasses = (estado: string): string => {
  switch (estado) {
    case 'INSCRITO':
      return 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
    case 'CANCELADO':
      return 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'
    case 'COMPLETADO':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100'
  }
}

const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('es-BO', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}
</script>
