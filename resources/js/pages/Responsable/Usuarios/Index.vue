<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold">Gestión de Usuarios</h1>
        <p class="text-muted-foreground">Administrar usuarios del sistema CICIT</p>
      </div>
      <button
        @click="showCreateForm = true"
        class="btn-primary"
      >
        <Plus class="w-4 h-4 mr-2" />
        Nuevo Usuario
      </button>
    </div>

    <!-- Filtros -->
    <div class="card p-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="label">Buscar</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Nombre, registro o cédula..."
            class="input"
          />
        </div>
        <div>
          <label class="label">Tipo</label>
          <select v-model="filters.rol" class="input">
            <option value="">Todos</option>
            <option v-for="rol in roles" :key="rol" :value="rol">
              {{ rol }}
            </option>
          </select>
        </div>
        <div>
          <label class="label">Estado</label>
          <select v-model="filters.activo" class="input">
            <option value="">Todos</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="resetFilters" class="btn-secondary w-full">
            <RotateCcw class="w-4 h-4 mr-2" />
            Limpiar
          </button>
        </div>
      </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th @click="sort('registro')" class="cursor-pointer hover:bg-muted/50">
                Registro
                <ArrowUpDown class="w-4 h-4 inline ml-1" />
              </th>
              <th @click="sort('nombre')" class="cursor-pointer hover:bg-muted/50">
                Nombre Completo
                <ArrowUpDown class="w-4 h-4 inline ml-1" />
              </th>
              <th>Carnet</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Rol</th>
              <th>Estado</th>
              <th @click="sort('created_at')" class="cursor-pointer hover:bg-muted/50">
                Fecha Registro
                <ArrowUpDown class="w-4 h-4 inline ml-1" />
              </th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="usuario in usuarios.data" :key="usuario.id">
              <td class="font-mono">{{ usuario.registro }}</td>
              <td class="font-medium">{{ usuario.nombre }} {{ usuario.apellido }}</td>
              <td>{{ usuario.carnet }}</td>
              <td>{{ usuario.email }}</td>
              <td>{{ usuario.telefono || '-' }}</td>
              <td>
                <span class="badge" :class="getRolBadgeClass(usuario.rol)">
                  {{ usuario.rol }}
                </span>
              </td>
              <td>
                <span class="badge" :class="usuario.activo ? 'badge-success' : 'badge-destructive'">
                  {{ usuario.activo ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td>{{ formatDate(usuario.created_at) }}</td>
              <td class="text-center">
                <div class="flex justify-center space-x-2">
                  <button
                    @click="editUser(usuario)"
                    class="btn-sm btn-secondary"
                    title="Editar"
                  >
                    <Edit2 class="w-4 h-4" />
                  </button>
                  <button
                    @click="toggleUserStatus(usuario)"
                    class="btn-sm"
                    :class="usuario.activo ? 'btn-destructive' : 'btn-success'"
                    :title="usuario.activo ? 'Desactivar' : 'Activar'"
                  >
                    <component :is="usuario.activo ? UserX : UserCheck" class="w-4 h-4" />
                  </button>
                  <button
                    @click="resetPassword(usuario)"
                    class="btn-sm btn-warning"
                    title="Restablecer contraseña"
                  >
                    <KeyRound class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="usuarios.last_page > 1" class="border-t p-4">
        <div class="flex justify-between items-center">
          <p class="text-sm text-muted-foreground">
            Mostrando {{ usuarios.from }} a {{ usuarios.to }} de {{ usuarios.total }} usuarios
          </p>
          <div class="flex space-x-2">
            <button
              v-for="page in getPageNumbers()"
              :key="page"
              @click="goToPage(page)"
              class="btn-sm"
              :class="page === usuarios.current_page ? 'btn-primary' : 'btn-secondary'"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Crear/Editar Usuario -->
    <div v-if="showCreateForm || editingUser" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2 class="text-xl font-bold">
            {{ editingUser ? 'Editar Usuario' : 'Nuevo Usuario' }}
          </h2>
          <button @click="closeModal" class="text-muted-foreground hover:text-foreground">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="saveUser" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="label required">Registro</label>
              <input
                v-model="form.registro"
                type="text"
                class="input"
                :disabled="!!editingUser"
                required
              />
              <div v-if="errors.registro" class="text-sm text-destructive mt-1">
                {{ errors.registro }}
              </div>
            </div>

            <div>
              <label class="label required">Nombre</label>
              <input
                v-model="form.nombre"
                type="text"
                class="input"
                required
              />
              <div v-if="errors.nombre" class="text-sm text-destructive mt-1">
                {{ errors.nombre }}
              </div>
            </div>

            <div>
              <label class="label required">Apellido</label>
              <input
                v-model="form.apellido"
                type="text"
                class="input"
                required
              />
              <div v-if="errors.apellido" class="text-sm text-destructive mt-1">
                {{ errors.apellido }}
              </div>
            </div>

            <div>
              <label class="label required">Carnet</label>
              <input
                v-model="form.carnet"
                type="text"
                class="input"
                required
              />
              <div v-if="errors.carnet" class="text-sm text-destructive mt-1">
                {{ errors.carnet }}
              </div>
            </div>

            <div>
              <label class="label required">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="input"
                required
              />
              <div v-if="errors.email" class="text-sm text-destructive mt-1">
                {{ errors.email }}
              </div>
            </div>

            <div>
              <label class="label">Teléfono</label>
              <input
                v-model="form.telefono"
                type="text"
                class="input"
              />
              <div v-if="errors.telefono" class="text-sm text-destructive mt-1">
                {{ errors.telefono }}
              </div>
            </div>

            <div>
              <label class="label required">Rol</label>
              <select v-model="form.rol" class="input" required>
                <option value="">Seleccionar rol...</option>
                <option v-for="rol in roles" :key="rol" :value="rol">
                  {{ rol }}
                </option>
              </select>
              <div v-if="errors.rol" class="text-sm text-destructive mt-1">
                {{ errors.rol }}
              </div>
            </div>

            <div>
              <label class="label">Estado</label>
              <select v-model="form.activo" class="input">
                <option :value="true">Activo</option>
                <option :value="false">Inactivo</option>
              </select>
            </div>

            <!-- Campo de contraseña solo para usuarios nuevos -->
            <div v-if="!editingUser" class="md:col-span-2">
              <label class="label required">Contraseña</label>
              <input
                v-model="form.password"
                type="password"
                class="input"
                placeholder="Ingrese la contraseña para el usuario"
                required
                minlength="8"
              />
              <div v-if="errors.password" class="text-sm text-destructive mt-1">
                {{ errors.password }}
              </div>
              <p class="text-sm text-muted-foreground mt-1">
                La contraseña debe tener al menos 8 caracteres
              </p>
            </div>
          </div>

          <div class="flex justify-end space-x-2 pt-4 border-t">
            <button type="button" @click="closeModal" class="btn-secondary">
              Cancelar
            </button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Guardando...' : (editingUser ? 'Actualizar' : 'Crear') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  Plus,
  Edit2,
  UserX,
  UserCheck,
  KeyRound,
  ArrowUpDown,
  RotateCcw,
  X
} from 'lucide-vue-next'

// Props
interface User {
  id: number
  registro: string
  nombre: string
  apellido: string
  carnet: string
  email: string
  telefono?: string
  rol: string
  activo: boolean
  created_at: string
}

interface PaginatedUsers {
  data: User[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

const props = defineProps<{
  usuarios: PaginatedUsers
  roles: string[]
}>()

// Estado reactivo
const showCreateForm = ref(false)
const editingUser = ref<User | null>(null)
const saving = ref(false)
const errors = ref<Record<string, string>>({})

const filters = reactive({
  search: '',
  rol: '',
  activo: '',
  sort: 'created_at',
  direction: 'desc'
})

const form = reactive({
  registro: '',
  nombre: '',
  apellido: '',
  carnet: '',
  email: '',
  telefono: '',
  rol: '',
  activo: true,
  password: ''
})

// Watchers para filtros
watch(filters, () => {
  router.get(route('responsable.usuarios'), filters, {
    preserveState: true,
    preserveScroll: true
  })
}, { deep: true })

// Métodos
const resetFilters = () => {
  Object.assign(filters, {
    search: '',
    tipo: '',
    activo: '',
    sort: 'created_at',
    direction: 'desc'
  })
}

const sort = (field: string) => {
  if (filters.sort === field) {
    filters.direction = filters.direction === 'asc' ? 'desc' : 'asc'
  } else {
    filters.sort = field
    filters.direction = 'asc'
  }
}

const goToPage = (page: number) => {
  router.get(route('responsable.usuarios'), { ...filters, page })
}

const getPageNumbers = () => {
  const pages = []
  const current = props.usuarios.current_page
  const last = props.usuarios.last_page

  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i)
  }

  return pages
}

const getRolBadgeClass = (rol: string) => {
  const classes: Record<string, string> = {
    'RESPONSABLE': 'badge-destructive',
    'ADMINISTRATIVO': 'badge-warning',
    'TUTOR': 'badge-primary'
  }

  return classes[rol] || 'badge-secondary'
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const resetForm = () => {
  Object.assign(form, {
    registro: '',
    nombre: '',
    apellido: '',
    carnet: '',
    email: '',
    telefono: '',
    rol: '',
    activo: true,
    password: ''
  })
  errors.value = {}
}

const editUser = (user: User) => {
  editingUser.value = user
  Object.assign(form, {
    registro: user.registro,
    nombre: user.nombre,
    apellido: user.apellido,
    carnet: user.carnet,
    email: user.email,
    telefono: user.telefono || '',
    rol: user.rol,
    activo: user.activo,
    password: ''
  })
}

const closeModal = () => {
  showCreateForm.value = false
  editingUser.value = null
  resetForm()
}

const saveUser = () => {
  saving.value = true
  errors.value = {}

  const url = editingUser.value
    ? route('responsable.usuarios.update', editingUser.value.id)
    : route('responsable.usuarios.store')

  const method = editingUser.value ? 'put' : 'post'

  router[method](url, form, {
    onSuccess: () => {
      closeModal()
      // Toast de éxito aquí
    },
    onError: (responseErrors) => {
      errors.value = responseErrors
    },
    onFinish: () => {
      saving.value = false
    }
  })
}

const toggleUserStatus = (user: User) => {
  if (confirm(`¿Estás seguro de ${user.activo ? 'desactivar' : 'activar'} este usuario?`)) {
    router.put(route('responsable.usuarios.toggle-status', user.id), {}, {
      preserveScroll: true
    })
  }
}

const resetPassword = (user: User) => {
  if (confirm(`¿Estás seguro de restablecer la contraseña de ${user.nombre} ${user.apellido}?`)) {
    router.post(route('responsable.usuarios.reset-password', user.id), {}, {
      preserveScroll: true
    })
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}

.modal-content {
  background-color: hsl(var(--background));
  border: 1px solid hsl(var(--border));
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 42rem;
  margin: 1rem;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid hsl(var(--border));
}

.modal-body {
  padding: 1.5rem;
}

.label.required::after {
  content: ' *';
  color: hsl(var(--destructive));
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table th {
  border-bottom: 1px solid hsl(var(--border));
  background-color: hsl(var(--muted) / 0.5);
  padding: 0.75rem 1rem;
  text-align: left;
  font-size: 0.875rem;
  font-weight: 500;
}

.table td {
  border-bottom: 1px solid hsl(var(--border));
  padding: 0.75rem 1rem;
  font-size: 0.875rem;
}

.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.badge-primary {
  background-color: hsl(var(--primary) / 0.1);
  color: hsl(var(--primary));
}

.badge-secondary {
  background-color: hsl(var(--secondary) / 0.1);
  color: hsl(var(--secondary-foreground));
}

.badge-success {
  background-color: rgb(220 252 231);
  color: rgb(22 101 52);
}

.dark .badge-success {
  background-color: rgb(20 83 45 / 0.2);
  color: rgb(74 222 128);
}

.badge-destructive {
  background-color: hsl(var(--destructive) / 0.1);
  color: hsl(var(--destructive));
}

.badge-warning {
  background-color: rgb(254 249 195);
  color: rgb(133 77 14);
}

.dark .badge-warning {
  background-color: rgb(113 63 18 / 0.2);
  color: rgb(250 204 21);
}
</style>
