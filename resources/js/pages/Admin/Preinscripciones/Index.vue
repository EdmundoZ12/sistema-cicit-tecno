<template>
  <AdminLayout :pending-count="stats.pendientes" page-title="Gestión de Preinscripciones">
    
    <!-- Filters and Search -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          
          <!-- Search -->
          <div class="md:col-span-2">
            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Buscar preinscripción
            </label>
            <div class="relative">
              <input 
                v-model="filters.search"
                @input="updateFilters"
                type="text" 
                id="search"
                placeholder="ID, nombre, apellido, carnet..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              >
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Estado Filter -->
          <div>
            <label for="estado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Estado
            </label>
            <select 
              v-model="filters.estado"
              @change="updateFilters"
              id="estado"
              class="w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:text-white"
            >
              <option value="">Todos los estados</option>
              <option value="PENDIENTE">Pendiente</option>
              <option value="APROBADA">Aprobada</option>
              <option value="RECHAZADA">Rechazada</option>
            </select>
          </div>

          <!-- Curso Filter -->
          <div>
            <label for="curso" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Curso
            </label>
            <select 
              v-model="filters.curso_id"
              @change="updateFilters"
              id="curso"
              class="w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:text-white"
            >
              <option value="">Todos los cursos</option>
              <option v-for="curso in cursos" :key="curso.id" :value="curso.id">
                {{ curso.nombre }}
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Pendientes</p>
            <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ stats.pendientes }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-green-600 dark:text-green-400">Aprobadas</p>
            <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ stats.aprobadas }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-red-600 dark:text-red-400">Rechazadas</p>
            <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ stats.rechazadas }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Preinscripciones Table -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
          <div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
              Preinscripciones
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Gestiona las preinscripciones de los participantes
            </p>
          </div>
          <div class="mt-4 sm:mt-0">
            <button
              @click="exportarPDF"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              Exportar PDF
            </button>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  ID
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Participante
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Curso
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Fecha
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Estado
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="preinscripcion in preinscripciones.data" :key="preinscripcion.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                  #{{ String(preinscripcion.id).padStart(6, '0') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                          {{ preinscripcion.participante.nombre.charAt(0) }}{{ preinscripcion.participante.apellido.charAt(0) }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ preinscripcion.participante.nombre }} {{ preinscripcion.participante.apellido }}
                      </div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ preinscripcion.participante.carnet }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 dark:text-white">{{ preinscripcion.curso.nombre }}</div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">{{ preinscripcion.curso.tutor.nombre_completo }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ formatDate(preinscripcion.fecha_preinscripcion) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusBadgeClass(preinscripcion.estado)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ preinscripcion.estado }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                  <Link :href="`/admin/preinscripciones/${preinscripcion.id}`" 
                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                    Ver
                  </Link>
                  
                  <button v-if="preinscripcion.estado === 'PENDIENTE'"
                          @click="aprobarPreinscripcion(preinscripcion.id)"
                          class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                    Aprobar
                  </button>
                  
                  <button v-if="preinscripcion.estado === 'PENDIENTE'"
                          @click="rechazarPreinscripcion(preinscripcion.id)"
                          class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                    Rechazar
                  </button>

                  <Link v-if="preinscripcion.estado === 'APROBADA' && !preinscripcion.tiene_pago"
                        :href="`/admin/pagos/registrar?preinscripcion=${preinscripcion.id}`"
                        class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300">
                    Registrar Pago
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="preinscripciones.links.length > 3" class="mt-6">
          <nav class="flex items-center justify-between">
            <div class="hidden sm:block">
              <p class="text-sm text-gray-700 dark:text-gray-300">
                Mostrando {{ preinscripciones.from }} a {{ preinscripciones.to }} de {{ preinscripciones.total }} resultados
              </p>
            </div>
            <div class="flex-1 flex justify-between sm:justify-end space-x-2">
              <template v-for="link in preinscripciones.links" :key="link.label">
                <Link 
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    'px-3 py-2 text-sm rounded-md',
                    link.active 
                      ? 'bg-blue-600 text-white' 
                      : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600'
                  ]"
                >
                  <span>{{ link.label.replace('&laquo;', '«').replace('&raquo;', '»') }}</span>
                </Link>
                <span 
                  v-else
                  :class="[
                    'px-3 py-2 text-sm rounded-md',
                    'bg-gray-100 dark:bg-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                  ]"
                >
                  {{ link.label.replace('&laquo;', '«').replace('&raquo;', '»') }}
                </span>
              </template>
            </div>
          </nav>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <!-- Aprobar Modal -->
    <div v-show="showAprobarModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showAprobarModal = false"></div>
        
        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                  Aprobar Preinscripción
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    ¿Estás seguro de que deseas aprobar esta preinscripción? Esta acción permitirá al participante proceder con el pago.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              @click="confirmarAprobacion"
              type="button" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Aprobar
            </button>
            <button 
              @click="showAprobarModal = false"
              type="button" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Rechazar Modal -->
    <div v-show="showRechazarModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRechazarModal = false"></div>
        
        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                  Rechazar Preinscripción
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    ¿Estás seguro de que deseas rechazar esta preinscripción? Por favor proporciona una razón.
                  </p>
                  <textarea 
                    v-model="razonRechazo"
                    rows="3"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:text-white"
                    placeholder="Motivo del rechazo..."
                  ></textarea>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              @click="confirmarRechazo"
              type="button" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Rechazar
            </button>
            <button 
              @click="showRechazarModal = false"
              type="button" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

// Props interfaces
interface Participante {
  nombre: string
  apellido: string
  carnet: string
}

interface Tutor {
  nombre_completo: string
}

interface Curso {
  id: number
  nombre: string
  tutor: Tutor
}

interface Preinscripcion {
  id: number
  estado: string
  fecha_preinscripcion: string
  tiene_pago: boolean
  participante: Participante
  curso: Curso
}

interface PaginatedPreinscripciones {
  data: Preinscripcion[]
  links: Array<{
    url: string | null
    label: string
    active: boolean
  }>
  from: number
  to: number
  total: number
}

interface Stats {
  pendientes: number
  aprobadas: number
  rechazadas: number
}

interface CursoOption {
  id: number
  nombre: string
}

interface Props {
  preinscripciones: PaginatedPreinscripciones
  stats: Stats
  cursos: CursoOption[]
  filters: {
    search: string
    estado: string
    curso_id: string
  }
}

const props = defineProps<Props>()

// Reactive data
const filters = reactive({
  search: props.filters.search || '',
  estado: props.filters.estado || '',
  curso_id: props.filters.curso_id || ''
})

const showAprobarModal = ref(false)
const showRechazarModal = ref(false)
const preinscripcionSeleccionada = ref<number | null>(null)
const razonRechazo = ref('')

// Methods
const updateFilters = () => {
  router.get('/admin/preinscripciones', filters, {
    preserveState: true,
    replace: true
  })
}

const aprobarPreinscripcion = (id: number) => {
  preinscripcionSeleccionada.value = id
  showAprobarModal.value = true
}

const rechazarPreinscripcion = (id: number) => {
  preinscripcionSeleccionada.value = id
  showRechazarModal.value = true
  razonRechazo.value = ''
}

const confirmarAprobacion = () => {
  if (preinscripcionSeleccionada.value) {
    showAprobarModal.value = false
    router.patch(`/admin/preinscripciones/${preinscripcionSeleccionada.value}/aprobar`, {}, {
      onSuccess: () => {
        preinscripcionSeleccionada.value = null
        // Redirigir a la misma página para actualizar datos
        router.visit(route('admin.preinscripciones.index'), {
          preserveScroll: true
        })
      },
      onError: (errors) => {
        console.error('Error al aprobar:', errors)
        preinscripcionSeleccionada.value = null
      }
    })
  }
}

const confirmarRechazo = () => {
  if (preinscripcionSeleccionada.value) {
    showRechazarModal.value = false
    router.patch(`/admin/preinscripciones/${preinscripcionSeleccionada.value}/rechazar`, {
      observaciones: razonRechazo.value
    }, {
      onSuccess: () => {
        preinscripcionSeleccionada.value = null
        razonRechazo.value = ''
        // Redirigir a la misma página para actualizar datos
        router.visit(route('admin.preinscripciones.index'), {
          preserveScroll: true
        })
      },
      onError: (errors) => {
        console.error('Error al rechazar:', errors)
        preinscripcionSeleccionada.value = null
        razonRechazo.value = ''
      }
    })
  }
}

const exportarPDF = () => {
  window.open('/admin/preinscripciones/export?format=pdf', '_blank')
}

// Helper functions
const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('es-BO', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusBadgeClass = (estado: string): string => {
  const classes = {
    'PENDIENTE': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    'APROBADA': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    'RECHAZADA': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
  }
  return classes[estado as keyof typeof classes] || 'bg-gray-100 text-gray-800'
}
</script>
