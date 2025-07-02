<template>
  <div class="space-y-4" :class="themeClasses">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Gestión de Tipos de Participante</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Administrar tipos de participante del sistema CICIT</p>
      </div>
      <button
        @click="openCreateModal"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
      >
        <Plus class="w-4 h-4 mr-2" />
        Nuevo Tipo de Participante
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
              placeholder="Código o descripción..."
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
              class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
            >
              <RotateCcw class="w-4 h-4 mr-2" />
              Limpiar
            </button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <Card>
        <CardContent class="p-4">
          <div class="flex items-center">
            <div class="p-2 bg-teal-100 dark:bg-teal-900 rounded-lg">
              <UserCheck class="h-5 w-5 text-teal-600 dark:text-teal-400" />
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tipos</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-4">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
              <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Activos</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.activos }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-4">
          <div class="flex items-center">
            <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
              <Users class="h-5 w-5 text-purple-600 dark:text-purple-400" />
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Con Participantes</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.conParticipantes }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Tabla de tipos de participante -->
    <Card v-else>
      <CardContent class="p-0">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Código
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Descripción
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Participantes
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Estado
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Fecha Creación
                </th>
                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">Acciones</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr
                v-for="tipo in filteredTipos"
                :key="tipo.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ tipo.codigo }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 dark:text-white">
                    {{ tipo.descripcion }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">
                    <span class="font-medium">{{ tipo.participantes_count || 0 }}</span>
                    <span v-if="tipo.participantes_activos_count !== undefined" class="text-gray-500 dark:text-gray-400">
                      ({{ tipo.participantes_activos_count }} activos)
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="tipo.activo
                      ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                      : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  >
                    {{ tipo.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ formatDate(tipo.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button
                      @click="openEditModal(tipo)"
                      class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200 p-1 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20"
                      title="Editar"
                    >
                      <Edit2 class="w-4 h-4" />
                    </button>
                    <button
                      @click="toggleStatus(tipo)"
                      :class="tipo.activo
                        ? 'text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/20'
                        : 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20'"
                      class="transition-colors duration-200 p-1 rounded-md"
                      :title="tipo.activo ? 'Desactivar' : 'Activar'"
                    >
                      <component :is="tipo.activo ? UserX : UserCheck" class="w-4 h-4" />
                    </button>
                    <button
                      @click="deleteTipo(tipo)"
                      class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200 p-1 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20"
                      title="Eliminar"
                    >
                      <X class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty state -->
          <div v-if="filteredTipos.length === 0" class="text-center py-12">
            <UserCheck class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay tipos de participante</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {{ hasFilters ? 'No se encontraron resultados con los filtros aplicados.' : 'Comienza creando un nuevo tipo de participante.' }}
            </p>
            <div v-if="!hasFilters" class="mt-6">
              <button
                @click="openCreateModal"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <Plus class="w-4 h-4 mr-2" />
                Nuevo Tipo de Participante
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
          <DialogTitle>{{ isEditing ? 'Editar Tipo de Participante' : 'Nuevo Tipo de Participante' }}</DialogTitle>
          <DialogDescription>
            {{ isEditing ? 'Modifica los datos del tipo de participante.' : 'Completa los datos para crear un nuevo tipo de participante.' }}
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label for="codigo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Código *
            </label>
            <input
              id="codigo"
              v-model="form.codigo"
              type="text"
              maxlength="10"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-800 dark:text-white uppercase"
              :class="{ 'border-red-500': errors.codigo }"
              placeholder="Ej: ESTUDIANTE"
              required
              :disabled="processing || isEditing"
            />
            <p v-if="errors.codigo" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.codigo }}</p>
          </div>

          <div>
            <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Descripción *
            </label>
            <input
              id="descripcion"
              v-model="form.descripcion"
              type="text"
              maxlength="255"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-800 dark:text-white"
              :class="{ 'border-red-500': errors.descripcion }"
              placeholder="Ej: Estudiante de pregrado"
              required
              :disabled="processing"
            />
            <p v-if="errors.descripcion" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.descripcion }}</p>
          </div>

          <div v-if="isEditing">
            <label class="flex items-center">
              <input
                v-model="form.activo"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                :disabled="processing"
              />
              <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Activo</span>
            </label>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              :disabled="processing"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="processing"
            >
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
    <div
      v-if="notification.show"
      :class="[
        'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-md transition-all duration-300 transform',
        notification.type === 'success'
          ? 'bg-green-100 border border-green-200 text-green-800 dark:bg-green-900 dark:border-green-700 dark:text-green-200'
          : notification.type === 'error'
          ? 'bg-red-100 border border-red-200 text-red-800 dark:bg-red-900 dark:border-red-700 dark:text-red-200'
          : 'bg-blue-100 border border-blue-200 text-blue-800 dark:bg-blue-900 dark:border-blue-700 dark:text-blue-200'
      ]"
    >
      <div class="flex items-center">
        <component :is="notification.type === 'success' ? CheckCircle : notification.type === 'error' ? XCircle : Info" class="w-5 h-5 mr-2" />
        <p class="text-sm font-medium">{{ notification.message }}</p>
        <button
          @click="hideNotification"
          class="ml-auto pl-3"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import {
  Plus, Edit2, UserCheck, UserX, RotateCcw, CheckCircle, XCircle, Info, X, Users
} from 'lucide-vue-next'
import Card from '@/components/ui/card/Card.vue'
import CardContent from '@/components/ui/card/CardContent.vue'
import Dialog from '@/components/ui/dialog/Dialog.vue'
import DialogContent from '@/components/ui/dialog/DialogContent.vue'
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue'
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue'
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue'

// Tipos
interface TipoParticipante {
  id: number
  codigo: string
  descripcion: string
  activo: boolean
  created_at: string
  updated_at: string
  participantes_count?: number
  participantes_activos_count?: number
}

interface Estadisticas {
  total: number
  activos: number
  conParticipantes: number
}

interface Notification {
  show: boolean
  type: 'success' | 'error' | 'info'
  message: string
}

interface Filters {
  search: string
  activo: string
}

interface UserThemeConfig {
  tema_id: number
  tamano_fuente: number
  alto_contraste: boolean
  modo_automatico: boolean
}

// Props
const props = defineProps<{
  userThemeConfig?: UserThemeConfig | null
  tiposParticipante?: any
  estadisticas?: Estadisticas
  filters?: any
}>()

// Configuración de tema
const themeClasses = computed(() => {
  const classes = []

  if (props.userThemeConfig?.alto_contraste) {
    classes.push('high-contrast')
  }

  // Aplicar tamaño de fuente
  const fontSize = props.userThemeConfig?.tamano_fuente || 14
  if (fontSize === 12) classes.push('text-xs')
  else if (fontSize === 14) classes.push('text-sm')
  else if (fontSize === 16) classes.push('text-base')
  else if (fontSize === 18) classes.push('text-lg')
  else if (fontSize === 20) classes.push('text-xl')

  return classes.join(' ')
})

// Estados reactivos
const tipos = ref<TipoParticipante[]>([])
const loading = ref(true)
const showModal = ref(false)
const isEditing = ref(false)
const processing = ref(false)
const errors = ref<Record<string, string>>({})

// Filtros locales
const localFilters = ref<Filters>({
  search: '',
  activo: ''
})

// Estadísticas
const stats = ref<Estadisticas>({
  total: 0,
  activos: 0,
  conParticipantes: 0
})

// Notificaciones
const notification = ref<Notification>({
  show: false,
  type: 'success',
  message: ''
})

// Formulario
const form = useForm({
  id: null as number | null,
  codigo: '',
  descripcion: '',
  activo: true as boolean
})

// Normalizar código a mayúsculas automáticamente
watch(
  () => form.codigo,
  (val) => {
    if (val && val !== val.toUpperCase()) {
      form.codigo = val.toUpperCase();
    }
  }
)

// Computed
const filteredTipos = computed(() => {
  let result = tipos.value

  if (localFilters.value.search) {
    const search = localFilters.value.search.toLowerCase()
    result = result.filter(tipo =>
      tipo.codigo.toLowerCase().includes(search) ||
      tipo.descripcion.toLowerCase().includes(search)
    )
  }

  if (localFilters.value.activo !== '') {
    const isActive = localFilters.value.activo === 'true'
    result = result.filter(tipo => tipo.activo === isActive)
  }

  return result
})

const hasFilters = computed(() => {
  return localFilters.value.search || localFilters.value.activo !== ''
})

// Métodos
const loadTipos = async () => {
  try {
    loading.value = true

    const response = await fetch('/responsable/tipos-participante', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    if (response.ok) {
      const data = await response.json()
      tipos.value = data.tiposParticipante?.data || []
      stats.value = data.estadisticas || { total: 0, activos: 0, conParticipantes: 0 }
    } else {
      console.error('Error loading tipos:', response.status, response.statusText)
      showNotification('Error al cargar los tipos de participante', 'error')
    }
  } catch (error) {
    console.error('Error loading tipos:', error)
    showNotification('Error al cargar los tipos de participante', 'error')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  isEditing.value = false
  form.reset()
  form.codigo = ''
  form.descripcion = ''
  form.activo = true
  errors.value = {}
  showModal.value = true
}

const openEditModal = (tipo: TipoParticipante) => {
  isEditing.value = true
  form.id = tipo.id
  form.codigo = tipo.codigo
  form.descripcion = tipo.descripcion
  form.activo = tipo.activo
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

    // Si está editando, solo enviar el código si realmente cambió
    const payload: any = {
      descripcion: form.descripcion,
      activo: form.activo
    }
    if (!isEditing.value || form.codigo !== tipos.value.find(t => t.id === form.id)?.codigo) {
      payload.codigo = form.codigo
    }

    if (isEditing.value) {
      await form.put(`/responsable/tipos-participante/${form.id}`, {
        ...payload,
        preserveScroll: true,
        onSuccess: () => {
          closeModal()
          loadTipos()
          showNotification('Tipo de participante actualizado exitosamente', 'success')
        },
        onError: (formErrors) => {
          errors.value = formErrors
          console.error('Validation errors:', formErrors)
        }
      })
    } else {
      await form.post('/responsable/tipos-participante', {
        ...payload,
        preserveScroll: true,
        onSuccess: () => {
          closeModal()
          loadTipos()
          showNotification('Tipo de participante creado exitosamente', 'success')
        },
        onError: (formErrors) => {
          errors.value = formErrors
          console.error('Validation errors:', formErrors)
        }
      })
    }
  } catch (error) {
    console.error('Submit error:', error)
    showNotification('Error al procesar la solicitud', 'error')
  } finally {
    processing.value = false
  }
}

const toggleStatus = async (tipo: TipoParticipante) => {
  try {
    router.visit(`/responsable/tipos-participante/${tipo.id}/toggle-activo`, {
      method: 'post',
      preserveScroll: true,
      onSuccess: () => {
        loadTipos()
        showNotification(
          tipo.activo ? 'Tipo de participante desactivado exitosamente' : 'Tipo de participante activado exitosamente',
          'success'
        )
      },
      onError: () => {
        showNotification('Error al cambiar el estado del tipo de participante', 'error')
      }
    })
  } catch (error: any) {
    console.error('Toggle status error:', error)
    showNotification('Error al cambiar el estado del tipo de participante', 'error')
  }
}

const deleteTipo = async (tipo: TipoParticipante) => {
  if (!confirm(`¿Seguro que deseas eliminar el tipo de participante "${tipo.codigo}"? Esta acción no se puede deshacer.`)) return
  try {
    processing.value = true
    const response = await fetch(`/responsable/tipos-participante/${tipo.id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content || ''
      }
    })
    if (response.ok) {
      showNotification('Tipo de participante eliminado exitosamente', 'success')
      window.location.reload()
    } else {
      let data = null
      try {
        data = await response.json()
      } catch {}
      showNotification((data && data.message) || 'Error al eliminar el tipo de participante', 'error')
    }
  } catch (error) {
    showNotification('Error al eliminar el tipo de participante', 'error')
  } finally {
    processing.value = false
  }
}

const resetFilters = () => {
  localFilters.value = {
    search: '',
    activo: ''
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

const showNotification = (message: string, type: 'success' | 'error' | 'info' = 'success') => {
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

// Watchers
watch(() => [localFilters.value.search, localFilters.value.activo],
  () => {
    // Recargar datos cuando cambien los filtros si es necesario
  },
  { deep: true }
)

// Lifecycle
onMounted(() => {
  loadTipos()
})
</script>
