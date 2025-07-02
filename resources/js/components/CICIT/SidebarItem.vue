<template>
  <component
    :is="item.action_type === 'content' ? 'button' : Link"
    :href="item.action_type === 'content' ? undefined : item.ruta"
    @click="item.action_type === 'content' ? handleContentClick() : undefined"
    :class="[
      'group flex items-center rounded-md px-2 py-2 text-sm font-medium transition-colors duration-200 w-full text-left',
      isActive
        ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 border-r-2 border-primary-500'
        : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100',
      collapsed ? 'justify-center' : ''
    ]"
  >
    <!-- Icono -->
    <component
      :is="iconComponent"
      :class="[
        'flex-shrink-0 h-5 w-5',
        isActive ? 'text-primary-500 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400',
        collapsed ? '' : 'mr-3'
      ]"
    />

    <!-- Título del menú -->
    <span v-if="!collapsed" class="truncate">
      {{ item.titulo || item.nombre }}
    </span>

    <!-- Badge para notificaciones (opcional) -->
    <span
      v-if="!collapsed && badge && badge > 0"
      class="ml-auto inline-block py-0.5 px-2 text-xs font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300"
    >
      {{ badge }}
    </span>

    <!-- Tooltip para modo colapsado -->
    <div
      v-if="collapsed"
      class="absolute left-16 invisible group-hover:visible bg-gray-900 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 whitespace-nowrap z-10"
    >
      {{ item.titulo || item.nombre }}
    </div>
  </component>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import {
  HomeIcon,
  UsersIcon,
  CalendarIcon,
  AcademicCapIcon,
  DocumentTextIcon,
  CreditCardIcon,
  ChartBarIcon,
  CogIcon,
  ClipboardDocumentListIcon,
  ClipboardDocumentCheckIcon,
  BookOpenIcon,
  PencilIcon,
  UserGroupIcon
} from '@heroicons/vue/24/outline'
import type { MenuItem } from '@/types'

interface Props {
  item: MenuItem
  collapsed: boolean
  isActive: boolean
  badge?: number
}

const props = defineProps<Props>()

// Mapeo de iconos
const iconMap = {
  home: HomeIcon,
  users: UsersIcon,
  'user-group': UserGroupIcon,
  calendar: CalendarIcon,
  'academic-cap': AcademicCapIcon,
  'document-text': DocumentTextIcon,
  'credit-card': CreditCardIcon,
  'chart-bar': ChartBarIcon,
  cog: CogIcon,
  'clipboard-list': ClipboardDocumentListIcon,
  'clipboard-check': ClipboardDocumentCheckIcon,
  'book-open': BookOpenIcon,
  'pencil-alt': PencilIcon,
  certificate: AcademicCapIcon, // Fallback para certificados
  settings: CogIcon,
  'trending-up': ChartBarIcon
}

// Componente de icono dinámico
const iconComponent = computed(() => {
  const iconName = props.item.icono as keyof typeof iconMap
  return iconMap[iconName] || HomeIcon // Fallback al icono home
})

// Función para manejar contenido dinámico
const handleContentClick = () => {
  if (props.item.content_key && window.setDashboardContent) {
    window.setDashboardContent(props.item.content_key, props.item.titulo || props.item.nombre)
  }
}
</script>
