<template>
  <aside
    :class="[
      'bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 ease-in-out z-30 h-full overflow-y-auto flex flex-col',
      collapsed ? 'w-16' : 'w-56'
    ]"
  >
    <!-- Header del sidebar -->
    <div class="flex h-14 items-center justify-between px-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex-shrink-0">
      <div v-if="!collapsed" class="flex items-center space-x-2 min-w-0">
        <div class="h-7 w-7 rounded bg-primary-600 flex items-center justify-center flex-shrink-0">
          <span class="text-xs font-bold text-white">{{ user?.rol?.charAt(0) }}</span>
        </div>
        <div class="min-w-0">
          <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ user?.rol }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ user?.nombre }}</p>
        </div>
      </div>

      <!-- Botón para colapsar -->
      <button
        @click="$emit('toggle')"
        class="rounded-md p-1.5 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 flex-shrink-0"
      >
        <ChevronLeftIcon v-if="!collapsed" class="h-4 w-4" />
        <ChevronRightIcon v-else class="h-4 w-4" />
      </button>
    </div>

    <!-- Navegación principal -->
    <nav class="flex-1 py-4 px-2 space-y-1 overflow-y-auto">
      <!-- Dashboard -->
      <SidebarItem
        :item="dashboardItem"
        :collapsed="collapsed"
        :is-active="$page.url === dashboardItem.ruta"
      />

      <!-- ADMINISTRACIÓN -->
      <div class="mt-6">
        <div v-if="!collapsed" class="px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
          ADMINISTRACIÓN
        </div>
        <div class="space-y-1">
          <SidebarItem :item="adminItems[0]" :collapsed="collapsed" :is-active="isActiveItem(adminItems[0])" />
          <SidebarItem :item="adminItems[1]" :collapsed="collapsed" :is-active="isActiveItem(adminItems[1])" />
          <SidebarItem :item="adminItems[2]" :collapsed="collapsed" :is-active="isActiveItem(adminItems[2])" />
          <SidebarItem :item="adminItems[3]" :collapsed="collapsed" :is-active="isActiveItem(adminItems[3])" />
        </div>
      </div>
      <!-- SISTEMA -->
      <div class="mt-6">
        <div v-if="!collapsed" class="px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
          SISTEMA
        </div>
        <div class="space-y-1">
          <SidebarItem :item="systemItems[0]" :collapsed="collapsed" :is-active="isActiveItem(systemItems[0])" />
          <SidebarItem :item="systemItems[1]" :collapsed="collapsed" :is-active="isActiveItem(systemItems[1])" />
          <SidebarItem :item="systemItems[2]" :collapsed="collapsed" :is-active="isActiveItem(systemItems[2])" />
          <SidebarItem :item="systemItems[3]" :collapsed="collapsed" :is-active="isActiveItem(systemItems[3])" />
        </div>
      </div>
      <!-- CERTIFICACIÓN -->
      <div class="mt-6">
        <div v-if="!collapsed" class="px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
          CERTIFICACIÓN
        </div>
        <div class="space-y-1">
          <SidebarItem :item="certItems[0]" :collapsed="collapsed" :is-active="isActiveItem(certItems[0])" />
        </div>
      </div>
    </nav>

    <!-- Footer del sidebar -->
    <div class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex-shrink-0">
      <!-- Perfil de usuario -->
      <div class="p-2">
        <SidebarItem
          :item="profileItem"
          :collapsed="collapsed"
          :is-active="$page.url === '/profile/edit'"
        />
      </div>

      <!-- Indicador de estado -->
      <div class="flex items-center justify-center space-x-2 text-xs text-gray-500 dark:text-gray-400 p-2">
        <div class="h-2 w-2 rounded-full bg-green-400"></div>
        <span v-if="!collapsed">Sistema Activo</span>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import SidebarItem from '@/components/CICIT/SidebarItem.vue'
import type { MenuItem, ComponentUser } from '@/types'

interface Props {
  menuItems: MenuItem[]
  user?: ComponentUser
  collapsed: boolean
  open: boolean
}

const props = defineProps<Props>()
defineEmits<{
  toggle: []
}>()

const page = usePage()

// Dashboard principal según el rol
const dashboardItem = computed((): MenuItem => ({
  id: 0,
  nombre: 'Dashboard',
  titulo: 'Dashboard',
  ruta: getDashboardRoute(),
  icono: 'home',
  orden: 0,
  es_activo: true,
  activo: true,
  roles_permitidos: props.user?.rol || '',
  descripcion: 'Panel principal de administración'
}))

// Item de perfil de usuario
const profileItem = computed((): MenuItem => ({
  id: -1,
  nombre: 'Mi Perfil',
  titulo: 'Mi Perfil',
  ruta: '/profile/edit',
  icono: 'user',
  orden: -1,
  es_activo: true,
  activo: true,
  roles_permitidos: props.user?.rol || '',
  descripcion: 'Editar perfil y configuración'
}))

// Ítems fijos para RESPONSABLE
const adminItems = [
  {
    id: 101,
    nombre: 'Gestión de Usuarios',
    titulo: 'Gestión de Usuarios',
    ruta: '/usuarios',
    icono: 'users',
    orden: 1,
    es_activo: true,
    activo: true,
    roles_permitidos: 'RESPONSABLE',
    descripcion: 'Administrar usuarios del sistema',
    action_type: 'content',
    content_key: 'usuarios'
  },
  {
    id: 104,
    nombre: 'Gestiones Académicas',
    titulo: 'Gestiones Académicas',
    ruta: '/dashboard',
    icono: 'calendar',
    orden: 2,
    es_activo: true,
    activo: true,
    roles_permitidos: 'RESPONSABLE',
    descripcion: 'Administrar gestiones académicas',
    action_type: 'content',
    content_key: 'gestiones'
  },
  {
    id: 102,
    nombre: 'Tipos de Participante',
    titulo: 'Tipos de Participante',
    ruta: '/tipos-participante',
    icono: 'user-group',
    orden: 3,
    es_activo: true,
    activo: true,
    roles_permitidos: 'RESPONSABLE',
    descripcion: 'Configurar tipos de participantes',
    action_type: 'content',
    content_key: 'tipos-participante'
  },
  {
    id: 103,
    nombre: 'Gestión de Cursos',
    titulo: 'Gestión de Cursos',
    ruta: '/cursos',
    icono: 'book-open',
    orden: 4,
    es_activo: true,
    activo: true,
    roles_permitidos: 'RESPONSABLE',
    descripcion: 'Administrar cursos y contenido',
    action_type: 'content',
    content_key: 'cursos'
  }
]
const systemItems = [
  {
    id: 201,
    nombre: 'Reportes',
    titulo: 'Reportes',
    ruta: '/reportes',
    icono: 'chart-bar',
    orden: 1,
    es_activo: true,
    activo: true,
    roles_permitidos: 'RESPONSABLE',
    descripcion: 'Generar reportes del sistema',
    action_type: 'content',
    content_key: 'reportes'
  },
  {
    id: 202,
    nombre: 'Estadísticas',
    titulo: 'Estadísticas',
    ruta: '/estadisticas',
    icono: 'trending-up',
    orden: 2,
    es_activo: true,
    activo: true,
    roles_permitidos: 'RESPONSABLE',
    descripcion: 'Ver estadísticas avanzadas',
    action_type: 'content',
    content_key: 'estadisticas'
  },
  {
    id: 203,
    nombre: 'Gestión de Pagos',
    titulo: 'Gestión de Pagos',
    ruta: '/pagos',
    icono: 'credit-card',
    orden: 3,
    es_activo: true,
    activo: true,
    roles_permitidos: 'RESPONSABLE',
    descripcion: 'Administrar pagos e ingresos',
    action_type: 'content',
    content_key: 'pagos'
  },
  {
    id: 204,
    nombre: 'Configuración',
    titulo: 'Configuración',
    ruta: '/configuracion',
    icono: 'cog',
    orden: 4,
    es_activo: true,
    activo: true,
    roles_permitidos: 'RESPONSABLE',
    descripcion: 'Configuración del sistema',
    action_type: 'navigation'
  }
]
const certItems = [
  {
    id: 301,
    nombre: 'Certificados',
    titulo: 'Certificados',
    ruta: '/certificados',
    icono: 'academic-cap',
    orden: 1,
    es_activo: true,
    activo: true,
    roles_permitidos: 'RESPONSABLE',
    descripcion: 'Gestión de certificados',
    action_type: 'content',
    content_key: 'certificados'
  }
]

function getDashboardRoute(): string {
  switch (props.user?.rol) {
    case 'RESPONSABLE':
      return '/dashboard/responsable'
    case 'ADMINISTRATIVO':
      return '/dashboard/administrativo'
    case 'TUTOR':
      return '/dashboard/tutor'
    default:
      return '/dashboard'
  }
}

function isActiveItem(item: MenuItem): boolean {
  return page.url === item.ruta || page.url.startsWith(item.ruta + '/')
}
</script>
