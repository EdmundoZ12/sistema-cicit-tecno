<template>
  <header class="bg-white border-b border-gray-200 shadow-sm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between">
        <!-- Logo y nombre CICIT -->
        <div class="flex items-center">
          <!-- Botón menú móvil -->
          <button
            @click="$emit('toggle-sidebar')"
            class="mr-3 rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 md:hidden"
          >
            <Bars3Icon class="h-6 w-6" />
          </button>

          <Link :href="route('home')" class="flex items-center space-x-3">
            <!-- Logo CICIT -->
            <div class="flex-shrink-0">
              <img
                class="h-10 w-auto"
                src="/images/logos/cicit-logo.svg"
                alt="CICIT - Centro Integral de Certificación e Innovación Tecnológica"
                @error="handleLogoError"
              >
            </div>
            <!-- Nombre del centro -->
            <div class="hidden md:block">
              <h1 class="text-xl font-bold text-primary-600">CICIT</h1>
              <p class="text-xs text-gray-500">Centro de Certificación e Innovación</p>
            </div>
          </Link>
        </div>

        <!-- Barra de búsqueda central -->
        <div v-if="searchEnabled" class="flex-1 max-w-md mx-8">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
            </div>
            <input
              v-model="searchTerm"
              @keyup.enter="performSearch"
              @input="onSearchInput"
              type="search"
              placeholder="Buscar cursos, certificados, participantes..."
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            >
            <!-- Resultados de búsqueda en tiempo real -->
            <div v-if="searchResults.length > 0" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
              <div
                v-for="result in searchResults"
                :key="result.id"
                @click="selectSearchResult(result)"
                class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-50"
              >
                <div class="flex items-center">
                  <span class="font-medium">{{ result.title }}</span>
                  <span class="ml-2 text-sm text-gray-500">{{ result.type }}</span>
                </div>
                <p class="text-sm text-gray-500 truncate">{{ result.description }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Navegación derecha -->
        <div class="flex items-center space-x-4">
          <!-- Notificaciones -->
          <div class="relative">
            <button
              type="button"
              class="relative rounded-full bg-white p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
              @click="toggleNotifications"
            >
              <BellIcon class="h-6 w-6" />
              <span v-if="unreadNotifications > 0" class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-red-500 text-xs text-white flex items-center justify-center">
                {{ unreadNotifications }}
              </span>
            </button>
          </div>

          <!-- Selector de tema -->
          <ThemeSelector />

          <!-- Menú de usuario -->
          <div class="relative ml-3">
            <button
              @click="toggleUserMenu"
              class="flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 hover:bg-gray-50 p-2 transition-colors"
            >
              <span class="sr-only">Usuario</span>
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                  <div class="h-8 w-8 rounded-full bg-primary-600 flex items-center justify-center">
                    <span class="text-sm font-medium text-white">
                      {{ getUserInitials(user?.nombre || user?.name, user?.apellido) }}
                    </span>
                  </div>
                </div>
                <div class="hidden md:block text-left">
                  <p class="text-sm font-medium text-gray-900">{{ user?.nombre || user?.name || 'Usuario' }} {{ user?.apellido || '' }}</p>
                  <p class="text-xs text-gray-500">{{ user?.rol || 'Sin rol' }}</p>
                </div>
                <ChevronDownIcon class="h-4 w-4 text-gray-400" />
              </div>
            </button>

            <!-- Dropdown Menu -->
            <div
              v-show="showUserMenu"
              @click="showUserMenu = false"
              class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 border border-gray-200 dark:border-gray-700"
            >
              <Link
                :href="route('admin.dashboard')"
                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                Mi Perfil
              </Link>
              <Link
                :href="route('configuracion.index')"
                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                Configuración
              </Link>
              <hr class="border-gray-200 dark:border-gray-700">
              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                Cerrar Sesión
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
// import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import {
  MagnifyingGlassIcon,
  BellIcon,
  ChevronDownIcon,
  Bars3Icon
} from '@heroicons/vue/24/outline'
import ThemeSelector from '@/components/CICIT/ThemeSelector.vue'

import type { ComponentUser } from '@/types'

interface SearchResult {
  id: number
  title: string
  type: string
  description: string
}

interface Props {
  user?: ComponentUser
  searchEnabled?: boolean
}

withDefaults(defineProps<Props>(), {
  searchEnabled: true
})

const emit = defineEmits<{
  search: [searchTerm: string]
  'toggle-sidebar': []
}>()

const searchTerm = ref('')
const searchResults = ref<SearchResult[]>([])
const unreadNotifications = ref(0)
const showNotifications = ref(false)
const showUserMenu = ref(false)

// Búsqueda en tiempo real
let searchTimeout: number
const onSearchInput = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(async () => {
    if (searchTerm.value.trim().length >= 2) {
      await performLiveSearch()
    } else {
      searchResults.value = []
    }
  }, 300)
}

const performLiveSearch = async () => {
  // Simulación de resultados de búsqueda (sin API)
  if (searchTerm.value.trim().length >= 2) {
    searchResults.value = [
      {
        id: 1,
        title: `Resultados para: ${searchTerm.value}`,
        type: 'Curso',
        description: 'Búsqueda en desarrollo'
      }
    ]
  } else {
    searchResults.value = []
  }
}

const performSearch = () => {
  if (searchTerm.value.trim()) {
    emit('search', searchTerm.value)
    searchResults.value = []
  }
}

const selectSearchResult = (result: SearchResult) => {
  // Por ahora solo limpiamos la búsqueda
  searchResults.value = []
  searchTerm.value = result.title
  emit('search', result.title)
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
}

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value
}

const getUserInitials = (nombre?: string, apellido?: string) => {
  if (!nombre || !apellido) return 'U'
  return `${nombre.charAt(0)}${apellido.charAt(0)}`.toUpperCase()
}

const handleLogoError = (event: Event) => {
  // Si falla cargar el logo, usar un placeholder
  const target = event.target as HTMLImageElement
  target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iOCIgZmlsbD0iIzM3NEJGRiIvPgo8dGV4dCB4PSIyMCIgeT0iMjYiIGZvbnQtZmFtaWx5PSJzYW5zLXNlcmlmIiBmb250LXNpemU9IjE0IiBmb250LXdlaWdodD0iYm9sZCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiPkM8L3RleHQ+Cjwvc3ZnPgo='
}

// Obtener notificaciones no leídas (simplificado)
const fetchUnreadNotifications = async () => {
  // Por ahora, simplemente establecer en 0
  unreadNotifications.value = 0
}

// Cargar notificaciones al montar el componente
fetchUnreadNotifications()
</script>
