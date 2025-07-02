<template>
  <div class="relative">
    <!-- Botón del selector de tema -->
    <button
      @click="isOpen = !isOpen"
      type="button"
      class="relative rounded-full bg-white p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
      :title="`Tema actual: ${currentTheme?.nombre || 'Predeterminado'}`"
    >
      <component :is="currentThemeIcon" class="h-6 w-6" />
    </button>

    <!-- Panel del selector -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 z-10 mt-2 w-80 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
      >
        <div class="p-4">
          <h3 class="text-sm font-medium text-gray-900 mb-4">Personalizar Apariencia</h3>

          <!-- Selección de temas -->
          <div class="mb-4">
            <label class="text-xs font-medium text-gray-700 uppercase tracking-wide">Temas CICIT</label>
            <div class="mt-2 grid grid-cols-2 gap-2">
              <button
                v-for="theme in availableThemes"
                :key="theme.id"
                @click="selectTheme(theme)"
                :class="[
                  'relative flex items-center space-x-2 rounded-md border p-2 text-left text-sm transition-all',
                  currentTheme?.id === theme.id
                    ? 'border-primary-500 bg-primary-50 text-primary-700'
                    : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                ]"
              >
                <!-- Vista previa del tema -->
                <div
                  class="h-4 w-4 rounded-full border border-gray-300"
                  :style="{ backgroundColor: theme.color_primario }"
                ></div>
                <div class="flex-1 min-w-0">
                  <p class="font-medium truncate">{{ theme.nombre }}</p>
                  <p class="text-xs text-gray-500 truncate">{{ theme.target_edad }}</p>
                </div>
                <!-- Indicador de tema activo -->
                <CheckIcon
                  v-if="currentTheme?.id === theme.id"
                  class="h-4 w-4 text-primary-600"
                />
              </button>
            </div>
          </div>

          <!-- Opciones de accesibilidad -->
          <div class="border-t border-gray-200 pt-4">
            <label class="text-xs font-medium text-gray-700 uppercase tracking-wide">Accesibilidad</label>

            <!-- Tamaño de fuente -->
            <div class="mt-3">
              <label class="text-sm text-gray-700">Tamaño de fuente</label>
              <div class="mt-1 flex items-center space-x-3">
                <button
                  @click="adjustFontSize('decrease')"
                  class="rounded-md bg-gray-100 p-1 text-gray-600 hover:bg-gray-200"
                  :disabled="fontSize <= 12"
                >
                  <MinusIcon class="h-4 w-4" />
                </button>
                <span class="text-sm font-medium w-12 text-center">{{ fontSize }}px</span>
                <button
                  @click="adjustFontSize('increase')"
                  class="rounded-md bg-gray-100 p-1 text-gray-600 hover:bg-gray-200"
                  :disabled="fontSize >= 24"
                >
                  <PlusIcon class="h-4 w-4" />
                </button>
              </div>
            </div>

            <!-- Alto contraste -->
            <div class="mt-3 flex items-center justify-between">
              <label class="text-sm text-gray-700">Alto contraste</label>
              <button
                @click="toggleHighContrast"
                :class="[
                  'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
                  highContrast ? 'bg-primary-600' : 'bg-gray-200'
                ]"
              >
                <span
                  :class="[
                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                    highContrast ? 'translate-x-5' : 'translate-x-0'
                  ]"
                />
              </button>
            </div>

            <!-- Modo automático día/noche -->
            <div class="mt-3 flex items-center justify-between">
              <label class="text-sm text-gray-700">Modo automático día/noche</label>
              <button
                @click="toggleAutoMode"
                :class="[
                  'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
                  autoMode ? 'bg-primary-600' : 'bg-gray-200'
                ]"
              >
                <span
                  :class="[
                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                    autoMode ? 'translate-x-5' : 'translate-x-0'
                  ]"
                />
              </button>
            </div>
          </div>

          <!-- Información del horario -->
          <div v-if="autoMode" class="mt-3 text-xs text-gray-500">
            <p>Modo diurno: 6:00 - 18:00</p>
            <p>Modo nocturno: 18:00 - 6:00</p>
            <p>Hora actual: {{ currentTime }}</p>
          </div>

          <!-- Botón para restablecer -->
          <div class="mt-4 pt-3 border-t border-gray-200">
            <button
              @click="resetToDefaults"
              class="w-full rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
            >
              Restablecer a valores predeterminados
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import {
  SunIcon,
  MoonIcon,
  AdjustmentsHorizontalIcon,
  CheckIcon,
  PlusIcon,
  MinusIcon
} from '@heroicons/vue/24/outline'

interface Theme {
  id: number | string
  nombre: string
  descripcion?: string
  color_primario: string
  color_secundario?: string
  color_fondo?: string
  color_texto?: string
  tamano_fuente_base?: number
  alto_contraste?: boolean
  target_edad?: string
  modo_oscuro?: boolean
  activo?: boolean
}

const isOpen = ref(false)
const availableThemes = ref<Theme[]>([])
const currentTheme = ref<Theme | null>(null)
const fontSize = ref(16)
const highContrast = ref(false)
const autoMode = ref(false)
const currentTime = ref('')

// Icono del tema actual
const currentThemeIcon = computed(() => {
  if (!currentTheme.value) return AdjustmentsHorizontalIcon

  if (autoMode.value) {
    const hour = new Date().getHours()
    return (hour >= 6 && hour < 18) ? SunIcon : MoonIcon
  }

  return currentTheme.value.modo_oscuro ? MoonIcon : SunIcon
})

// Cargar temas disponibles desde la API
const loadThemes = async () => {
  try {
    const response = await fetch('/api/temas/disponibles', {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    console.log('Temas cargados:', data) // Debug
    availableThemes.value = data.themes || []
  } catch (error) {
    console.error('Error cargando temas:', error)
    // Fallback a temas básicos si falla la API
    availableThemes.value = [
      {
        id: 1,
        nombre: 'Claro',
        descripcion: 'Tema claro para uso diurno',
        color_primario: '#3b82f6',
        color_secundario: '#6b7280',
        color_fondo: '#ffffff',
        color_texto: '#111827',
        tamano_fuente_base: 16,
        alto_contraste: false,
        target_edad: 'adultos',
        modo_oscuro: false,
        activo: true
      },
      {
        id: 2,
        nombre: 'Oscuro',
        descripcion: 'Tema oscuro para uso nocturno',
        color_primario: '#3b82f6',
        color_secundario: '#6b7280',
        color_fondo: '#1f2937',
        color_texto: '#f9fafb',
        tamano_fuente_base: 16,
        alto_contraste: false,
        target_edad: 'adultos',
        modo_oscuro: true,
        activo: true
      }
    ]
  }
}

// Cargar configuración del usuario desde la API
const loadUserConfig = async () => {
  try {
    const response = await fetch('/api/configuracion/usuario', {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json'
      }
    })

    if (!response.ok) {
      // Si no está autenticado o hay error, usar configuración por defecto
      console.log('Usuario no autenticado o error en API, usando configuración por defecto')
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    console.log('Configuración usuario cargada:', data) // Debug

    // Buscar el tema correspondiente
    const tema = availableThemes.value.find(t => t.id === data.tema_id) || availableThemes.value[0]

    currentTheme.value = tema
    fontSize.value = data.tamano_fuente || 16
    highContrast.value = data.alto_contraste || false
    autoMode.value = data.modo_automatico || false

  } catch (error) {
    console.error('Error cargando configuración:', error)
    // Usar configuración por defecto
    currentTheme.value = availableThemes.value[0] || null
    fontSize.value = 16
    highContrast.value = false
    autoMode.value = false
  }
}

// Seleccionar tema
const selectTheme = async (theme: Theme) => {
  currentTheme.value = theme
  await saveUserConfig()
  applyTheme()
}

// Ajustar tamaño de fuente
const adjustFontSize = async (action: 'increase' | 'decrease') => {
  if (action === 'increase' && fontSize.value < 24) {
    fontSize.value += 2
  } else if (action === 'decrease' && fontSize.value > 12) {
    fontSize.value -= 2
  }

  await saveUserConfig()
  applyFontSize()
}

// Toggle alto contraste
const toggleHighContrast = async () => {
  highContrast.value = !highContrast.value
  await saveUserConfig()
  applyTheme()
}

// Toggle modo automático
const toggleAutoMode = async () => {
  autoMode.value = !autoMode.value
  await saveUserConfig()

  if (autoMode.value) {
    applyAutoTheme()
    startAutoThemeTimer()
  } else {
    stopAutoThemeTimer()
    applyTheme()
  }
}

// Aplicar tema
const applyTheme = () => {
  if (!currentTheme.value) return

  const root = document.documentElement
  let theme = currentTheme.value

  // Si modo automático está activo, elegir tema según la hora
  if (autoMode.value) {
    const hour = new Date().getHours()
    const isDayTime = hour >= 6 && hour < 18

    // Buscar tema apropiado según la hora
    const targetTheme = availableThemes.value.find(t =>
      isDayTime ? !t.modo_oscuro : t.modo_oscuro
    )
    if (targetTheme) theme = targetTheme
  }

  // Aplicar colores del tema
  root.style.setProperty('--color-primary', theme.color_primario || '#3b82f6')
  root.style.setProperty('--color-secondary', theme.color_secundario || '#6b7280')
  root.style.setProperty('--color-background', theme.color_fondo || '#ffffff')
  root.style.setProperty('--color-text', theme.color_texto || '#111827')

  // Aplicar alto contraste si está activado
  if (highContrast.value) {
    root.style.setProperty('--color-background', '#FFFFFF')
    root.style.setProperty('--color-text', '#000000')
    root.classList.add('high-contrast')
  } else {
    root.classList.remove('high-contrast')
  }

  // Aplicar clase de tema oscuro/claro
  if (theme.modo_oscuro) {
    root.classList.add('dark')
    root.classList.remove('light')
  } else {
    root.classList.add('light')
    root.classList.remove('dark')
  }
}

// Aplicar tamaño de fuente
const applyFontSize = () => {
  document.documentElement.style.setProperty('--font-size-base', `${fontSize.value}px`)
}

// Aplicar tema automático
const applyAutoTheme = () => {
  if (!autoMode.value) return
  applyTheme()
}

// Timer para modo automático
let autoThemeTimer: number | null = null

const startAutoThemeTimer = () => {
  // Verificar cada minuto si cambió la hora
  autoThemeTimer = setInterval(() => {
    applyAutoTheme()
    updateCurrentTime()
  }, 60000)
}

const stopAutoThemeTimer = () => {
  if (autoThemeTimer) {
    clearInterval(autoThemeTimer)
    autoThemeTimer = null
  }
}

// Actualizar hora actual
const updateCurrentTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Guardar configuración del usuario
const saveUserConfig = async () => {
  try {
    const response = await fetch('/api/configuracion/usuario', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        tema_id: currentTheme.value?.id,
        tamano_fuente: fontSize.value,
        alto_contraste: highContrast.value,
        modo_automatico: autoMode.value
      })
    })

    if (!response.ok) {
      console.log('No se pudo guardar la configuración (posiblemente no autenticado)')
      // Si no está autenticado, solo aplicar localmente
      return
    }

    console.log('Configuración guardada correctamente')
  } catch (error) {
    console.error('Error guardando configuración:', error)
    // Aplicar localmente aunque no se pueda guardar en servidor
  }
}

// Restablecer a valores predeterminados
const resetToDefaults = async () => {
  const defaultTheme = availableThemes.value.find(t => t.target_edad === 'adultos' && !t.modo_oscuro)
  if (defaultTheme) {
    currentTheme.value = defaultTheme
  }
  fontSize.value = 16
  highContrast.value = false
  autoMode.value = false

  await saveUserConfig()
  applyTheme()
  applyFontSize()
  stopAutoThemeTimer()
}

// Cerrar panel al hacer clic fuera
const handleClickOutside = (event: Event) => {
  const target = event.target as HTMLElement
  if (!target.closest('.relative')) {
    isOpen.value = false
  }
}

onMounted(() => {
  loadThemes()
  loadUserConfig()
  updateCurrentTime()

  // Actualizar hora cada minuto
  const timeInterval = setInterval(updateCurrentTime, 60000)

  // Listener para cerrar panel
  document.addEventListener('click', handleClickOutside)

  onUnmounted(() => {
    clearInterval(timeInterval)
    stopAutoThemeTimer()
    document.removeEventListener('click', handleClickOutside)
  })
})
</script>

<style>
/* Variables CSS personalizadas para temas */
:root {
  --color-primary: #374BFF;
  --color-secondary: #6B7280;
  --color-background: #FFFFFF;
  --color-text: #111827;
  --font-size-base: 16px;
}

/* Aplicar tamaño de fuente base */
html {
  font-size: var(--font-size-base);
}

/* Estilos para alto contraste */
.high-contrast {
  filter: contrast(150%);
}

.high-contrast * {
  border-color: #000000 !important;
}
</style>
