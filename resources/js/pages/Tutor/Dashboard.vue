<template>
  <TutorLayout 
    page-title="Dashboard del Tutor" 
    :pending-count="estadisticas.tareas_pendientes"
  >
    <!-- Estadísticas principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Cursos -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                  Cursos Activos
                </dt>
                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ estadisticas.cursos_en_progreso }} / {{ estadisticas.total_cursos }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <!-- Estudiantes -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                  Total Estudiantes
                </dt>
                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ estadisticas.total_estudiantes }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <!-- Tareas Pendientes -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                  Tareas Pendientes
                </dt>
                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ estadisticas.tareas_pendientes }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <!-- Promedio General -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                  Promedio General
                </dt>
                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ estadisticas.promedio_general_cursos }}%
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <!-- Cursos Recientes -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
            Mis Cursos
          </h3>
          <div class="space-y-4">
            <div 
              v-for="curso in cursos_recientes" 
              :key="curso.id"
              class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ curso.nombre }}
                  </h4>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ curso.fecha_inicio }} - {{ curso.fecha_fin }}
                  </p>
                  <div class="flex items-center mt-2 space-x-4">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                      {{ curso.total_estudiantes }} estudiantes
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                      {{ curso.total_tareas }} tareas
                    </span>
                  </div>
                </div>
                <div class="ml-4">
                  <span :class="getEstadoBadgeClass(curso.estado)" class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ getEstadoLabel(curso.estado) }}
                  </span>
                  <div class="mt-2 w-20">
                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                      <div 
                        class="bg-blue-600 h-2 rounded-full" 
                        :style="{ width: curso.progreso + '%' }"
                      ></div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-1">
                      {{ curso.progreso }}%
                    </p>
                  </div>
                </div>
              </div>
              <div class="mt-3">
                <Link 
                  :href="route('tutor.mis-cursos.show', curso.id)" 
                  class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200"
                >
                  Ver detalles →
                </Link>
              </div>
            </div>
          </div>
          <div class="mt-4">
            <Link 
              :href="route('tutor.mis-cursos.index')" 
              class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200"
            >
              Ver todos los cursos →
            </Link>
          </div>
        </div>
      </div>

      <!-- Actividad Reciente -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
            Actividad Reciente
          </h3>
          <div class="space-y-3">
            <div 
              v-for="actividad in actividad_reciente" 
              :key="actividad.titulo"
              class="flex items-start"
            >
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
              <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ actividad.titulo }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  {{ actividad.curso }} • {{ actividad.fecha }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sección inferior con alertas y acciones rápidas -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Estudiantes que necesitan atención -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
            Estudiantes que Necesitan Atención
          </h3>
          <div v-if="estudiantes_atencion.length > 0" class="space-y-3">
            <div 
              v-for="estudiante in estudiantes_atencion" 
              :key="estudiante.nombre"
              class="border-l-4 border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 p-3"
            >
              <div class="flex justify-between items-start">
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ estudiante.nombre }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ estudiante.curso }}
                  </p>
                  <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                    {{ estudiante.motivo }}
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    Promedio: {{ estudiante.promedio_tareas }}%
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    Asistencia: {{ estudiante.porcentaje_asistencia }}%
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">
              ¡Excelente! Todos los estudiantes están al día.
            </p>
          </div>
        </div>
      </div>

      <!-- Tareas próximas -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
            Tareas Próximas
          </h3>
          <div v-if="tareas_proximas.length > 0" class="space-y-3">
            <div 
              v-for="tarea in tareas_proximas" 
              :key="tarea.id"
              class="border border-gray-200 dark:border-gray-700 rounded p-3"
            >
              <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                {{ tarea.titulo }}
              </h4>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ tarea.curso }}
              </p>
              <div class="flex justify-between items-center mt-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  {{ tarea.fecha_asignacion }}
                </span>
                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full">
                  {{ tarea.dias_restantes }} días
                </span>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">
              No hay tareas próximas programadas.
            </p>
          </div>
        </div>
      </div>

      <!-- Acciones rápidas -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
            Acciones Rápidas
          </h3>
          <div class="space-y-3">
            <Link 
              :href="route('tutor.tareas.create')" 
              class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Crear Nueva Tarea
            </Link>

            <Link 
              :href="route('tutor.asistencias.index')" 
              class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
              </svg>
              Marcar Asistencia
            </Link>

            <Link 
              :href="route('tutor.notas.index')" 
              class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              Calificar Tareas
            </Link>
          </div>
        </div>
      </div>
    </div>
  </TutorLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import TutorLayout from '@/layouts/TutorLayout.vue'

interface Estadisticas {
  total_cursos: number
  cursos_activos: number
  cursos_en_progreso: number
  total_estudiantes: number
  estudiantes_aprobados: number
  estudiantes_reprobados: number
  total_tareas: number
  tareas_pendientes: number
  calificaciones_esta_semana: number
  asistencias_hoy: number
  promedio_asistencia_general: number
  promedio_general_cursos: number
  estudiantes_bajo_rendimiento: number
  estudiantes_destacados: number
}

interface Curso {
  id: number
  nombre: string
  fecha_inicio: string
  fecha_fin: string
  total_estudiantes: number
  total_tareas: number
  estado: string
  progreso: number
}

interface Actividad {
  tipo: string
  titulo: string
  curso: string
  fecha: string
  icono: string
}

interface TareaProxima {
  id: number
  titulo: string
  curso: string
  fecha_asignacion: string
  dias_restantes: number
}

interface EstudianteAtencion {
  nombre: string
  curso: string
  promedio_tareas: number
  porcentaje_asistencia: number
  motivo: string
}

interface Props {
  estadisticas: Estadisticas
  cursos_recientes: Curso[]
  actividad_reciente: Actividad[]
  tareas_proximas: TareaProxima[]
  estudiantes_atencion: EstudianteAtencion[]
}

defineProps<Props>()

const getEstadoBadgeClass = (estado: string): string => {
  const classes = {
    'próximo': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    'en_progreso': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    'finalizado': 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
  }
  return classes[estado as keyof typeof classes] || 'bg-gray-100 text-gray-800'
}

const getEstadoLabel = (estado: string): string => {
  const labels = {
    'próximo': 'Próximo',
    'en_progreso': 'En Progreso',
    'finalizado': 'Finalizado'
  }
  return labels[estado as keyof typeof labels] || estado
}
</script>
