<template>
  <ResponsableLayout>
    <Head title="Editar Curso" />

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Curso</h1>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Modifique la información del curso
              </p>
            </div>
            <Link
              href="/dashboard"
              class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
              @click="goBackToCursos"
            >
              <ArrowLeft class="w-4 h-4 mr-2" />
              Volver
            </Link>
          </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- Información Básica -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Información Básica</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                  <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nombre del Curso *
                  </label>
                  <input
                    id="nombre"
                    v-model="form.nombre"
                    type="text"
                    required
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-700 dark:text-white"
                    :class="{ 'border-red-300': errors.nombre }"
                  />
                  <div v-if="errors.nombre" class="mt-2 text-sm text-red-600">{{ errors.nombre }}</div>
                </div>

                <div class="md:col-span-2">
                  <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Descripción
                  </label>
                  <textarea
                    id="descripcion"
                    v-model="form.descripcion"
                    rows="3"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-700 dark:text-white"
                    :class="{ 'border-red-300': errors.descripcion }"
                  />
                  <div v-if="errors.descripcion" class="mt-2 text-sm text-red-600">{{ errors.descripcion }}</div>
                </div>

                <div>
                  <label for="duracion_horas" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Duración (horas) *
                  </label>
                  <input
                    id="duracion_horas"
                    v-model.number="form.duracion_horas"
                    type="number"
                    min="1"
                    required
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-700 dark:text-white"
                    :class="{ 'border-red-300': errors.duracion_horas }"
                  />
                  <div v-if="errors.duracion_horas" class="mt-2 text-sm text-red-600">{{ errors.duracion_horas }}</div>
                </div>

                <div>
                  <label for="nivel" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nivel
                  </label>
                  <select
                    id="nivel"
                    v-model="form.nivel"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-700 dark:text-white"
                    :class="{ 'border-red-300': errors.nivel }"
                  >
                    <option value="">Seleccionar nivel</option>
                    <option value="Básico">Básico</option>
                    <option value="Intermedio">Intermedio</option>
                    <option value="Avanzado">Avanzado</option>
                  </select>
                  <div v-if="errors.nivel" class="mt-2 text-sm text-red-600">{{ errors.nivel }}</div>
                </div>

                <div>
                  <label for="tutor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Tutor *
                  </label>
                  <select
                    id="tutor_id"
                    v-model="form.tutor_id"
                    required
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-700 dark:text-white"
                    :class="{ 'border-red-300': errors.tutor_id }"
                  >
                    <option value="">Seleccionar tutor</option>
                    <option v-for="tutor in tutores" :key="tutor.id" :value="tutor.id">
                      {{ tutor.nombre }} {{ tutor.apellido }}
                    </option>
                  </select>
                  <div v-if="errors.tutor_id" class="mt-2 text-sm text-red-600">{{ errors.tutor_id }}</div>
                </div>

                <div>
                  <label for="gestion_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Gestión *
                  </label>
                  <select
                    id="gestion_id"
                    v-model="form.gestion_id"
                    required
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-700 dark:text-white"
                    :class="{ 'border-red-300': errors.gestion_id }"
                  >
                    <option value="">Seleccionar gestión</option>
                    <option v-for="gestion in gestiones" :key="gestion.id" :value="gestion.id">
                      {{ gestion.nombre }}
                    </option>
                  </select>
                  <div v-if="errors.gestion_id" class="mt-2 text-sm text-red-600">{{ errors.gestion_id }}</div>
                </div>
              </div>
            </div>

            <!-- Detalles del Curso -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Detalles del Curso</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label for="aula" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Aula
                  </label>
                  <input
                    id="aula"
                    v-model="form.aula"
                    type="text"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-700 dark:text-white"
                    :class="{ 'border-red-300': errors.aula }"
                  />
                  <div v-if="errors.aula" class="mt-2 text-sm text-red-600">{{ errors.aula }}</div>
                </div>

                <div>
                  <label for="cupos_totales" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Cupos Totales *
                  </label>
                  <input
                    id="cupos_totales"
                    v-model.number="form.cupos_totales"
                    type="number"
                    :min="curso.cupos_ocupados || 1"
                    required
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-700 dark:text-white"
                    :class="{ 'border-red-300': errors.cupos_totales }"
                  />
                  <div v-if="errors.cupos_totales" class="mt-2 text-sm text-red-600">{{ errors.cupos_totales }}</div>
                  <p class="mt-1 text-xs text-gray-500">Cupos ocupados: {{ curso.cupos_ocupados || 0 }}</p>
                </div>

                <div>
                  <label for="logo_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Logo del Curso
                  </label>
                  <input
                    id="logo_file"
                    ref="logoInput"
                    type="file"
                    accept="image/*"
                    @change="handleLogoUpload"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                  />
                  <div v-if="form.logo_url" class="mt-2">
                    <img :src="form.logo_url" alt="Logo preview" class="h-20 w-20 object-cover rounded-md" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Fechas -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Fechas</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Fecha de Inicio *
                  </label>
                  <input
                    id="fecha_inicio"
                    v-model="form.fecha_inicio"
                    type="date"
                    required
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-700 dark:text-white"
                    :class="{ 'border-red-300': errors.fecha_inicio }"
                  />
                  <div v-if="errors.fecha_inicio" class="mt-2 text-sm text-red-600">{{ errors.fecha_inicio }}</div>
                </div>

                <div>
                  <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Fecha de Fin *
                  </label>
                  <input
                    id="fecha_fin"
                    v-model="form.fecha_fin"
                    type="date"
                    required
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-700 dark:text-white"
                    :class="{ 'border-red-300': errors.fecha_fin }"
                  />
                  <div v-if="errors.fecha_fin" class="mt-2 text-sm text-red-600">{{ errors.fecha_fin }}</div>
                </div>
              </div>
            </div>

            <!-- Estado -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Estado</h3>
              <div class="flex items-center">
                <input
                  id="activo"
                  v-model="form.activo"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="activo" class="ml-2 block text-sm text-gray-900 dark:text-white">
                  Curso activo
                </label>
              </div>
            </div>

            <!-- Precios por Tipo de Participante -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Precios por Tipo de Participante</h3>
              <div class="space-y-4">
                <div v-for="(precio, index) in form.precios" :key="index" class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border border-gray-200 dark:border-gray-600 rounded-md">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Tipo de Participante
                    </label>
                    <select
                      v-model="precio.tipo_participante_id"
                      required
                      class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-700 dark:text-white"
                    >
                      <option value="">Seleccionar tipo</option>
                      <option v-for="tipo in tiposParticipante" :key="tipo.id" :value="tipo.id">
                        {{ tipo.codigo }} - {{ tipo.descripcion }}
                      </option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Precio (Bs.)
                    </label>
                    <input
                      v-model.number="precio.precio"
                      type="number"
                      min="0"
                      step="0.01"
                      required
                      class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm dark:bg-gray-700 dark:text-white"
                    />
                  </div>

                  <div class="flex items-end">
                    <button
                      v-if="form.precios.length > 1"
                      type="button"
                      @click="removePrecio(index)"
                      class="inline-flex items-center px-3 py-2 border border-red-300 text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </div>

                <button
                  type="button"
                  @click="addPrecio"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <Plus class="w-4 h-4 mr-2" />
                  Agregar Precio
                </button>
              </div>
              <div v-if="errors.precios" class="mt-2 text-sm text-red-600">{{ errors.precios }}</div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-600">
              <Link
                href="/dashboard"
                class="inline-flex justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                @click="goBackToCursos"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="processing"
                class="inline-flex justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
              >
                <span v-if="processing" class="inline-flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Actualizando...
                </span>
                <span v-else>Actualizar Curso</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </ResponsableLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import ResponsableLayout from '@/layouts/CICITLayout.vue'
import { ArrowLeft, Plus, Trash2 } from 'lucide-vue-next'

// Types
interface Curso {
  id: number
  nombre: string
  descripcion?: string
  duracion_horas: number
  nivel?: string
  logo_url?: string
  tutor_id: number
  gestion_id: number
  aula?: string
  cupos_totales: number
  cupos_ocupados: number
  fecha_inicio: string
  fecha_fin: string
  activo: boolean
  precios: Array<{
    tipo_participante_id: number
    precio: number
  }>
}

// Props
const props = defineProps<{
  curso: Curso
  tutores: Array<{ id: number; nombre: string; apellido: string }>
  gestiones: Array<{ id: number; nombre: string }>
  tiposParticipante: Array<{ id: number; codigo: string; descripcion: string }>
  errors?: Record<string, string>
}>()

// Computed for safe error access
const errors = computed(() => props.errors || {})

// Helper function to format date for input
const formatDateForInput = (dateValue: any): string => {
  if (!dateValue) return ''

  // If it's already a string in YYYY-MM-DD format, return it
  if (typeof dateValue === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(dateValue)) {
    return dateValue
  }

  // If it's a string with time, extract date part
  if (typeof dateValue === 'string' && dateValue.includes(' ')) {
    return dateValue.split(' ')[0]
  }

  // If it's a Date object or other format, convert to YYYY-MM-DD
  try {
    const date = new Date(dateValue)
    if (isNaN(date.getTime())) return ''

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
  } catch {
    return ''
  }
}

// Form
const form = useForm({
  nombre: props.curso.nombre,
  descripcion: props.curso.descripcion || '',
  duracion_horas: props.curso.duracion_horas,
  nivel: props.curso.nivel || '',
  logo_url: props.curso.logo_url || '',
  tutor_id: props.curso.tutor_id,
  gestion_id: props.curso.gestion_id,
  aula: props.curso.aula || '',
  cupos_totales: props.curso.cupos_totales,
  fecha_inicio: formatDateForInput(props.curso.fecha_inicio),
  fecha_fin: formatDateForInput(props.curso.fecha_fin),
  activo: props.curso.activo,
  precios: props.curso.precios.length > 0 ? props.curso.precios.map(p => ({
    tipo_participante_id: p.tipo_participante_id,
    precio: p.precio
  })) : [
    { tipo_participante_id: 0, precio: 0 }
  ]
})

const processing = ref(false)
const logoInput = ref<HTMLInputElement>()

// Methods
const goBackToCursos = (e: Event) => {
  e.preventDefault()
  // Navegar al dashboard y activar la sección de cursos
  window.location.href = '/dashboard'
  // Usar localStorage para indicar que debe activar la sección de cursos
  localStorage.setItem('activeDashboardSection', 'cursos')
}

const handleLogoUpload = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return

  // Validar tipo de archivo en frontend
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
  if (!validTypes.includes(file.type)) {
    alert('Por favor seleccione una imagen válida (JPEG, PNG, WEBP)')
    if (logoInput.value) logoInput.value.value = ''
    return
  }

  // Validar tamaño (2MB)
  if (file.size > 2 * 1024 * 1024) {
    alert('La imagen no debe superar los 2MB')
    if (logoInput.value) logoInput.value.value = ''
    return
  }

  const formData = new FormData()
  formData.append('image', file)

  try {
    // Obtener token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                      document.querySelector('input[name="_token"]')?.getAttribute('value')

    const headers: Record<string, string> = {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    }

    if (csrfToken) {
      headers['X-CSRF-TOKEN'] = csrfToken
    }

    const response = await fetch('/responsable/cursos/upload-image', {
      method: 'POST',
      body: formData,
      headers,
      credentials: 'same-origin' // Include cookies for session-based auth
    })

    const data = await response.json()

    if (response.ok && data.success) {
      form.logo_url = data.url
      console.log('Imagen subida exitosamente:', data.url)
    } else {
      console.error('Error response:', data)
      alert('Error al subir la imagen: ' + (data.message || 'Error desconocido'))
      if (logoInput.value) logoInput.value.value = ''
    }
  } catch (error) {
    console.error('Error uploading image:', error)
    alert('Error de conectividad al subir la imagen. Intente nuevamente.')
    if (logoInput.value) logoInput.value.value = ''
  }
}

const addPrecio = () => {
  form.precios.push({ tipo_participante_id: 0, precio: 0 })
}

const removePrecio = (index: number) => {
  form.precios.splice(index, 1)
}

const submit = () => {
  form.put(route('responsable.cursos.update', props.curso.id), {
    onSuccess: () => {
      // Mostrar notificación de éxito
      alert('Curso actualizado exitosamente')
      // Redirigir al dashboard con la sección de cursos activa
      localStorage.setItem('activeDashboardSection', 'cursos')
      window.location.href = '/dashboard'
    },
    onError: () => {
      alert('Error al actualizar el curso. Por favor verifique los datos.')
    }
  })
}
</script>
