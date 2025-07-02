<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-30 w-64 bg-white dark:bg-gray-800 shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

      <!-- Logo/Header -->
      <div class="flex items-center justify-center h-20 shadow-md bg-blue-600 dark:bg-blue-700">
        <div class="flex items-center">
          <svg class="w-8 h-8 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
          <span class="text-xl font-bold text-white">CICIT Admin</span>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="mt-8">
        <div class="px-4 space-y-2">

          <!-- Dashboard -->
          <Link
            href="/admin"
            class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            :class="{ 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300': $page.component === 'Admin/Dashboard' }"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
            </svg>
            Dashboard
          </Link>

          <!-- Preinscripciones -->
          <div class="space-y-1">
            <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Gestión de Inscripciones
            </div>

            <Link
              href="/admin/preinscripciones"
              class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              :class="{ 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300': $page.component.startsWith('Admin/Preinscripciones') }"
            >
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              Preinscripciones
              <span v-if="pendingCount > 0" class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                {{ pendingCount }}
              </span>
            </Link>

            <Link
              href="/admin/inscripciones"
              class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              :class="{ 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300': $page.component.startsWith('Admin/Inscripciones') }"
            >
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
              </svg>
              Inscripciones
            </Link>

            <Link
              href="/admin/pagos"
              class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              :class="{ 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300': $page.component.startsWith('Admin/Pagos') }"
            >
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
              Pagos
            </Link>
          </div>

          <!-- Cursos -->
          <div class="space-y-1">
            <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Gestión de Cursos
            </div>

            <Link
              href="/admin/cursos"
              class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              :class="{ 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300': $page.component.startsWith('Admin/Cursos') }"
            >
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
              Cursos
            </Link>

            <Link
              href="/admin/participantes"
              class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              :class="{ 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300': $page.component.startsWith('Admin/Participantes') }"
            >
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
              Participantes
            </Link>
          </div>

          <!-- Reportes -->
          <div class="space-y-1">
            <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Reportes
            </div>

            <Link
              href="/admin/reportes"
              class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              :class="{ 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300': $page.component.startsWith('Admin/Reportes') }"
            >
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
              Reportes
            </Link>
          </div>

        </div>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="lg:ml-64">
      <!-- Top Header -->
      <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center py-4">

            <!-- Mobile menu button -->
            <div class="flex items-center lg:hidden">
              <button
                @click="sidebarOpen = !sidebarOpen"
                class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
              </button>
            </div>

            <!-- Page Title -->
            <div class="flex-1 min-w-0 lg:ml-0 ml-4">
              <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ pageTitle }}
              </h1>
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">

              <!-- Notifications -->
              <button class="p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zM15 17l-2-14-2 14m0 0h4"></path>
                </svg>
              </button>

              <!-- User Dropdown -->
              <div class="relative">
                <button
                  @click="userMenuOpen = !userMenuOpen"
                  class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                    <span class="text-sm font-medium text-white">
                      {{ $page.props.auth.user?.nombre?.charAt(0) || 'A' }}
                    </span>
                  </div>
                  <div class="text-left">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ $page.props.auth.user?.nombre || 'Administrativo' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Administrativo</p>
                  </div>
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>

                <!-- Dropdown Menu -->
                <div
                  v-show="userMenuOpen"
                  @click="userMenuOpen = false"
                  class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 border border-gray-200 dark:border-gray-700"
                >
                  <Link
                    href="/admin/perfil"
                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                  >
                    Mi Perfil
                  </Link>
                  <Link
                    href="/admin/configuracion"
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

      <!-- Page Content -->
      <main class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <slot />
        </div>
      </main>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div
      v-show="sidebarOpen"
      @click="sidebarOpen = false"
      class="fixed inset-0 z-20 bg-black opacity-50 lg:hidden"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

// Props
interface Props {
  pendingCount?: number
  pageTitle?: string
}

withDefaults(defineProps<Props>(), {
  pendingCount: 0,
  pageTitle: 'Dashboard'
})

// Reactive data
const sidebarOpen = ref(false)
const userMenuOpen = ref(false)
</script>
