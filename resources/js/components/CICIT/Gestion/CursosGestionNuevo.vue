<template>
  <div class="space-y-4" :class="themeClasses">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Gestión de Cursos</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Administrar cursos del sistema CICIT</p>
      </div>
      <button
        @click="openCreateModal"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
      >
        <Plus class="w-4 h-4 mr-2" />
        Nuevo Curso
      </button>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center">
          <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
            <BookOpen class="w-5 h-5 text-blue-600 dark:text-blue-400" />
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Cursos</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ stats.total }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center">
          <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
            <CheckCircle class="w-5 h-5 text-green-600 dark:text-green-400" />
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Activos</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ stats.activos }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center">
          <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
            <Clock class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">En Progreso</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ stats.en_progreso }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center">
          <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
            <Users class="w-5 h-5 text-purple-600 dark:text-purple-400" />
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Con Inscripciones</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ stats.con_inscripciones }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <Card class="border border-gray-200 dark:border-gray-700">
      <CardContent class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Buscar
            </label>
            <input
              v-model="localFilters.search"
              type="text"
              placeholder="Nombre del curso..."
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm dark:bg-gray-800 dark:text-white"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Estado
            </label>
            <select
              v-model="localFilters.activo"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm dark:bg-gray-800 dark:text-white"
            >
              <option value="">Todos</option>
              <option value="true">Activos</option>
              <option value="false">Inactivos</option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              @click="resetFilters"
              class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
            >
              <RotateCcw class="w-4 h-4 mr-2" />
              Limpiar
            </button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Grid de Cards de Cursos -->
    <div v-else-if="filteredCursos.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card v-for="curso in filteredCursos" :key="curso.id" class="border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow duration-200">
        <CardContent class="p-6">
          <!-- Imagen del curso -->
          <div class="mb-4">
            <img
              :src="curso.logo_url || '/images/default-course.jpg'"
              :alt="curso.nombre"
              class="w-full h-32 object-cover rounded-lg"
              @error="$event.target.src = '/images/default-course.jpg'"
            />
          </div>

          <!-- Información del curso -->
          <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ curso.nombre }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 line-clamp-2">{{ curso.descripcion || 'Sin descripción' }}</p>

            <!-- Info adicional -->
            <div class="space-y-1">
              <p class="text-sm text-gray-500 dark:text-gray-400">
                <strong>Tutor:</strong> {{ curso.tutor?.nombre }} {{ curso.tutor?.apellido }}
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                <strong>Duración:</strong> {{ curso.duracion_horas }} horas
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                <strong>Cupos:</strong> {{ curso.cupos_ocupados || 0 }}/{{ curso.cupos_totales }}
              </p>
            </div>
          </div>

          <!-- Estado -->
          <div class="mb-4">
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

          <!-- Acciones -->
          <div class="flex justify-between items-center">
            <div class="flex space-x-2">
              <button
                @click="editCurso(curso)"
                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
              >
                <Edit class="w-3 h-3 mr-1" />
                Editar
              </button>

              <button
                @click="viewCurso(curso)"
                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
              >
                <Eye class="w-3 h-3 mr-1" />
                Ver
              </button>
            </div>

            <!-- Toggle Active -->
            <button
              @click="toggleCursoActivo(curso)"
              :class="[
                'inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded transition-colors duration-200',
                curso.activo
                  ? 'text-red-700 bg-red-100 hover:bg-red-200 focus:ring-red-500'
                  : 'text-green-700 bg-green-100 hover:bg-green-200 focus:ring-green-500'
              ]"
            >
              {{ curso.activo ? 'Desactivar' : 'Activar' }}
            </button>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <BookOpen class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay cursos</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ hasFilters ? 'No se encontraron cursos con los filtros aplicados.' : 'Comienza creando un nuevo curso.' }}
      </p>
    </div>

    <!-- Notification -->
    <div
      v-if="notification.show"
      :class="[
        'fixed bottom-4 right-4 max-w-sm w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden z-50',
        notification.type === 'success' ? 'border-l-4 border-green-400' : 'border-l-4 border-red-400'
      ]"
    >
      <div class="p-4">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <CheckCircle v-if="notification.type === 'success'" class="h-6 w-6 text-green-400" />
            <XCircle v-else class="h-6 w-6 text-red-400" />
          </div>
          <div class="ml-3 w-0 flex-1 pt-0.5">
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ notification.message }}</p>
          </div>
          <div class="ml-4 flex-shrink-0 flex">
            <button
              @click="hideNotification"
              class="bg-white dark:bg-gray-800 rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <X class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import {
  Plus,
  Edit,
  Eye,
  BookOpen,
  CheckCircle,
  Clock,
  Users,
  RotateCcw,
  XCircle,
  X
} from 'lucide-vue-next';
import { Card, CardContent } from '@/components/ui/card/index';

// Props
interface UserThemeConfig {
  tema_id: number;
  tamano_fuente: number;
  alto_contraste: boolean;
}

interface Curso {
  id: number;
  nombre: string;
  descripcion?: string;
  duracion_horas: number;
  logo_url?: string;
  activo: boolean;
  cupos_totales: number;
  cupos_ocupados: number;
  tutor?: {
    nombre: string;
    apellido: string;
  };
}

interface Estadisticas {
  total: number;
  activos: number;
  en_progreso: number;
  con_inscripciones: number;
}

interface Filters {
  search: string;
  activo: string;
}

interface Notification {
  show: boolean;
  type: 'success' | 'error';
  message: string;
}

const props = defineProps<{
  userThemeConfig?: UserThemeConfig;
}>();

// Computed para clases de tema
const themeClasses = computed(() => {
  const classes = []

  if (props.userThemeConfig?.alto_contraste) {
    classes.push('high-contrast')
  }

  const fontSize = props.userThemeConfig?.tamano_fuente || 14
  if (fontSize === 12) classes.push('text-xs')
  else if (fontSize === 14) classes.push('text-sm')
  else if (fontSize === 16) classes.push('text-base')
  else if (fontSize === 18) classes.push('text-lg')
  else if (fontSize === 20) classes.push('text-xl')

  return classes.join(' ')
})

// Estados reactivos
const cursos = ref<Curso[]>([])
const loading = ref(true)
const showModal = ref(false)
const isEditing = ref(false)
const processing = ref(false)

// Filtros locales
const localFilters = ref<Filters>({
  search: '',
  activo: ''
})

// Estadísticas
const stats = ref<Estadisticas>({
  total: 0,
  activos: 0,
  en_progreso: 0,
  con_inscripciones: 0
})

// Notificaciones
const notification = ref<Notification>({
  show: false,
  type: 'success',
  message: ''
})

// Computeds
const hasFilters = computed(() => {
  return localFilters.value.search || localFilters.value.activo !== ''
})

const filteredCursos = computed(() => {
  let filtered = cursos.value

  if (localFilters.value.search) {
    const search = localFilters.value.search.toLowerCase()
    filtered = filtered.filter(curso =>
      curso.nombre.toLowerCase().includes(search) ||
      curso.descripcion?.toLowerCase().includes(search) ||
      curso.tutor?.nombre?.toLowerCase().includes(search) ||
      curso.tutor?.apellido?.toLowerCase().includes(search)
    )
  }

  if (localFilters.value.activo !== '') {
    const isActive = localFilters.value.activo === 'true'
    filtered = filtered.filter(curso => curso.activo === isActive)
  }

  return filtered
})

// Métodos
const loadCursos = async () => {
  try {
    loading.value = true

    const response = await fetch('/responsable/cursos', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    if (response.ok) {
      const data = await response.json()
      cursos.value = data.cursosData?.data || []
      stats.value = data.cursosEstadisticas || { total: 0, activos: 0, en_progreso: 0, con_inscripciones: 0 }
      console.log('✅ Cursos loaded:', cursos.value.length)
    } else {
      console.error('Error loading cursos:', response.status, response.statusText)
      showNotification('Error al cargar los cursos', 'error')
    }
  } catch (error) {
    console.error('Error loading cursos:', error)
    showNotification('Error al cargar los cursos', 'error')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  showNotification('Funcionalidad en desarrollo', 'error')
}

const editCurso = (curso: Curso) => {
  showNotification('Funcionalidad en desarrollo', 'error')
}

const viewCurso = (curso: Curso) => {
  showNotification('Funcionalidad en desarrollo', 'error')
}

const toggleCursoActivo = (curso: Curso) => {
  showNotification('Funcionalidad en desarrollo', 'error')
}

const resetFilters = () => {
  localFilters.value = {
    search: '',
    activo: ''
  }
}

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
  notification.value = {
    show: true,
    type,
    message
  }

  setTimeout(() => {
    hideNotification()
  }, 5000)
}

const hideNotification = () => {
  notification.value.show = false
}

// Lifecycle
onMounted(() => {
  loadCursos()

  // Aplicar configuración de tema
  if (props.userThemeConfig) {
    const config = props.userThemeConfig

    if (config.tema_id === 2) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
