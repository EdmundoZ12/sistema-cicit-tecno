<template>
  <AdminLayout :pending-count="stats.preinscripciones_pendientes" page-title="Dashboard">
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      
      <!-- Preinscripciones Pendientes -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                  Preinscripciones Pendientes
                </dt>
                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ stats.preinscripciones_pendientes }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
          <div class="text-sm">
            <Link href="/admin/preinscripciones" class="font-medium text-blue-700 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-100">
              Ver todas
            </Link>
          </div>
        </div>
      </div>

      <!-- Inscripciones Activas -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                  Inscripciones Activas
                </dt>
                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ stats.inscripciones_activas }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
          <div class="text-sm">
            <Link href="/admin/inscripciones" class="font-medium text-blue-700 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-100">
              Ver todas
            </Link>
          </div>
        </div>
      </div>

      <!-- Cursos Activos -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                  Cursos Activos
                </dt>
                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ stats.cursos_activos }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
          <div class="text-sm">
            <Link href="/admin/cursos" class="font-medium text-blue-700 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-100">
              Ver todos
            </Link>
          </div>
        </div>
      </div>

      <!-- Ingresos del Mes -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                  Ingresos del Mes
                </dt>
                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ formatMoney(stats.ingresos_mes) }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
          <div class="text-sm">
            <Link href="/admin/pagos" class="font-medium text-blue-700 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-100">
              Ver pagos
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      
      <!-- Preinscripciones Recientes -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
            Preinscripciones Recientes
          </h3>
          
          <div class="space-y-4">
            <div v-for="preinscripcion in preinscripciones_recientes" :key="preinscripcion.id" 
                 class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
              <div class="flex-1">
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                      <span class="text-sm font-medium text-blue-700 dark:text-blue-300">
                        {{ preinscripcion.participante.nombre.charAt(0) }}{{ preinscripcion.participante.apellido.charAt(0) }}
                      </span>
                    </div>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                      {{ preinscripcion.participante.nombre }} {{ preinscripcion.participante.apellido }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                      {{ preinscripcion.curso.nombre }}
                    </p>
                  </div>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span :class="getStatusBadgeClass(preinscripcion.estado)" 
                      class="px-2 py-1 text-xs rounded-full">
                  {{ preinscripcion.estado }}
                </span>
                <Link :href="`/admin/preinscripciones/${preinscripcion.id}`" 
                      class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </Link>
              </div>
            </div>
            
            <div v-if="preinscripciones_recientes.length === 0" class="text-center py-4">
              <p class="text-gray-500 dark:text-gray-400">No hay preinscripciones recientes</p>
            </div>
          </div>
          
          <div class="mt-4">
            <Link href="/admin/preinscripciones" 
                  class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
              Ver todas las preinscripciones →
            </Link>
          </div>
        </div>
      </div>

      <!-- Pagos Recientes -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
            Pagos Recientes
          </h3>
          
          <div class="space-y-4">
            <div v-for="pago in pagos_recientes" :key="pago.id" 
                 class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
              <div class="flex-1">
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                      <svg class="w-5 h-5 text-green-700 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ formatMoney(pago.monto) }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                      Recibo: {{ pago.recibo }}
                    </p>
                  </div>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm text-gray-900 dark:text-white">
                  {{ formatDate(pago.fecha_pago) }}
                </p>
                <Link :href="`/admin/pagos/${pago.id}`" 
                      class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">
                  Ver detalles
                </Link>
              </div>
            </div>
            
            <div v-if="pagos_recientes.length === 0" class="text-center py-4">
              <p class="text-gray-500 dark:text-gray-400">No hay pagos recientes</p>
            </div>
          </div>
          
          <div class="mt-4">
            <Link href="/admin/pagos" 
                  class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
              Ver todos los pagos →
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Acciones Rápidas</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <Link href="/admin/preinscripciones?estado=PENDIENTE" 
              class="relative group bg-white dark:bg-gray-800 p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg shadow transition-colors">
          <div>
            <span class="rounded-lg inline-flex p-3 bg-yellow-500 text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </span>
          </div>
          <div class="mt-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              Revisar Pendientes
            </h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              Procesar preinscripciones pendientes de aprobación
            </p>
          </div>
        </Link>

        <Link href="/admin/cursos/create" 
              class="relative group bg-white dark:bg-gray-800 p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg shadow transition-colors">
          <div>
            <span class="rounded-lg inline-flex p-3 bg-blue-500 text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
            </span>
          </div>
          <div class="mt-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              Nuevo Curso
            </h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              Crear un nuevo curso disponible
            </p>
          </div>
        </Link>

        <Link href="/admin/pagos/registrar" 
              class="relative group bg-white dark:bg-gray-800 p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg shadow transition-colors">
          <div>
            <span class="rounded-lg inline-flex p-3 bg-green-500 text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
              </svg>
            </span>
          </div>
          <div class="mt-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              Registrar Pago
            </h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              Procesar pago de preinscripción
            </p>
          </div>
        </Link>

        <Link href="/admin/reportes" 
              class="relative group bg-white dark:bg-gray-800 p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg shadow transition-colors">
          <div>
            <span class="rounded-lg inline-flex p-3 bg-purple-500 text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </span>
          </div>
          <div class="mt-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              Ver Reportes
            </h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              Generar reportes de actividad
            </p>
          </div>
        </Link>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'

// Props
interface Preinscripcion {
  id: number
  estado: string
  participante: {
    nombre: string
    apellido: string
  }
  curso: {
    nombre: string
  }
}

interface Pago {
  id: number
  monto: number
  recibo: string
  fecha_pago: string
}

interface Stats {
  preinscripciones_pendientes: number
  inscripciones_activas: number
  cursos_activos: number
  ingresos_mes: number
}

interface Props {
  stats: Stats
  preinscripciones_recientes: Preinscripcion[]
  pagos_recientes: Pago[]
}

defineProps<Props>()

// Helper functions
const formatMoney = (amount: number): string => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB'
  }).format(amount)
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('es-BO', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
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
