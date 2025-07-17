<template>
  <div class="space-y-4" :class="themeClasses">
    <!-- Header sin botón -->
    <div class="flex justify-between items-start">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Gestión de Usuarios</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Administrar usuarios del sistema CICIT</p>
      </div>
    </div>

    <!-- Filtros -->
    <Card class="border border-gray-200 dark:border-gray-700">
      <CardContent class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Buscar
            </label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nombre, registro o carnet..."
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm dark:bg-gray-800 dark:text-white"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Tipo
            </label>
            <select
              v-model="filters.rol"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm dark:bg-gray-800 dark:text-white"
            >
              <option value="">Todos</option>
              <option v-for="rol in roles" :key="rol" :value="rol">
                {{ rol }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Estado
            </label>
            <select
              v-model="filters.activo"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm dark:bg-gray-800 dark:text-white"
            >
              <option value="">Todos</option>
              <option value="1">Activos</option>
              <option value="0">Inactivos</option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="resetFilters"
              class="w-full inline-flex items-center justify-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
            >
              <RotateCcw class="w-4 h-4 mr-2" />
              Limpiar
            </button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <Card class="border border-gray-200 dark:border-gray-700">
        <CardContent class="p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <Users class="h-8 w-8 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Usuarios</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.total }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="border border-gray-200 dark:border-gray-700">
        <CardContent class="p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <UserCheck class="h-8 w-8 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Activos</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.activos }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="border border-gray-200 dark:border-gray-700">
        <CardContent class="p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <GraduationCap class="h-8 w-8 text-purple-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Responsables</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.responsables }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="border border-gray-200 dark:border-gray-700">
        <CardContent class="p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <Activity class="h-8 w-8 text-orange-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nuevos (mes)</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.nuevos_mes }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Tabla de usuarios -->
    <Card class="border border-gray-200 dark:border-gray-700">
      <CardHeader class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
        <CardTitle class="text-lg font-medium text-gray-900 dark:text-white">
          Lista de Usuarios
        </CardTitle>
        <CardDescription class="text-sm text-gray-500 dark:text-gray-400">
          {{ filteredUsers.length }} usuarios encontrados
        </CardDescription>
      </CardHeader>
      <CardContent class="p-0">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Usuario
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Información
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Rol
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Estado
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Último acceso
                </th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="usuario in paginatedUsers" :key="usuario.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-primary-600 flex items-center justify-center">
                        <span class="text-sm font-medium text-white">
                          {{ getInitials(usuario.nombre, usuario.apellido) }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ usuario.nombre }} {{ usuario.apellido }}
                      </div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ usuario.registro }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">{{ usuario.carnet }}</div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">{{ usuario.email }}</div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <Badge :variant="getRolVariant(usuario.rol)">
                    {{ usuario.rol }}
                  </Badge>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <Badge :variant="usuario.activo ? 'default' : 'destructive'">
                    {{ usuario.activo ? 'Activo' : 'Inactivo' }}
                  </Badge>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ formatDate(usuario.ultimo_acceso) }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end space-x-2">
                    <button
                      @click="editUser(usuario)"
                      class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
                      <Edit class="w-4 h-4" />
                    </button>
                    <button
                      @click="toggleUserStatus(usuario)"
                      class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300"
                    >
                      <Power class="w-4 h-4" />
                    </button>
                    <button
                      @click="deleteUser(usuario)"
                      class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>

    <!-- Paginación -->
    <div class="flex items-center justify-between">
      <div class="text-sm text-gray-700 dark:text-gray-300">
        Mostrando {{ (currentPage - 1) * itemsPerPage + 1 }} a {{ Math.min(currentPage * itemsPerPage, filteredUsers.length) }} de {{ filteredUsers.length }} resultados
      </div>
      <div class="flex space-x-2">
        <button
          @click="currentPage--"
          :disabled="currentPage === 1"
          class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-white"
        >
          Anterior
        </button>
        <span class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300">
          Página {{ currentPage }} de {{ totalPages }}
        </span>
        <button
          @click="currentPage++"
          :disabled="currentPage === totalPages"
          class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-white"
        >
          Siguiente
        </button>
      </div>
    </div>

    <!-- Modal de Crear/Editar Usuario -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="submitForm">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                    {{ isEditing ? 'Editar Usuario' : 'Crear Usuario' }}
                  </h3>

                  <div class="mt-4 space-y-4">
                    <!-- Registro -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Registro *</label>
                      <input
                        v-model="form.registro"
                        type="text"
                        required
                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': errors.registro }"
                      />
                      <p v-if="errors.registro" class="mt-1 text-sm text-red-600">{{ errors.registro }}</p>
                    </div>

                    <!-- Nombre -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label>
                      <input
                        v-model="form.nombre"
                        type="text"
                        required
                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': errors.nombre }"
                      />
                      <p v-if="errors.nombre" class="mt-1 text-sm text-red-600">{{ errors.nombre }}</p>
                    </div>

                    <!-- Apellido -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Apellido *</label>
                      <input
                        v-model="form.apellido"
                        type="text"
                        required
                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': errors.apellido }"
                      />
                      <p v-if="errors.apellido" class="mt-1 text-sm text-red-600">{{ errors.apellido }}</p>
                    </div>

                    <!-- Carnet -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Carnet/Cédula *</label>
                      <input
                        v-model="form.carnet"
                        type="text"
                        required
                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': errors.carnet }"
                      />
                      <p v-if="errors.carnet" class="mt-1 text-sm text-red-600">{{ errors.carnet }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email *</label>
                      <input
                        v-model="form.email"
                        type="email"
                        required
                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': errors.email }"
                      />
                      <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                    </div>

                    <!-- Teléfono -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                      <input
                        v-model="form.telefono"
                        type="text"
                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': errors.telefono }"
                      />
                      <p v-if="errors.telefono" class="mt-1 text-sm text-red-600">{{ errors.telefono }}</p>
                    </div>

                    <!-- Rol -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rol *</label>
                      <select
                        v-model="form.rol"
                        required
                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': errors.rol }"
                      >
                        <option value="">Seleccionar rol</option>
                        <option value="RESPONSABLE">Responsable</option>
                        <option value="ADMINISTRATIVO">Administrativo</option>
                        <option value="TUTOR">Tutor</option>
                      </select>
                      <p v-if="errors.rol" class="mt-1 text-sm text-red-600">{{ errors.rol }}</p>
                    </div>

                    <!-- Password (solo para crear) -->
                    <div v-if="!isEditing">
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña *</label>
                      <input
                        v-model="form.password"
                        type="password"
                        required
                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': errors.password }"
                      />
                      <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
                    </div>

                    <!-- Estado (solo para editar) -->
                    <div v-if="isEditing" class="flex items-center">
                      <input
                        v-model="form.activo"
                        type="checkbox"
                        id="activo"
                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                      />
                      <label for="activo" class="ml-2 block text-sm text-gray-900 dark:text-white">
                        Usuario activo
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                type="submit"
                :disabled="processing"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="processing" class="mr-2">
                  <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                </span>
                {{ isEditing ? 'Actualizar' : 'Crear' }}
              </button>
              <button
                type="button"
                @click="closeModal"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-700"
              >
                Cancelar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmación -->
    <div v-if="showConfirmModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeConfirmModal"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <AlertTriangle class="h-6 w-6 text-red-600" />
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                  {{ confirmAction.title }}
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ confirmAction.message }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="executeConfirmAction"
              :disabled="processing"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
            >
              <span v-if="processing" class="mr-2">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
              </span>
              Confirmar
            </button>
            <button
              @click="closeConfirmModal"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-700"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Notificaciones -->
    <div v-if="notification.show" :class="[
      'fixed top-4 right-4 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden z-50',
      notification.type === 'success' ? 'border-l-4 border-green-400' : 'border-l-4 border-red-400'
    ]">
      <div class="p-4">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <CheckCircle v-if="notification.type === 'success'" class="h-6 w-6 text-green-400" />
            <XCircle v-else class="h-6 w-6 text-red-400" />
          </div>
          <div class="ml-3 w-0 flex-1 pt-0.5">
            <p class="text-sm font-medium text-gray-900">{{ notification.message }}</p>
          </div>
          <div class="ml-4 flex-shrink-0 flex">
            <button @click="notification.show = false" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
              <X class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import {
  Users,
  UserCheck,
  GraduationCap,
  Activity,
  RotateCcw,
  Edit,
  Power,
  Trash2,
  AlertTriangle,
  CheckCircle,
  XCircle,
  X
} from 'lucide-vue-next';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card/index';
import Badge from '@/components/ui/badge/Badge.vue';

interface Usuario {
  id: number;
  nombre: string;
  apellido: string;
  registro: string;
  carnet: string;
  email: string;
  telefono?: string;
  rol: string;
  activo: boolean;
  ultimo_acceso: string;
  created_at: string;
}

interface UsuariosStats {
  total: number;
  activos: number;
  responsables: number;
  nuevos_mes: number;
}

interface UserForm {
  registro: string;
  nombre: string;
  apellido: string;
  carnet: string;
  email: string;
  telefono: string;
  rol: string;
  activo: boolean;
  password?: string;
}

const props = defineProps<{
  usuarios?: Usuario[];
  userThemeConfig?: {
    tema_id: number;
    tamano_fuente: number;
    alto_contraste: boolean;
  };
}>();

// Configuración del tema
const themeClasses = computed(() => {
  const classes = [];

  if (props.userThemeConfig?.alto_contraste) {
    classes.push('high-contrast');
  }

  // Aplicar tamaño de fuente
  if (props.userThemeConfig?.tamano_fuente) {
    const fontSize = props.userThemeConfig.tamano_fuente;
    if (fontSize === 12) classes.push('text-xs');
    else if (fontSize === 14) classes.push('text-sm');
    else if (fontSize === 16) classes.push('text-base');
    else if (fontSize === 18) classes.push('text-lg');
    else if (fontSize === 20) classes.push('text-xl');
  }

  return classes.join(' ');
});

// Estado del componente
const showModal = ref(false);
const showConfirmModal = ref(false);
const isEditing = ref(false);
const processing = ref(false);
const currentPage = ref(1);
const itemsPerPage = ref(10);

// Datos de usuarios
const usuariosData = ref<Usuario[]>([]);

// Estado del formulario
const form = ref<UserForm>({
  registro: '',
  nombre: '',
  apellido: '',
  carnet: '',
  email: '',
  telefono: '',
  rol: '',
  activo: true,
  password: ''
});

// Errores de validación
const errors = ref<Record<string, string>>({});

// Usuario actualmente editado
const currentUser = ref<Usuario | null>(null);

// Filtros
const filters = ref({
  search: '',
  rol: '',
  activo: ''
});

// Acción de confirmación
const confirmAction = ref({
  title: '',
  message: '',
  action: () => {}
});

// Notificaciones
const notification = ref({
  show: false,
  type: 'success' as 'success' | 'error',
  message: ''
});

const roles = ['RESPONSABLE', 'ADMINISTRATIVO', 'TUTOR'];

// Computed
const filteredUsers = computed(() => {
  let filtered = usuariosData.value;

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase();
    filtered = filtered.filter((u: Usuario) =>
      u.nombre.toLowerCase().includes(search) ||
      u.apellido.toLowerCase().includes(search) ||
      u.registro.toLowerCase().includes(search) ||
      u.carnet.includes(search) ||
      u.email.toLowerCase().includes(search)
    );
  }

  if (filters.value.rol) {
    filtered = filtered.filter((u: Usuario) => u.rol === filters.value.rol);
  }

  if (filters.value.activo !== '') {
    filtered = filtered.filter((u: Usuario) => u.activo === (filters.value.activo === '1'));
  }

  return filtered;
});

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredUsers.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / itemsPerPage.value);
});

const stats = computed((): UsuariosStats => {
  const total = usuariosData.value.length;
  const activos = usuariosData.value.filter((u: Usuario) => u.activo).length;
  const responsables = usuariosData.value.filter((u: Usuario) => u.rol === 'RESPONSABLE').length;

  // Nuevos usuarios este mes
  const thisMonth = new Date();
  thisMonth.setDate(1);
  const nuevos_mes = usuariosData.value.filter((u: Usuario) =>
    new Date(u.created_at) >= thisMonth
  ).length;

  return { total, activos, responsables, nuevos_mes };
});
const BASE_URL = 'https://mail.tecnoweb.org.bo/inf513/grupo07sa/proy2/sistema-cicit-tecno/public';

// Métodos
const loadUsers = async () => {
  try {
    const response = await fetch(`${BASE_URL}/responsable/usuarios-data`);
    if (response.ok) {
      const data = await response.json();
      usuariosData.value = data.users || [];
    }
  } catch {
    // Usar datos mock si no hay endpoint
    usuariosData.value = props.usuarios || [
      {
        id: 1,
        nombre: 'Juan',
        apellido: 'Pérez',
        registro: 'admin',
        carnet: '12345678',
        email: 'admin@cicit.edu.bo',
        telefono: '70123456',
        rol: 'RESPONSABLE',
        activo: true,
        ultimo_acceso: '2025-07-01T10:00:00',
        created_at: '2025-01-01T00:00:00'
      },
      {
        id: 2,
        nombre: 'María',
        apellido: 'García',
        registro: 'maria.garcia',
        carnet: '87654321',
        email: 'maria@cicit.edu.bo',
        telefono: '71234567',
        rol: 'ADMINISTRATIVO',
        activo: true,
        ultimo_acceso: '2025-06-30T15:30:00',
        created_at: '2025-02-15T00:00:00'
      },
      {
        id: 3,
        nombre: 'Carlos',
        apellido: 'López',
        registro: 'carlos.lopez',
        carnet: '11223344',
        email: 'carlos@cicit.edu.bo',
        telefono: '',
        rol: 'TUTOR',
        activo: false,
        ultimo_acceso: '2025-06-25T09:15:00',
        created_at: '2025-03-10T00:00:00'
      }
    ];
  }
};

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
  notification.value = { show: true, type, message };
  setTimeout(() => {
    notification.value.show = false;
  }, 5000);
};

const getInitials = (nombre: string, apellido: string) => {
  return `${nombre.charAt(0)}${apellido.charAt(0)}`.toUpperCase();
};

const getRolVariant = (rol: string) => {
  switch (rol) {
    case 'RESPONSABLE':
      return 'default';
    case 'ADMINISTRATIVO':
      return 'secondary';
    case 'TUTOR':
      return 'outline';
    default:
      return 'outline';
  }
};

const formatDate = (date: string) => {
  if (!date) return 'Nunca';
  return new Date(date).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const resetFilters = () => {
  filters.value = {
    search: '',
    rol: '',
    activo: ''
  };
  currentPage.value = 1;
};

const resetForm = () => {
  form.value = {
    registro: '',
    nombre: '',
    apellido: '',
    carnet: '',
    email: '',
    telefono: '',
    rol: '',
    activo: true,
    password: ''
  };
  errors.value = {};
};

const openCreateModal = () => {
  resetForm();
  isEditing.value = false;
  showModal.value = true;
};

const openEditModal = (usuario: Usuario) => {
  currentUser.value = usuario;
  form.value = {
    registro: usuario.registro,
    nombre: usuario.nombre,
    apellido: usuario.apellido,
    carnet: usuario.carnet,
    email: usuario.email,
    telefono: usuario.telefono || '',
    rol: usuario.rol,
    activo: usuario.activo,
    password: ''
  };
  errors.value = {};
  isEditing.value = true;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
  currentUser.value = null;
  isEditing.value = false;
};

const submitForm = async () => {
  if (processing.value) return;

  processing.value = true;
  errors.value = {};

  try {
    const method = isEditing.value ? 'put' : 'post';
    const url = isEditing.value
      ? `/responsable/usuarios/${currentUser.value?.id}`
      : '/responsable/usuarios';

    const formData = { ...form.value };

    // Si estamos editando y no hay nueva contraseña, la removemos
    if (isEditing.value && !formData.password) {
      delete formData.password;
    }

    router.visit(url, {
      method,
      data: formData,
      preserveScroll: true,
      preserveState: true,
      only: [], // No recargar ningún prop específico para evitar navegación
      onSuccess: () => {
        closeModal();
        showNotification(
          isEditing.value ? 'Usuario actualizado exitosamente' : 'Usuario creado exitosamente'
        );
        loadUsers(); // Recargar datos localmente
      },
      onError: (errorData) => {
        errors.value = errorData;
        showNotification('Error al procesar la solicitud', 'error');
      },
      onFinish: () => {
        processing.value = false;
      }
    });
  } catch {
    processing.value = false;
    showNotification('Error al procesar la solicitud', 'error');
  }
};

const editUser = (usuario: Usuario) => {
  openEditModal(usuario);
};

const toggleUserStatus = (usuario: Usuario) => {
  const newStatus = !usuario.activo;
  const action = newStatus ? 'activar' : 'desactivar';

  confirmAction.value = {
    title: `${action.charAt(0).toUpperCase() + action.slice(1)} Usuario`,
    message: `¿Está seguro de ${action} al usuario ${usuario.nombre} ${usuario.apellido}?`,
    action: () => performToggleStatus(usuario)
  };

  showConfirmModal.value = true;
};

const performToggleStatus = async (usuario: Usuario) => {
  if (processing.value) return;

  processing.value = true;

  try {
    router.visit(`/responsable/usuarios/${usuario.id}/toggle-status`, {
      method: 'put',
      preserveScroll: true,
      preserveState: true,
      only: [], // No recargar ningún prop específico para evitar navegación
      onSuccess: () => {
        usuario.activo = !usuario.activo;
        showNotification(
          `Usuario ${usuario.activo ? 'activado' : 'desactivado'} exitosamente`
        );
        loadUsers(); // Recargar datos localmente
      },
      onError: () => {
        showNotification('Error al cambiar el estado del usuario', 'error');
      },
      onFinish: () => {
        processing.value = false;
        closeConfirmModal();
      }
    });
  } catch {
    processing.value = false;
    showNotification('Error al cambiar el estado del usuario', 'error');
    closeConfirmModal();
  }
};

const deleteUser = (usuario: Usuario) => {
  confirmAction.value = {
    title: 'Eliminar Usuario',
    message: `¿Está seguro de eliminar al usuario ${usuario.nombre} ${usuario.apellido}? Esta acción no se puede deshacer.`,
    action: () => performDelete(usuario)
  };

  showConfirmModal.value = true;
};

const performDelete = async (usuario: Usuario) => {
  if (processing.value) return;

  processing.value = true;

  try {
    router.delete(`/responsable/usuarios/${usuario.id}`, {
      preserveScroll: true,
      preserveState: true,
      only: [], // No recargar ningún prop específico para evitar navegación
      onSuccess: () => {
        showNotification('Usuario eliminado exitosamente');
        loadUsers(); // Recargar datos localmente
      },
      onError: () => {
        showNotification('Error al eliminar el usuario', 'error');
      },
      onFinish: () => {
        processing.value = false;
        closeConfirmModal();
      }
    });
  } catch {
    processing.value = false;
    showNotification('Error al eliminar el usuario', 'error');
    closeConfirmModal();
  }
};

const closeConfirmModal = () => {
  showConfirmModal.value = false;
  confirmAction.value = { title: '', message: '', action: () => {} };
};

const executeConfirmAction = () => {
  confirmAction.value.action();
};

// Watcher para reiniciar página cuando cambian los filtros
watch([filters], () => {
  currentPage.value = 1;
}, { deep: true });

// Cargar usuarios al montar el componente
onMounted(() => {
  loadUsers();

  // Escuchar evento global para abrir modal de crear usuario
  const handleOpenCreateModal = () => {
    openCreateModal();
  };

  document.addEventListener('openCreateModal', handleOpenCreateModal);

  // Limpiar listener al desmontar
  onUnmounted(() => {
    document.removeEventListener('openCreateModal', handleOpenCreateModal);
  });
});
</script>

<style scoped>
.high-contrast {
  filter: contrast(120%);
}
</style>
