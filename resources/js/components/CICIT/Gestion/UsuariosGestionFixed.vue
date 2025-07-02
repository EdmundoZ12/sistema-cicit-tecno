<template>
  <div class="space-y-4" :class="themeClasses">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Gestión de Usuarios</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Administrar usuarios del sistema CICIT</p>
      </div>
      <button
        @click="openCreateModal"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
      >
        <Plus class="w-4 h-4 mr-2" />
        Nuevo Usuario
      </button>
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
              placeholder="Nombre, registro o cédula..."
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm dark:bg-gray-800 dark:text-white"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Rol
            </label>
            <select
              v-model="filters.rol"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm dark:bg-gray-800 dark:text-white"
            >
              <option value="">Todos los roles</option>
              <option v-for="rol in roles" :key="rol" :value="rol">{{ rol }}</option>
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
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <Card>
        <CardContent class="p-4">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
              <Users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Usuarios</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-4">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
              <UserCheck class="h-5 w-5 text-green-600 dark:text-green-400" />
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
              <GraduationCap class="h-5 w-5 text-purple-600 dark:text-purple-400" />
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Responsables</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.responsables }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-4">
          <div class="flex items-center">
            <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
              <Activity class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nuevos (Mes)</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.nuevos_mes }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Tabla de usuarios -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Users class="h-5 w-5" />
          Lista de Usuarios
        </CardTitle>
        <CardDescription>
          {{ filteredUsers.length }} usuarios encontrados
        </CardDescription>
      </CardHeader>
      <CardContent class="p-0">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Usuario
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Contacto
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Rol
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Estado
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Último Acceso
                </th>
                <th scope="col" class="relative px-4 py-3">
                  <span class="sr-only">Acciones</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="usuario in paginatedUsers" :key="usuario.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                          {{ usuario.nombre.charAt(0) }}{{ usuario.apellido.charAt(0) }}
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
                  <div class="text-sm text-gray-500 dark:text-gray-400">{{ usuario.telefono || 'Sin teléfono' }}</div>
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
                  <div class="flex items-center justify-end gap-2">
                    <button
                      @click="editUser(usuario)"
                      class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                      title="Editar usuario"
                    >
                      <Edit class="h-4 w-4" />
                    </button>
                    <button
                      @click="toggleUserStatus(usuario)"
                      :class="usuario.activo ? 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300' : 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300'"
                      :title="usuario.activo ? 'Desactivar usuario' : 'Activar usuario'"
                    >
                      <Power class="h-4 w-4" />
                    </button>
                    <button
                      @click="deleteUser(usuario)"
                      class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                      title="Eliminar usuario"
                    >
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="totalPages > 1" class="px-4 py-3 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Anterior
            </button>
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Siguiente
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700 dark:text-gray-300">
                Mostrando {{ ((currentPage - 1) * itemsPerPage) + 1 }} a {{ Math.min(currentPage * itemsPerPage, filteredUsers.length) }} de {{ filteredUsers.length }} resultados
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <button
                  @click="currentPage--"
                  :disabled="currentPage === 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ChevronDown class="h-5 w-5 rotate-90" />
                </button>
                <button
                  v-for="page in visiblePages"
                  :key="page"
                  @click="currentPage = page"
                  :class="[
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                    page === currentPage
                      ? 'z-10 bg-primary-50 border-primary-500 text-primary-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                  ]"
                >
                  {{ page }}
                </button>
                <button
                  @click="currentPage++"
                  :disabled="currentPage === totalPages"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ChevronDown class="h-5 w-5 -rotate-90" />
                </button>
              </nav>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

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

                    <!-- Nota sobre contraseña automática (solo para crear) -->
                    <div v-if="!isEditing" class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-md p-3">
                      <div class="flex">
                        <div class="flex-shrink-0">
                          <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                          </svg>
                        </div>
                        <div class="ml-3">
                          <p class="text-sm text-blue-700 dark:text-blue-300">
                            <strong>Nota:</strong> La contraseña se generará automáticamente cuando se cree el usuario. Se mostrará después de crearlo.
                          </p>
                        </div>
                      </div>
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
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 dark:bg-blue-500 text-base font-medium text-white hover:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
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
              @click="confirmAction.action"
              :disabled="processing"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
            >
              <span v-if="processing" class="mr-2">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
              </span>
              Confirmar
            </button>
            <button
              type="button"
              @click="closeConfirmModal"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-700"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import {
  Users,
  Plus,
  UserCheck,
  GraduationCap,
  Activity,
  RotateCcw,
  Edit,
  Power,
  Trash2,
  AlertTriangle,
  ChevronDown
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
  const fontSize = props.userThemeConfig?.tamano_fuente || 14;
  if (fontSize === 12) classes.push('text-xs');
  else if (fontSize === 14) classes.push('text-sm');
  else if (fontSize === 16) classes.push('text-base');
  else if (fontSize === 18) classes.push('text-lg');
  else if (fontSize === 20) classes.push('text-xl');

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
const usuariosList = ref<Usuario[]>([]);

// Estado del formulario
const form = ref<UserForm>({
  registro: '',
  nombre: '',
  apellido: '',
  carnet: '',
  email: '',
  telefono: '',
  rol: '',
  activo: true
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
const confirmAction = ref<{
  title: string;
  message: string;
  action: () => void;
}>({
  title: '',
  message: '',
  action: () => {}
});

const roles = ['RESPONSABLE', 'ADMINISTRATIVO', 'TUTOR'];

// Computed
const filteredUsers = computed(() => {
  let filtered = usuariosList.value;

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
    const isActive = filters.value.activo === 'true';
    filtered = filtered.filter((u: Usuario) => u.activo === isActive);
  }

  return filtered;
});

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage.value));

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredUsers.value.slice(start, end);
});

const visiblePages = computed(() => {
  const pages = [];
  const start = Math.max(1, currentPage.value - 2);
  const end = Math.min(totalPages.value, currentPage.value + 2);

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

const stats = computed((): UsuariosStats => {
  const total = usuariosList.value.length;
  const activos = usuariosList.value.filter((u: Usuario) => u.activo).length;
  const responsables = usuariosList.value.filter((u: Usuario) => u.rol === 'RESPONSABLE').length;

  // Nuevos usuarios este mes
  const currentMonth = new Date().getMonth();
  const currentYear = new Date().getFullYear();
  const nuevos_mes = usuariosList.value.filter((u: Usuario) =>
    new Date(u.created_at).getMonth() === currentMonth &&
    new Date(u.created_at).getFullYear() === currentYear
  ).length;

  return { total, activos, responsables, nuevos_mes };
});

// Cargar datos
const loadUsers = async () => {
  try {
    const response = await fetch('/responsable/usuarios-data');
    if (response.ok) {
      const data = await response.json();
      usuariosList.value = data.users || [];
    }
  } catch {
    // Si falla la carga desde la API, usar los datos de props
    usuariosList.value = props.usuarios || [];
  }
};

// Event listeners para modal desde el header
onMounted(() => {
  loadUsers();

  // Escuchar evento personalizado para abrir modal desde el header
  document.addEventListener('openCreateModal', () => {
    openCreateModal();
  });
});

// Funciones del modal
const resetForm = () => {
  form.value = {
    registro: '',
    nombre: '',
    apellido: '',
    carnet: '',
    email: '',
    telefono: '',
    rol: '',
    activo: true
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
    activo: usuario.activo
  };
  errors.value = {};
  isEditing.value = true;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
  currentUser.value = null;
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

    router.visit(url, {
      method,
      data: form.value,
      preserveScroll: true,
      onSuccess: () => {
        closeModal();
        showNotification(
          isEditing.value ? 'Usuario actualizado exitosamente' : 'Usuario creado exitosamente'
        );
        loadUsers();
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
      onSuccess: () => {
        usuario.activo = !usuario.activo;
        showNotification(
          `Usuario ${usuario.activo ? 'activado' : 'desactivado'} exitosamente`
        );
        loadUsers();
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
    action: () => performDeleteUser(usuario)
  };

  showConfirmModal.value = true;
};

const performDeleteUser = async (usuario: Usuario) => {
  if (processing.value) return;

  processing.value = true;

  try {
    router.visit(`/responsable/usuarios/${usuario.id}`, {
      method: 'delete',
      preserveScroll: true,
      onSuccess: () => {
        showNotification('Usuario eliminado exitosamente');
        loadUsers();
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

const resetFilters = () => {
  filters.value = {
    search: '',
    rol: '',
    activo: ''
  };
  currentPage.value = 1;
};

const getRolVariant = (rol: string) => {
  switch (rol) {
    case 'RESPONSABLE': return 'destructive';
    case 'ADMINISTRATIVO': return 'default';
    case 'TUTOR': return 'secondary';
    default: return 'outline';
  }
};

const formatDate = (dateString: string) => {
  if (!dateString) return 'Nunca';
  return new Date(dateString).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Notificaciones (implementación simple)
const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
  // En una implementación real, aquí mostrarías una notificación toast
  console.log(`${type.toUpperCase()}: ${message}`);
};

// Watcher para resetear la página cuando cambien los filtros
watch([() => filters.value.search, () => filters.value.rol, () => filters.value.activo], () => {
  currentPage.value = 1;
});
</script>
