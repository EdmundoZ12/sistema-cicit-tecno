<script setup lang="ts">
import CICITLayout from '@/layouts/CICITLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import {
  User,
  Mail,
  Phone,
  IdCard,
  Shield,
  Calendar,
  Save,
  Eye,
  EyeOff,
  CheckCircle,
  XCircle
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card/index';
import { Button } from '@/components/ui/button/index';
import { Input } from '@/components/ui/input/index';
import { Label } from '@/components/ui/label/index';
import Badge from '@/components/ui/badge/Badge.vue';

interface Usuario {
  id: number;
  nombre: string;
  apellido: string;
  email: string;
  telefono: string;
  carnet: string;
  registro: string;
  rol: string;
  activo: boolean;
  created_at: string;
  updated_at: string;
}

const props = defineProps<{
  usuario: Usuario;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Mi Perfil', href: '#' },
];

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Formulario para actualizar información personal
const profileForm = useForm({
  nombre: props.usuario.nombre,
  apellido: props.usuario.apellido,
  email: props.usuario.email,
  telefono: props.usuario.telefono || '',
  carnet: props.usuario.carnet || '',
});

// Formulario para cambio de contraseña
const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

// Computed para mostrar el estado del usuario
const statusBadge = computed(() => {
  return props.usuario.activo
    ? { text: 'Activo', variant: 'default' as const, icon: CheckCircle, color: 'text-green-600' }
    : { text: 'Inactivo', variant: 'destructive' as const, icon: XCircle, color: 'text-red-600' };
});

// Computed para el rol en español
const rolSpanish = computed(() => {
  const roles: Record<string, string> = {
    'RESPONSABLE': 'Responsable',
    'ADMINISTRATIVO': 'Administrativo',
    'TUTOR': 'Tutor',
    'PARTICIPANTE': 'Participante'
  };
  return roles[props.usuario.rol] || props.usuario.rol;
});

// Formatear fecha de registro
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Función para actualizar perfil
const updateProfile = () => {
  profileForm.put('/profile', {
    preserveScroll: true,
    onSuccess: () => {
      // Manejar éxito
    },
    onError: () => {
      // Manejar errores
    },
  });
};

// Función para cambiar contraseña
const updatePassword = () => {
  passwordForm.put('/profile/password', {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset();
    },
    onError: () => {
      // Manejar errores
    },
  });
};
</script>

<template>
  <Head title="Mi Perfil - CICIT" />

  <CICITLayout :breadcrumbs="breadcrumbs" title="Mi Perfil">
    <div class="space-y-6">
      <!-- Encabezado del perfil -->
      <div class="flex items-start justify-between">
        <div class="flex items-center space-x-4">
          <div class="h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white text-xl font-bold">
            {{ usuario.nombre.charAt(0) }}{{ usuario.apellido.charAt(0) }}
          </div>
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              {{ usuario.nombre }} {{ usuario.apellido }}
            </h1>
            <div class="flex items-center gap-3 mt-1">
              <p class="text-gray-600 dark:text-gray-400">{{ rolSpanish }}</p>
              <Badge :variant="statusBadge.variant" class="flex items-center gap-1">
                <component :is="statusBadge.icon" class="h-3 w-3" />
                {{ statusBadge.text }}
              </Badge>
            </div>
          </div>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Información del usuario -->
        <Card class="lg:col-span-1">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <User class="h-5 w-5" />
              Información Personal
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-3">
              <div class="flex items-center gap-3">
                <IdCard class="h-4 w-4 text-gray-500" />
                <div>
                  <p class="text-sm font-medium text-gray-600">Registro</p>
                  <p class="text-sm text-gray-900 dark:text-white">{{ usuario.registro }}</p>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <Mail class="h-4 w-4 text-gray-500" />
                <div>
                  <p class="text-sm font-medium text-gray-600">Email</p>
                  <p class="text-sm text-gray-900 dark:text-white">{{ usuario.email }}</p>
                </div>
              </div>

              <div v-if="usuario.telefono" class="flex items-center gap-3">
                <Phone class="h-4 w-4 text-gray-500" />
                <div>
                  <p class="text-sm font-medium text-gray-600">Teléfono</p>
                  <p class="text-sm text-gray-900 dark:text-white">{{ usuario.telefono }}</p>
                </div>
              </div>

              <div v-if="usuario.carnet" class="flex items-center gap-3">
                <IdCard class="h-4 w-4 text-gray-500" />
                <div>
                  <p class="text-sm font-medium text-gray-600">Carnet</p>
                  <p class="text-sm text-gray-900 dark:text-white">{{ usuario.carnet }}</p>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <Shield class="h-4 w-4 text-gray-500" />
                <div>
                  <p class="text-sm font-medium text-gray-600">Rol</p>
                  <p class="text-sm text-gray-900 dark:text-white">{{ rolSpanish }}</p>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <Calendar class="h-4 w-4 text-gray-500" />
                <div>
                  <p class="text-sm font-medium text-gray-600">Miembro desde</p>
                  <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(usuario.created_at) }}</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Formularios de edición -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Actualizar información personal -->
          <Card>
            <CardHeader>
              <CardTitle>Actualizar Información Personal</CardTitle>
              <CardDescription>
                Modifica tu información personal básica.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form @submit.prevent="updateProfile" class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                  <div>
                    <Label for="nombre">Nombre</Label>
                    <Input
                      id="nombre"
                      v-model="profileForm.nombre"
                      type="text"
                      required
                      class="mt-1"
                      :class="{ 'border-red-500': profileForm.errors.nombre }"
                    />
                    <p v-if="profileForm.errors.nombre" class="text-sm text-red-600 mt-1">
                      {{ profileForm.errors.nombre }}
                    </p>
                  </div>

                  <div>
                    <Label for="apellido">Apellido</Label>
                    <Input
                      id="apellido"
                      v-model="profileForm.apellido"
                      type="text"
                      required
                      class="mt-1"
                      :class="{ 'border-red-500': profileForm.errors.apellido }"
                    />
                    <p v-if="profileForm.errors.apellido" class="text-sm text-red-600 mt-1">
                      {{ profileForm.errors.apellido }}
                    </p>
                  </div>
                </div>

                <div>
                  <Label for="email">Email</Label>
                  <Input
                    id="email"
                    v-model="profileForm.email"
                    type="email"
                    required
                    class="mt-1"
                    :class="{ 'border-red-500': profileForm.errors.email }"
                  />
                  <p v-if="profileForm.errors.email" class="text-sm text-red-600 mt-1">
                    {{ profileForm.errors.email }}
                  </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                  <div>
                    <Label for="telefono">Teléfono (opcional)</Label>
                    <Input
                      id="telefono"
                      v-model="profileForm.telefono"
                      type="tel"
                      class="mt-1"
                      :class="{ 'border-red-500': profileForm.errors.telefono }"
                    />
                    <p v-if="profileForm.errors.telefono" class="text-sm text-red-600 mt-1">
                      {{ profileForm.errors.telefono }}
                    </p>
                  </div>

                  <div>
                    <Label for="carnet">Carnet (opcional)</Label>
                    <Input
                      id="carnet"
                      v-model="profileForm.carnet"
                      type="text"
                      class="mt-1"
                      :class="{ 'border-red-500': profileForm.errors.carnet }"
                    />
                    <p v-if="profileForm.errors.carnet" class="text-sm text-red-600 mt-1">
                      {{ profileForm.errors.carnet }}
                    </p>
                  </div>
                </div>

                <div class="flex justify-end">
                  <Button
                    type="submit"
                    :disabled="profileForm.processing"
                    class="flex items-center gap-2"
                  >
                    <Save class="h-4 w-4" />
                    {{ profileForm.processing ? 'Guardando...' : 'Guardar Cambios' }}
                  </Button>
                </div>
              </form>
            </CardContent>
          </Card>

          <!-- Cambiar contraseña -->
          <Card>
            <CardHeader>
              <CardTitle>Cambiar Contraseña</CardTitle>
              <CardDescription>
                Actualiza tu contraseña para mantener tu cuenta segura.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form @submit.prevent="updatePassword" class="space-y-4">
                <div>
                  <Label for="current_password">Contraseña Actual</Label>
                  <div class="relative mt-1">
                    <Input
                      id="current_password"
                      v-model="passwordForm.current_password"
                      :type="showCurrentPassword ? 'text' : 'password'"
                      required
                      :class="{ 'border-red-500': passwordForm.errors.current_password }"
                    />
                    <button
                      type="button"
                      @click="showCurrentPassword = !showCurrentPassword"
                      class="absolute inset-y-0 right-0 pr-3 flex items-center"
                    >
                      <component :is="showCurrentPassword ? EyeOff : Eye" class="h-4 w-4 text-gray-500" />
                    </button>
                  </div>
                  <p v-if="passwordForm.errors.current_password" class="text-sm text-red-600 mt-1">
                    {{ passwordForm.errors.current_password }}
                  </p>
                </div>

                <div>
                  <Label for="password">Nueva Contraseña</Label>
                  <div class="relative mt-1">
                    <Input
                      id="password"
                      v-model="passwordForm.password"
                      :type="showNewPassword ? 'text' : 'password'"
                      required
                      :class="{ 'border-red-500': passwordForm.errors.password }"
                    />
                    <button
                      type="button"
                      @click="showNewPassword = !showNewPassword"
                      class="absolute inset-y-0 right-0 pr-3 flex items-center"
                    >
                      <component :is="showNewPassword ? EyeOff : Eye" class="h-4 w-4 text-gray-500" />
                    </button>
                  </div>
                  <p v-if="passwordForm.errors.password" class="text-sm text-red-600 mt-1">
                    {{ passwordForm.errors.password }}
                  </p>
                </div>

                <div>
                  <Label for="password_confirmation">Confirmar Nueva Contraseña</Label>
                  <div class="relative mt-1">
                    <Input
                      id="password_confirmation"
                      v-model="passwordForm.password_confirmation"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      required
                      :class="{ 'border-red-500': passwordForm.errors.password_confirmation }"
                    />
                    <button
                      type="button"
                      @click="showConfirmPassword = !showConfirmPassword"
                      class="absolute inset-y-0 right-0 pr-3 flex items-center"
                    >
                      <component :is="showConfirmPassword ? EyeOff : Eye" class="h-4 w-4 text-gray-500" />
                    </button>
                  </div>
                  <p v-if="passwordForm.errors.password_confirmation" class="text-sm text-red-600 mt-1">
                    {{ passwordForm.errors.password_confirmation }}
                  </p>
                </div>

                <div class="flex justify-end">
                  <Button
                    type="submit"
                    :disabled="passwordForm.processing"
                    class="flex items-center gap-2"
                  >
                    <Save class="h-4 w-4" />
                    {{ passwordForm.processing ? 'Actualizando...' : 'Cambiar Contraseña' }}
                  </Button>
                </div>
              </form>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </CICITLayout>
</template>

<style scoped>
/* Mejoras de accesibilidad */
.focus\:outline-none:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

/* Transiciones suaves */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Mejoras visuales para dark mode */
.dark .bg-gradient-to-r {
  opacity: 0.9;
}

/* Mejor contraste para textos */
.dark .text-gray-600 {
  color: #d1d5db;
}

.dark .text-gray-500 {
  color: #9ca3af;
}
</style>
