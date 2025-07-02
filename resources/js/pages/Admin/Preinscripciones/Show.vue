<template>
  <AdminLayout page-title="Detalles de Preinscripción">
    <div class="max-w-4xl mx-auto">
      <!-- Header con acciones -->
      <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            Preinscripción #{{ preinscripcion.id }}
          </h1>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Registrada el {{ formatDate(preinscripcion.fecha_preinscripcion) }}
          </p>
        </div>
        <div class="flex space-x-3">
          <Link
            :href="route('admin.preinscripciones.index')"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
          </Link>
          
          <!-- Botones de acción según el estado -->
          <div v-if="preinscripcion.estado === 'PENDIENTE'" class="flex space-x-2">
            <button
              @click="aprobar"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Aprobar
            </button>
            <button
              @click="rechazar"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Rechazar
            </button>
          </div>
          
          <Link
            v-if="preinscripcion.estado === 'APROBADA' && !pago"
            :href="route('admin.pagos.create', { preinscripcion: preinscripcion.id })"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Registrar Pago
          </Link>
        </div>
      </div>

      <!-- Estado de la Preinscripción -->
      <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            Estado de la Preinscripción
          </h3>
        </div>
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <span :class="getStatusClasses(preinscripcion.estado)" class="px-3 py-1 rounded-full text-sm font-medium">
                {{ preinscripcion.estado }}
              </span>
              <div class="ml-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ getStatusDescription(preinscripcion.estado) }}
                </p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-500 dark:text-gray-400">Fecha de preinscripción</p>
              <p class="text-sm font-medium text-gray-900 dark:text-white">
                {{ formatDateTime(preinscripcion.fecha_preinscripcion) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Información del Participante -->
      <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            Información del Participante
          </h3>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-700">
          <dl>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Nombre Completo
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                {{ preinscripcion.participante.nombre }} {{ preinscripcion.participante.apellido }}
              </dd>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Carnet de Identidad
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2 font-mono">
                {{ preinscripcion.participante.carnet }}
              </dd>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Email
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                {{ preinscripcion.participante.email }}
              </dd>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Teléfono
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                {{ preinscripcion.participante.telefono || 'No especificado' }}
              </dd>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Tipo de Participante
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                  {{ preinscripcion.participante.tipo_participante?.descripcion }}
                </span>
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Información del Curso -->
      <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            Información del Curso
          </h3>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-700">
          <dl>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Nombre del Curso
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2 font-medium">
                {{ preinscripcion.curso.nombre }}
              </dd>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Tutor
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                {{ preinscripcion.curso.tutor?.nombre }} {{ preinscripcion.curso.tutor?.apellido }}
              </dd>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Fecha de Inicio
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                {{ formatDate(preinscripcion.curso.fecha_inicio) }}
              </dd>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Fecha de Fin
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                {{ formatDate(preinscripcion.curso.fecha_fin) }}
              </dd>
            </div>
            <div v-if="precioAplicable" class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Precio Aplicable
              </dt>
              <dd class="mt-1 text-lg font-bold text-green-600 dark:text-green-400 sm:mt-0 sm:col-span-2">
                Bs {{ formatMoney(precioAplicable.precio) }}
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Observaciones -->
      <div v-if="preinscripcion.observaciones" class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            Observaciones
          </h3>
        </div>
        <div class="p-6">
          <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ preinscripcion.observaciones }}</p>
        </div>
      </div>

      <!-- Información de Pago (si existe) -->
      <div v-if="pago" class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            Pago Registrado
          </h3>
        </div>
        <div class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4 flex-1">
              <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                Pago Procesado
              </h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                Recibo: <span class="font-mono font-medium">{{ pago.recibo }}</span>
              </p>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                Monto: <span class="font-bold text-green-600">Bs {{ formatMoney(pago.monto) }}</span>
              </p>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                Fecha: {{ formatDateTime(pago.fecha_pago) }}
              </p>
            </div>
            <div class="ml-4">
              <Link
                :href="route('admin.pagos.show', pago.id)"
                class="inline-flex items-center px-3 py-1 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
              >
                Ver detalles del pago
                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Información de Inscripción (si existe) -->
      <div v-if="inscripcion" class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            Inscripción Oficial
          </h3>
        </div>
        <div class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
              </svg>
            </div>
            <div class="ml-4">
              <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                Participante Inscrito Oficialmente
              </h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                Fecha de inscripción: {{ formatDateTime(inscripcion.fecha_inscripcion) }}
              </p>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                Estado: <span class="font-medium text-blue-600 dark:text-blue-400">{{ inscripcion.estado }}</span>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Aprobación -->
    <div v-show="showAprobarModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showAprobarModal = false"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
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
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Aprobar
            </button>
            <button
              @click="showAprobarModal = false"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Rechazo -->
    <div v-show="showRechazarModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRechazarModal = false"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
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
                    ¿Estás seguro de que deseas rechazar esta preinscripción? Proporciona una razón para el rechazo.
                  </p>
                  <textarea
                    v-model="razonRechazo"
                    rows="3"
                    placeholder="Motivo del rechazo..."
                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="confirmarRechazo"
              :disabled="!razonRechazo.trim()"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed sm:ml-3 sm:w-auto sm:text-sm"
            >
              Rechazar
            </button>
            <button
              @click="showRechazarModal = false"
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
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

// Interfaces
interface TipoParticipante {
  id: number
  descripcion: string
}

interface Participante {
  id: number
  nombre: string
  apellido: string
  carnet: string
  email: string
  telefono?: string
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
  fecha_inicio: string
  fecha_fin: string
  tutor: Tutor
}

interface Preinscripcion {
  id: number
  participante: Participante
  curso: Curso
  fecha_preinscripcion: string
  estado: string
  observaciones?: string
}

interface PrecioAplicable {
  id: number
  precio: number
}

interface Pago {
  id: number
  recibo: string
  monto: number
  fecha_pago: string
}

interface Inscripcion {
  id: number
  fecha_inscripcion: string
  estado: string
}

// Props
interface Props {
  preinscripcion: Preinscripcion
  precioAplicable?: PrecioAplicable
  pago?: Pago
  inscripcion?: Inscripcion
}

defineProps<Props>()

// Reactive data
const showAprobarModal = ref(false)
const showRechazarModal = ref(false)
const razonRechazo = ref('')

// Methods
const aprobar = () => {
  showAprobarModal.value = true
}

const rechazar = () => {
  showRechazarModal.value = true
  razonRechazo.value = ''
}

const confirmarAprobacion = () => {
  router.patch(route('admin.preinscripciones.aprobar', { id: preinscripcion.id }), {}, {
    onSuccess: () => {
      showAprobarModal.value = false
    },
    onError: (errors) => {
      console.error('Error al aprobar:', errors)
      showAprobarModal.value = false
    }
  })
}

const confirmarRechazo = () => {
  router.patch(route('admin.preinscripciones.rechazar', { id: preinscripcion.id }), {
    observaciones: razonRechazo.value
  }, {
    onSuccess: () => {
      showRechazarModal.value = false
      razonRechazo.value = ''
    },
    onError: (errors) => {
      console.error('Error al rechazar:', errors)
      showRechazarModal.value = false
    }
  })
}

const getStatusClasses = (estado: string): string => {
  switch (estado) {
    case 'PENDIENTE':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'
    case 'APROBADA':
      return 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
    case 'RECHAZADA':
      return 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100'
  }
}

const getStatusDescription = (estado: string): string => {
  switch (estado) {
    case 'PENDIENTE':
      return 'La preinscripción está pendiente de revisión por parte del administrador.'
    case 'APROBADA':
      return 'La preinscripción ha sido aprobada. El participante puede proceder con el pago.'
    case 'RECHAZADA':
      return 'La preinscripción ha sido rechazada.'
    default:
      return 'Estado desconocido.'
  }
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

const formatDateTime = (dateString: string): string => {
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}
</script>
