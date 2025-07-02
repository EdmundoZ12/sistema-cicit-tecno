<script setup lang="ts">
import CICITLayout from '@/layouts/CICITLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import {
  Palette,
  Type,
  Eye,
  Save,
  RotateCcw
} from 'lucide-vue-next';
import { ref, watch, nextTick } from 'vue';

interface TemaConfiguracion {
  tema_id: number;
  tamano_fuente: number;
  alto_contraste: boolean;
  modo_automatico: boolean;
}

interface Tema {
  id: number;
  nombre: string;
  descripcion: string;
  colores: {
    primario: string;
    secundario: string;
    fondo: string;
  };
}

const props = defineProps<{
  configuracion: TemaConfiguracion;
  temas_disponibles: Tema[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Configuración de Tema', href: '/configuracion' },
];

const form = useForm({
  tema_id: props.configuracion.tema_id,
  tamano_fuente: props.configuracion.tamano_fuente,
  alto_contraste: props.configuracion.alto_contraste,
  modo_automatico: props.configuracion.modo_automatico,
});

const previewMode = ref(false);

// Aplicar cambios en tiempo real para preview
const aplicarPreview = () => {
  const root = document.documentElement;

  // Aplicar tema
  if (form.tema_id === 2) {
    root.classList.add('dark');
    root.style.setProperty('--theme-primary', '#3b82f6');
    root.style.setProperty('--theme-accent', '#1e40af');
  } else {
    root.classList.remove('dark');
    root.style.setProperty('--theme-primary', '#2563eb');
    root.style.setProperty('--theme-accent', '#1d4ed8');
  }

  // Aplicar tamaño de fuente
  root.style.fontSize = `${form.tamano_fuente}px`;

  // Aplicar alto contraste
  if (form.alto_contraste) {
    root.classList.add('high-contrast');
  } else {
    root.classList.remove('high-contrast');
  }

  // Aplicar modo automático
  if (form.modo_automatico) {
    const hour = new Date().getHours();
    const isDark = hour < 7 || hour > 19;
    root.classList.toggle('dark', isDark);
  }
};

// Watch para cambios en tiempo real
watch([() => form.tema_id, () => form.tamano_fuente, () => form.alto_contraste, () => form.modo_automatico], () => {
  if (previewMode.value) {
    nextTick(() => aplicarPreview());
  }
});

const activarPreview = () => {
  previewMode.value = true;
  aplicarPreview();
};

const desactivarPreview = () => {
  previewMode.value = false;
  // Restaurar configuración original
  const root = document.documentElement;

  if (props.configuracion.tema_id === 2) {
    root.classList.add('dark');
  } else {
    root.classList.remove('dark');
  }

  root.style.fontSize = `${props.configuracion.tamano_fuente}px`;

  if (props.configuracion.alto_contraste) {
    root.classList.add('high-contrast');
  } else {
    root.classList.remove('high-contrast');
  }
};

const guardarConfiguracion = () => {
  form.post(route('configuracion.tema.actualizar'), {
    preserveScroll: true,
    onSuccess: () => {
      previewMode.value = false;
      // Aplicar la configuración guardada
      aplicarPreview();
    },
  });
};

const resetearConfiguracion = () => {
  form.reset();
  desactivarPreview();
};

// Tamaños de fuente disponibles
const tamanosFuente = [
  { value: 14, label: 'Pequeño (14px)' },
  { value: 16, label: 'Normal (16px)' },
  { value: 18, label: 'Grande (18px)' },
  { value: 20, label: 'Muy Grande (20px)' },
];

// Encontrar el tema actual
const temaActual = props.temas_disponibles.find(t => t.id === form.tema_id);
</script>

<template>
  <CICITLayout :breadcrumbs="breadcrumbs">
    <Head title="Configuración de Tema" />

    <div class="space-y-8">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Configuración de Tema</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Personaliza la apariencia del sistema CICIT según tus preferencias
        </p>
      </div>

      <!-- Estado del Preview -->
      <div v-if="previewMode" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Eye class="h-5 w-5 text-blue-600" />
            <span class="font-medium text-blue-900 dark:text-blue-100">Modo Preview Activado</span>
          </div>
          <div class="flex gap-2">
            <Button @click="guardarConfiguracion" size="sm" :disabled="form.processing">
              <Save class="h-4 w-4 mr-2" />
              Guardar Cambios
            </Button>
            <Button @click="desactivarPreview" variant="outline" size="sm">
              <RotateCcw class="h-4 w-4 mr-2" />
              Cancelar
            </Button>
          </div>
        </div>
      </div>

      <div class="grid gap-8 lg:grid-cols-3">
        <!-- Configuración Principal -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Tema de Color -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Palette class="h-5 w-5" />
                Tema de Color
              </CardTitle>
              <CardDescription>
                Selecciona el esquema de colores para el sistema
              </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid gap-4 md:grid-cols-2">
                <div v-for="tema in temas_disponibles" :key="tema.id"
                     @click="form.tema_id = tema.id"
                     :class="[
                       'cursor-pointer rounded-lg border-2 p-4 transition-all',
                       form.tema_id === tema.id
                         ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                         : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'
                     ]">
                  <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold">{{ tema.nombre }}</h3>
                    <div class="flex gap-1">
                      <div :style="{ backgroundColor: tema.colores.primario }" class="w-4 h-4 rounded-full"></div>
                      <div :style="{ backgroundColor: tema.colores.secundario }" class="w-4 h-4 rounded-full"></div>
                      <div :style="{ backgroundColor: tema.colores.fondo }" class="w-4 h-4 rounded-full border"></div>
                    </div>
                  </div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">{{ tema.descripcion }}</p>
                  <div v-if="form.tema_id === tema.id" class="mt-2">
                    <Badge variant="default">Seleccionado</Badge>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Configuración de Fuente -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Type class="h-5 w-5" />
                Tamaño de Fuente
              </CardTitle>
              <CardDescription>
                Ajusta el tamaño del texto para mejor legibilidad
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div>
                  <Label for="tamano-fuente">Tamaño de Fuente</Label>
                  <Select v-model="form.tamano_fuente">
                    <SelectTrigger>
                      <SelectValue placeholder="Selecciona un tamaño" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem v-for="tamano in tamanosFuente" :key="tamano.value" :value="tamano.value">
                        {{ tamano.label }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <p class="font-medium mb-2">Vista Previa del Texto</p>
                  <p :style="{ fontSize: form.tamano_fuente + 'px' }">
                    Este es un ejemplo de cómo se verá el texto con el tamaño seleccionado.
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Opciones de Accesibilidad -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Eye class="h-5 w-5" />
                Opciones de Accesibilidad
              </CardTitle>
              <CardDescription>
                Configuraciones para mejorar la accesibilidad visual
              </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
              <div class="flex items-center justify-between">
                <div>
                  <Label class="text-base font-medium">Alto Contraste</Label>
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    Aumenta el contraste para mejor visibilidad
                  </p>
                </div>
                <Switch v-model:checked="form.alto_contraste" />
              </div>

              <Separator />

              <div class="flex items-center justify-between">
                <div>
                  <Label class="text-base font-medium">Modo Automático</Label>
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    Cambia automáticamente entre tema claro y oscuro según la hora
                  </p>
                </div>
                <Switch v-model:checked="form.modo_automatico" />
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Panel Lateral -->
        <div class="space-y-6">
          <!-- Información Actual -->
          <Card>
            <CardHeader>
              <CardTitle>Configuración Actual</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div>
                <Label class="text-sm font-medium">Tema</Label>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ temaActual?.nombre || 'Tema no encontrado' }}
                </p>
              </div>

              <div>
                <Label class="text-sm font-medium">Tamaño de Fuente</Label>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ form.tamano_fuente }}px
                </p>
              </div>

              <div>
                <Label class="text-sm font-medium">Alto Contraste</Label>
                <Badge :variant="form.alto_contraste ? 'default' : 'secondary'">
                  {{ form.alto_contraste ? 'Activado' : 'Desactivado' }}
                </Badge>
              </div>

              <div>
                <Label class="text-sm font-medium">Modo Automático</Label>
                <Badge :variant="form.modo_automatico ? 'default' : 'secondary'">
                  {{ form.modo_automatico ? 'Activado' : 'Desactivado' }}
                </Badge>
              </div>
            </CardContent>
          </Card>

          <!-- Acciones -->
          <Card>
            <CardHeader>
              <CardTitle>Acciones</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <Button @click="activarPreview" variant="outline" class="w-full" :disabled="previewMode">
                <Eye class="h-4 w-4 mr-2" />
                Vista Previa
              </Button>

              <Button @click="guardarConfiguracion" class="w-full" :disabled="form.processing">
                <Save class="h-4 w-4 mr-2" />
                {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
              </Button>

              <Button @click="resetearConfiguracion" variant="outline" class="w-full">
                <RotateCcw class="h-4 w-4 mr-2" />
                Resetear
              </Button>
            </CardContent>
          </Card>

          <!-- Información -->
          <Card>
            <CardHeader>
              <CardTitle>Información</CardTitle>
            </CardHeader>
            <CardContent class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
              <p>• Los cambios se aplicarán inmediatamente al guardar</p>
              <p>• El modo automático se basa en la hora del sistema</p>
              <p>• El alto contraste mejora la visibilidad para usuarios con problemas visuales</p>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </CICITLayout>
</template>

<style scoped>
/* Estilos para mejorar la visibilidad del alto contraste */
.high-contrast {
  filter: contrast(1.5) brightness(1.1);
}

.dark .high-contrast {
  filter: contrast(1.8) brightness(1.2);
}

/* Transiciones suaves */
* {
  transition: all 0.3s ease;
}
</style>
