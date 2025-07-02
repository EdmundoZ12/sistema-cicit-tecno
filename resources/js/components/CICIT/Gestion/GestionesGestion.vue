<template>
  <div class="space-y-4" :class="themeClasses">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Gestión de Gestiones</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Administrar gestiones académicas del sistema CICIT</p>
      </div>
      <button
        @click="openCreateModal"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
      >
        <Plus class="w-4 h-4 mr-2" />
        Nueva Gestión
      </button>
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
              placeholder="Nombre o año..."
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm dark:bg-gray-800 dark:text-white"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Estado
            </label>
            <select
              v-model="localFilters.estado"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm dark:bg-gray-800 dark:text-white"
            >
              <option value="">Todas</option>
              <option value="actual">Actual</option>
              <option value="futura">Futura</option>
              <option value="pasada">Finalizada</option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              @click="resetFilters"
              class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
            >
              <RotateCcw class="w-4 h-4 mr-2" />
              Limpiar
            </button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Tabla de gestiones -->
    <Card>
      <CardContent class="p-0">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha Inicio</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha Fin</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cursos</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Activo</th>
                <th class="px-6 py-3 relative">
                  <span class="sr-only">Acciones</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="gestion in filteredGestiones" :key="gestion.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ gestion.nombre }}</div>
                    <div v-if="gestion.descripcion" class="text-sm text-gray-500 dark:text-gray-400">{{ gestion.descripcion }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="{
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': gestion.estado_calculado === 'En Curso',
                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': gestion.estado_calculado === 'Próxima',
                    'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': gestion.estado_calculado === 'Finalizada',
                  }" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ gestion.estado_calculado || 'Sin estado' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(gestion.fecha_inicio) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(gestion.fecha_fin) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-900 dark:text-white">{{ gestion.cursos_count || 0 }}</span>
                  <span v-if="gestion.cursos_activos_count" class="text-xs text-gray-500 dark:text-gray-400 ml-1">({{ gestion.cursos_activos_count }} activos)</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="gestion.activo ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ gestion.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button @click="openEditModal(gestion)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200 p-1 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20" title="Editar">
                      <Edit2 class="w-4 h-4" />
                    </button>
                    <button @click="deleteGestion(gestion)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200 p-1 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20" title="Eliminar">
                      <X class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="filteredGestiones.length === 0" class="text-center py-12">
            <Users class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay gestiones</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {{ hasFilters ? 'No se encontraron resultados con los filtros aplicados.' : 'Comienza creando una nueva gestión.' }}
            </p>
            <div v-if="!hasFilters" class="mt-6">
              <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <Plus class="w-4 h-4 mr-2" />
                Nueva Gestión
              </button>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Modal de creación/edición -->
    <Dialog v-model:open="showModal">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>{{ isEditing ? 'Editar Gestión' : 'Nueva Gestión' }}</DialogTitle>
          <DialogDescription>
            {{ isEditing ? 'Modifica los datos de la gestión.' : 'Completa los datos para crear una nueva gestión.' }}
          </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label>
            <input id="nombre" v-model="form.nombre" type="text" maxlength="100" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-800 dark:text-white" :class="{ 'border-red-500': errors.nombre }" placeholder="Ej: Gestión 2025-1" required :disabled="processing" />
            <p v-if="errors.nombre" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.nombre }}</p>
          </div>
          <div>
            <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
            <textarea id="descripcion" v-model="form.descripcion" rows="3" maxlength="1000" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-800 dark:text-white" :class="{ 'border-red-500': errors.descripcion }" placeholder="Descripción opcional de la gestión" :disabled="processing"></textarea>
            <p v-if="errors.descripcion" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.descripcion }}</p>
          </div>
          <div>
            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Inicio *</label>
            <input id="fecha_inicio" v-model="form.fecha_inicio" type="date" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-800 dark:text-white" :class="{ 'border-red-500': errors.fecha_inicio }" required :disabled="processing" />
            <p v-if="errors.fecha_inicio" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.fecha_inicio }}</p>
          </div>
          <div>
            <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Fin *</label>
            <input id="fecha_fin" v-model="form.fecha_fin" type="date" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-800 dark:text-white" :class="{ 'border-red-500': errors.fecha_fin }" required :disabled="processing" />
            <p v-if="errors.fecha_fin" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.fecha_fin }}</p>
          </div>
          <div>
            <label class="flex items-center">
              <input v-model="form.activo" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" :disabled="processing" />
              <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Activo</span>
            </label>
          </div>
          <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" :disabled="processing">Cancelar</button>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed" :disabled="processing">
              <span v-if="processing" class="flex items-center">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                Procesando...
              </span>
              <span v-else>{{ isEditing ? 'Actualizar' : 'Crear' }}</span>
            </button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Notificaciones -->
    <div v-if="notification.show" :class="[
      'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-md transition-all duration-300 transform',
      notification.type === 'success'
        ? 'bg-green-100 border border-green-200 text-green-800 dark:bg-green-900 dark:border-green-700 dark:text-green-200'
        : notification.type === 'error'
        ? 'bg-red-100 border border-red-200 text-red-800 dark:bg-red-900 dark:border-red-700 dark:text-red-200'
        : 'bg-blue-100 border border-blue-200 text-blue-800 dark:bg-blue-900 dark:border-blue-700 dark:text-blue-200'
    ]">
      <div class="flex items-center">
        <component :is="notification.type === 'success' ? CheckCircle : notification.type === 'error' ? XCircle : Info" class="w-5 h-5 mr-2" />
        <p class="text-sm font-medium">{{ notification.message }}</p>
        <button @click="hideNotification" class="ml-auto pl-3">
          <X class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import {
  Plus, Edit2, X, RotateCcw, CheckCircle, XCircle, Info, Users
} from 'lucide-vue-next'
import Card from '@/components/ui/card/Card.vue'
import CardContent from '@/components/ui/card/CardContent.vue'
import Dialog from '@/components/ui/dialog/Dialog.vue'
import DialogContent from '@/components/ui/dialog/DialogContent.vue'
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue'
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue'
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue'

// Tipos
interface Gestion {
  id: number
  nombre: string
  descripcion?: string
  fecha_inicio: string
  fecha_fin: string
  activo: boolean
  estado_calculado?: string
  color_estado?: string
  cursos_count?: number
  cursos_activos_count?: number
  created_at: string
  updated_at: string
}

interface Notification {
  show: boolean
  type: 'success' | 'error' | 'info'
  message: string
}

interface Filters {
  search: string
  estado: string
}

// Tema (puedes adaptar según tu sistema)
const themeClasses = computed(() => '')

// Estados reactivos
const gestiones = ref<Gestion[]>([])
const loading = ref(true)
const showModal = ref(false)
const isEditing = ref(false)
const processing = ref(false)
const errors = ref<Record<string, string>>({})

// Filtros locales
const localFilters = ref<Filters>({
  search: '',
  estado: ''
})

// Notificaciones
const notification = ref<Notification>({
  show: false,
  type: 'success',
  message: ''
})

// Formulario
const form = reactive({
  id: null as number | null,
  nombre: '',
  descripcion: '',
  fecha_inicio: '',
  fecha_fin: '',
  activo: true as boolean,
  reset() {
    this.id = null
    this.nombre = ''
    this.descripcion = ''
    this.fecha_inicio = ''
    this.fecha_fin = ''
    this.activo = true
  }
})

// Computed
const filteredGestiones = computed(() => {
  let result = gestiones.value
  if (localFilters.value.search) {
    const search = localFilters.value.search.toLowerCase()
    result = result.filter(g =>
      g.nombre.toLowerCase().includes(search) ||
      (g.descripcion && g.descripcion.toLowerCase().includes(search))
    )
  }
  if (localFilters.value.estado) {
    const estadoFilter = localFilters.value.estado
    result = result.filter(g => {
      if (estadoFilter === 'actual') return g.estado_calculado === 'En Curso'
      if (estadoFilter === 'futura') return g.estado_calculado === 'Próxima'
      if (estadoFilter === 'pasada') return g.estado_calculado === 'Finalizada'
      return true
    })
  }
  return result
})

const hasFilters = computed(() => {
  return localFilters.value.search || localFilters.value.estado
})

// Métodos
const loadGestiones = async () => {
  try {
    loading.value = true
    const response = await fetch('/responsable/gestiones', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    if (response.ok) {
      const data = await response.json()
      gestiones.value = data.gestiones?.data || []
    } else {
      showNotification('Error al cargar las gestiones', 'error')
    }
  } catch {
    showNotification('Error al cargar las gestiones', 'error')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  isEditing.value = false
  form.reset()
  errors.value = {}
  showModal.value = true
}

const openEditModal = (gestion: Gestion) => {
  isEditing.value = true
  form.id = gestion.id
  form.nombre = gestion.nombre
  form.descripcion = gestion.descripcion || ''
  // Convertir fechas al formato YYYY-MM-DD para inputs type="date"
  form.fecha_inicio = formatDateForInput(gestion.fecha_inicio)
  form.fecha_fin = formatDateForInput(gestion.fecha_fin)
  form.activo = gestion.activo
  errors.value = {}
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  form.reset()
  errors.value = {}
}

const submitForm = async () => {
  if (processing.value) return
  try {
    processing.value = true
    errors.value = {}

    const payload: any = {
      nombre: form.nombre,
      descripcion: form.descripcion || '',
      fecha_inicio: form.fecha_inicio,
      fecha_fin: form.fecha_fin,
      activo: form.activo
    }

    // Para edición, agregar el method spoofing
    if (isEditing.value) {
      payload._method = 'PUT'
    }

    // Debug: ver qué datos se están enviando
    console.log('Enviando datos:', payload)
    console.log('form.activo tipo:', typeof form.activo, 'valor:', form.activo)

    const url = isEditing.value ? `/responsable/gestiones/${form.id}` : '/responsable/gestiones'

    // Debug: ver qué datos se están enviando
    console.log('form.id:', form.id)
    console.log('isEditing:', isEditing.value)
    console.log('form data:', {
      nombre: form.nombre,
      descripcion: form.descripcion,
      fecha_inicio: form.fecha_inicio,
      fecha_fin: form.fecha_fin,
      activo: form.activo
    })

    // Crear FormData para envío correcto con method spoofing
    const formData = new FormData()
    formData.append('nombre', form.nombre)
    formData.append('descripcion', form.descripcion || '')
    formData.append('fecha_inicio', form.fecha_inicio)
    formData.append('fecha_fin', form.fecha_fin)

    // Para edición, agregar el method spoofing
    if (isEditing.value) {
      formData.append('_method', 'PUT')
    }

    console.log('FormData entries:')
    for (const pair of formData.entries()) {
      console.log(pair[0] + ': ' + pair[1])
    }

    const response = await fetch(url, {
      method: 'POST', // Siempre POST, Laravel maneja el spoofing con _method
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content || ''
      },
      body: formData
    })

    if (response.ok) {
      closeModal()
      loadGestiones()
      showNotification(
        isEditing.value ? 'Gestión actualizada exitosamente' : 'Gestión creada exitosamente',
        'success'
      )
    } else {
      const data = await response.json()
      if (data.errors) {
        errors.value = data.errors
      } else {
        showNotification(data.message || 'Error al procesar la solicitud', 'error')
      }
    }
  } catch {
    showNotification('Error al procesar la solicitud', 'error')
  } finally {
    processing.value = false
  }
}

const deleteGestion = async (gestion: Gestion) => {
  if (!confirm(`¿Seguro que deseas eliminar la gestión "${gestion.nombre}"? Esta acción no se puede deshacer.`)) return
  try {
    processing.value = true
    const response = await fetch(`/responsable/gestiones/${gestion.id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content || ''
      }
    })
    if (response.ok) {
      showNotification('Gestión eliminada exitosamente', 'success')
      loadGestiones()
    } else {
      let data = null
      try { data = await response.json() } catch {}
      showNotification((data && data.message) || 'Error al eliminar la gestión', 'error')
    }
  } catch {
    showNotification('Error al eliminar la gestión', 'error')
  } finally {
    processing.value = false
  }
}

const resetFilters = () => {
  localFilters.value = {
    search: '',
    estado: ''
  }
}

const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatDateForInput = (dateString: string) => {
  if (!dateString) return ''
  // Convertir fecha a formato YYYY-MM-DD para inputs type="date"
  const date = new Date(dateString)
  return date.toISOString().split('T')[0]
}

const showNotification = (message: string, type: 'success' | 'error' | 'info' = 'success') => {
  notification.value = {
    show: true,
    type,
    message
  }
  setTimeout(() => { hideNotification() }, 5000)
}

const hideNotification = () => {
  notification.value.show = false
}

onMounted(() => {
  loadGestiones()
})
</script>
