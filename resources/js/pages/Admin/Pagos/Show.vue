<template>
  <AdminLayout page-title="Detalles del Pago">
    <div class="max-w-4xl mx-auto">
      <!-- Header con acciones -->
      <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            Pago {{ pago.recibo }}
          </h1>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Registrado el {{ formatDate(pago.fecha_pago) }}
          </p>
        </div>
        <div class="flex space-x-3">
          <Link
            :href="route('admin.pagos.index')"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver a Pagos
          </Link>
          <button
            @click="imprimir"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Imprimir
          </button>
        </div>
      </div>

      <!-- Información del Pago -->
      <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            Información del Pago
          </h3>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-700">
          <dl>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Número de Recibo
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2 font-mono">
                {{ pago.recibo }}
              </dd>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Monto Pagado
              </dt>
              <dd class="mt-1 text-lg font-bold text-green-600 dark:text-green-400 sm:mt-0 sm:col-span-2">
                Bs {{ formatMoney(pago.monto) }}
              </dd>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Fecha de Pago
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                {{ formatDateTime(pago.fecha_pago) }}
              </dd>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                ID de Preinscripción
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                {{ pago.preinscripcion_id }}
              </dd>
            </div>
          </dl>
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
                {{ pago.preinscripcion.participante.nombre }} {{ pago.preinscripcion.participante.apellido }}
              </dd>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Carnet de Identidad
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2 font-mono">
                {{ pago.preinscripcion.participante.carnet }}
              </dd>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Email
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                {{ pago.preinscripcion.participante.email }}
              </dd>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Tipo de Participante
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                  {{ pago.preinscripcion.participante.tipo_participante?.nombre }}
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
                {{ pago.preinscripcion.curso.nombre }}
              </dd>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Tutor
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                {{ pago.preinscripcion.curso.tutor?.nombre }} {{ pago.preinscripcion.curso.tutor?.apellido }}
              </dd>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Modalidad
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                {{ pago.preinscripcion.curso.modalidad }}
              </dd>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Fecha de Inicio
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                {{ formatDate(pago.preinscripcion.curso.fecha_inicio) }}
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Estado de la Inscripción -->
      <div v-if="inscripcion" class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            Inscripción Oficial
          </h3>
        </div>
        <div class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
                Estado: <span class="font-medium text-green-600 dark:text-green-400">{{ inscripcion.estado }}</span>
              </p>
              <p v-if="inscripcion.observaciones" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                <strong>Observaciones:</strong> {{ inscripcion.observaciones }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Si no hay inscripción, mostrar advertencia -->
      <div v-else class="bg-yellow-50 dark:bg-yellow-900/50 border border-yellow-200 dark:border-yellow-800 rounded-md p-6">
        <div class="flex">
          <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.134 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
              Inscripción Pendiente
            </h3>
            <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
              El pago fue registrado pero no se encontró una inscripción oficial asociada.
              Esto puede indicar un problema en el proceso de inscripción automática.
            </p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
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
  modalidad: string
  fecha_inicio: string
  tutor: Tutor
}

interface Preinscripcion {
  id: number
  participante: Participante
  curso: Curso
}

interface Pago {
  id: number
  recibo: string
  monto: number
  fecha_pago: string
  preinscripcion_id: number
  preinscripcion: Preinscripcion
}

interface Inscripcion {
  id: number
  fecha_inscripcion: string
  estado: string
  observaciones?: string
}

// Props
interface Props {
  pago: Pago
  inscripcion?: Inscripcion
}

defineProps<Props>()

// Methods
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

const imprimir = () => {
  window.print()
}
</script>

<style>
@media print {
  .no-print {
    display: none !important;
  }
}
</style>
