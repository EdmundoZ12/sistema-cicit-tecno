<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
              <Link :href="route('tutor.dashboard')" class="text-xl font-bold text-blue-600 dark:text-blue-400">
                Centro de Capacitación - Tutor
              </Link>
            </div>

            <!-- Navigation Links -->
            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
              <NavLink :href="route('tutor.dashboard')" :active="route().current('tutor.dashboard')">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6a2 2 0 01-2 2H10a2 2 0 01-2-2V5z"></path>
                </svg>
                Dashboard
              </NavLink>

              <NavLink :href="route('tutor.mis-cursos.index')" :active="route().current('tutor.mis-cursos.*')">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                Mis Cursos
              </NavLink>

              <NavLink :href="route('tutor.asistencias.index')" :active="route().current('tutor.asistencias.*')">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                Asistencias
              </NavLink>

              <NavLink :href="route('tutor.tareas.index')" :active="route().current('tutor.tareas.*')">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Tareas
              </NavLink>

              <NavLink :href="route('tutor.notas.index')" :active="route().current('tutor.notas.*')">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                Calificaciones
              </NavLink>
            </div>
          </div>

          <div class="hidden sm:flex sm:items-center sm:ml-6">
            <!-- Pending Tasks Badge -->
            <div v-if="pendingCount > 0" class="mr-4">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                {{ pendingCount }} pendientes
              </span>
            </div>

            <!-- Settings Dropdown -->
            <div class="ml-3 relative">
              <Dropdown align="right" width="48">
                <template #trigger>
                  <span class="inline-flex rounded-md">
                    <button
                      type="button"
                      class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150"
                    >
                      {{ $page.props.auth.user.nombre }} {{ $page.props.auth.user.apellido }}
                      <svg
                        class="ml-2 -mr-0.5 h-4 w-4"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>
                  </span>
                </template>

                <template #content>
                  <DropdownLink :href="route('profile.edit')">Perfil</DropdownLink>
                  <DropdownLink :href="route('logout')" method="post" as="button">
                    Cerrar Sesión
                  </DropdownLink>
                </template>
              </Dropdown>
            </div>
          </div>

          <!-- Mobile menu button -->
          <div class="-mr-2 flex items-center sm:hidden">
            <button
              @click="showingNavigationDropdown = !showingNavigationDropdown"
              class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out"
            >
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path
                  :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"
                />
                <path
                  :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Navigation Menu -->
      <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <ResponsiveNavLink :href="route('tutor.dashboard')" :active="route().current('tutor.dashboard')">
            Dashboard
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('tutor.mis-cursos.index')" :active="route().current('tutor.mis-cursos.*')">
            Mis Cursos
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('tutor.asistencias.index')" :active="route().current('tutor.asistencias.*')">
            Asistencias
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('tutor.tareas.index')" :active="route().current('tutor.tareas.*')">
            Tareas
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('tutor.notas.index')" :active="route().current('tutor.notas.*')">
            Calificaciones
          </ResponsiveNavLink>
        </div>

        <!-- Mobile User Menu -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
          <div class="px-4">
            <div class="font-medium text-base text-gray-800 dark:text-gray-200">
              {{ $page.props.auth.user.nombre }} {{ $page.props.auth.user.apellido }}
            </div>
            <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
          </div>

          <div class="mt-3 space-y-1">
            <ResponsiveNavLink :href="route('profile.edit')">Perfil</ResponsiveNavLink>
            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
              Cerrar Sesión
            </ResponsiveNavLink>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Heading -->
    <header v-if="pageTitle" class="bg-white dark:bg-gray-800 shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          {{ pageTitle }}
        </h1>
      </div>
    </header>

    <!-- Page Content -->
    <main>
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <slot />
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import Dropdown from '@/components/Dropdown.vue'
import DropdownLink from '@/components/DropdownLink.vue'
import NavLink from '@/components/NavLink.vue'
import ResponsiveNavLink from '@/components/ResponsiveNavLink.vue'

interface Props {
  pageTitle?: string
  pendingCount?: number
}

defineProps<Props>()

const showingNavigationDropdown = ref(false)
</script>
