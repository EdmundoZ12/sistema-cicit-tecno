<template>
  <AdminLayout page-title="Registrar Pago">
    <div class="max-w-4xl mx-auto">
      <!-- Formulario de Búsqueda de Preinscripción -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Buscar Preinscripción por ID
          </h3>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Ingrese el ID de la preinscripción para procesar el pago
          </p>
        </div>
        <div class="p-6">
          <form @submit.prevent="buscarPreinscripcion" class="flex gap-4">
            <div class="flex-1">
              <label for="preinscripcion_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                ID de Preinscripción
              </label>
              <input
                id="preinscripcion_id"
                v-model="searchForm.preinscripcion_id"
                type="number"
                min="1"
                required
                placeholder="Ej: 123"
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                :disabled="searching"
              />
            </div>
            <div class="flex items-end">
              <button
                type="submit"
                :disabled="searching || !searchForm.preinscripcion_id"
                class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg v-if="searching" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ searching ? 'Buscando...' : 'Buscar' }}
              </button>
            </div>
          </form>

          <!-- Mensaje de Error de Búsqueda -->
          <div v-if="searchError" class="mt-4 p-4 bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800 rounded-md">
            <div class="flex">
              <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <div class="ml-3">
                <p class="text-sm text-red-800 dark:text-red-200">{{ searchError }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Información de la Preinscripción Encontrada -->
      <div v-if="preinscripcionData" class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Información de la Preinscripción
          </h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Información del Participante -->
            <div>
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Participante</h4>
              <dl class="space-y-2">
                <div>
                  <dt class="text-sm text-gray-500 dark:text-gray-400">Nombre completo</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ preinscripcionData.preinscripcion.participante.nombre }} 
                    {{ preinscripcionData.preinscripcion.participante.apellido }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm text-gray-500 dark:text-gray-400">Carnet de Identidad</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ preinscripcionData.preinscripcion.participante.carnet }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm text-gray-500 dark:text-gray-400">Tipo de Participante</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ preinscripcionData.preinscripcion.participante.tipo_participante?.nombre }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm text-gray-500 dark:text-gray-400">Email</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ preinscripcionData.preinscripcion.participante.email }}
                  </dd>
                </div>
              </dl>
            </div>

            <!-- Información del Curso -->
            <div>
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Curso</h4>
              <dl class="space-y-2">
                <div>
                  <dt class="text-sm text-gray-500 dark:text-gray-400">Nombre del curso</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ preinscripcionData.preinscripcion.curso.nombre }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm text-gray-500 dark:text-gray-400">Tutor</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ preinscripcionData.preinscripcion.curso.tutor?.nombre }} 
                    {{ preinscripcionData.preinscripcion.curso.tutor?.apellido }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm text-gray-500 dark:text-gray-400">Precio aplicable</dt>
                  <dd class="text-lg font-bold text-green-600 dark:text-green-400">
                    Bs {{ formatMoney(preinscripcionData.precio_aplicable?.precio || 0) }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm text-gray-500 dark:text-gray-400">Fecha de preinscripción</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ formatDate(preinscripcionData.preinscripcion.fecha_preinscripcion) }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Estado de la Preinscripción -->
          <div class="mt-6 p-4 bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800 rounded-md">
            <div class="flex">
              <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <div class="ml-3">
                <p class="text-sm text-green-800 dark:text-green-200">
                  <strong>Estado:</strong> {{ preinscripcionData.preinscripcion.estado }}
                  - La preinscripción está lista para procesar el pago.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Formulario de Registro de Pago -->
      <div v-if="preinscripcionData" class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Registro de Pago
          </h3>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Complete la información del pago para inscribir oficialmente al participante
          </p>
        </div>
        <div class="p-6">
          <form @submit.prevent="procesarPago">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="monto" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Monto Pagado (Bs) *
                </label>
                <input
                  id="monto"
                  v-model="paymentForm.monto"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-300': errors.monto }"
                />
                <p v-if="errors.monto" class="mt-1 text-sm text-red-600">{{ errors.monto }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                  Precio sugerido: Bs {{ formatMoney(preinscripcionData.precio_aplicable?.precio || 0) }}
                </p>
              </div>

              <div>
                <label for="recibo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Número de Recibo *
                </label>
                <input
                  id="recibo"
                  v-model="paymentForm.recibo"
                  type="text"
                  maxlength="50"
                  required
                  placeholder="Ej: REC-2024-001234"
                  class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-300': errors.recibo }"
                />
                <p v-if="errors.recibo" class="mt-1 text-sm text-red-600">{{ errors.recibo }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                  Debe ser único. Máximo 50 caracteres.
                </p>
              </div>
            </div>

            <div class="mt-6">
              <label for="observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Observaciones
              </label>
              <textarea
                id="observaciones"
                v-model="paymentForm.observaciones"
                rows="3"
                maxlength="500"
                placeholder="Observaciones adicionales sobre el pago (opcional)"
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.observaciones }"
              ></textarea>
              <p v-if="errors.observaciones" class="mt-1 text-sm text-red-600">{{ errors.observaciones }}</p>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ paymentForm.observaciones?.length || 0 }}/500 caracteres
              </p>
            </div>

            <!-- Confirmación -->
            <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/50 border border-yellow-200 dark:border-yellow-800 rounded-md">
              <div class="flex">
                <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.134 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div class="ml-3">
                  <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Confirmación</h4>
                  <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                    Al procesar este pago, se creará automáticamente la <strong>inscripción oficial</strong> 
                    del participante al curso. Esta acción no se puede deshacer.
                  </p>
                </div>
              </div>
            </div>

            <!-- Botones de Acción -->
            <div class="mt-6 flex justify-end space-x-3">
              <Link
                :href="route('admin.pagos.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="processing"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ processing ? 'Procesando...' : 'Procesar Pago e Inscribir Participante' }}
              </button>
            </div>
          </form>

          <!-- Errores generales -->
          <div v-if="errors.error" class="mt-4 p-4 bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800 rounded-md">
            <div class="flex">
              <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <div class="ml-3">
                <p class="text-sm text-red-800 dark:text-red-200">{{ errors.error }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Instrucciones si no hay preinscripción seleccionada -->
      <div v-else class="bg-blue-50 dark:bg-blue-900/50 border border-blue-200 dark:border-blue-800 rounded-md p-6">
        <div class="flex">
          <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
              Instrucciones para Registrar un Pago
            </h3>
            <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
              <ol class="list-decimal list-inside space-y-1">
                <li>Ingrese el ID de la preinscripción y haga clic en "Buscar"</li>
                <li>Verifique que la información del participante y curso sea correcta</li>
                <li>Complete los datos del pago (monto y número de recibo)</li>
                <li>Procese el pago para crear automáticamente la inscripción oficial</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import axios from 'axios'

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
  email: string
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
  participante: Participante
  curso: Curso
  fecha_preinscripcion: string
  estado: string
}

interface PrecioAplicable {
  id: number
  precio: number
  tipo_participante_id: number
}

interface PreinscripcionData {
  preinscripcion: Preinscripcion
  precio_aplicable: PrecioAplicable
  participante: Participante
  curso: Curso
}

// Props
interface Props {
  preinscripcion?: Preinscripcion
  errors: Record<string, string>
}

const props = defineProps<Props>()

// Reactive data
const searching = ref(false)
const processing = ref(false)
const searchError = ref('')
const preinscripcionData = ref<PreinscripcionData | null>(null)

const searchForm = reactive({
  preinscripcion_id: ''
})

const paymentForm = useForm({
  preinscripcion_id: 0,
  monto: '',
  recibo: '',
  observaciones: ''
})

// Si hay una preinscripción preseleccionada, buscarla automáticamente
if (props.preinscripcion) {
  searchForm.preinscripcion_id = props.preinscripcion.id.toString()
  buscarPreinscripcion()
}

// Methods
async function buscarPreinscripcion() {
  if (!searchForm.preinscripcion_id) return

  searching.value = true
  searchError.value = ''
  preinscripcionData.value = null

  try {
    const response = await axios.post('/admin/pagos/buscar-preinscripcion', {
      id: parseInt(searchForm.preinscripcion_id)
    })

    if (response.data.success) {
      preinscripcionData.value = response.data.data
      
      // Pre-llenar el formulario de pago
      paymentForm.preinscripcion_id = preinscripcionData.value.preinscripcion.id
      paymentForm.monto = preinscripcionData.value.precio_aplicable?.precio?.toString() || ''
    }
  } catch (error: any) {
    if (error.response?.data?.message) {
      searchError.value = error.response.data.message
    } else {
      searchError.value = 'Error al buscar la preinscripción. Verifique el ID e intente nuevamente.'
    }
  } finally {
    searching.value = false
  }
}

function procesarPago() {
  processing.value = true
  paymentForm.post(route('admin.pagos.store'), {
    onFinish: () => {
      processing.value = false
    }
  })
}

const formatMoney = (amount: number): string => {
  return new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  }).format(date)
}
</script>
