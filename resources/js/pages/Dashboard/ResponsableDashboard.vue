<script setup lang="ts">
import CICITLayout from '@/layouts/CICITLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { onMounted, computed, ref, watch } from 'vue';
import {
  Users,
  BookOpen,
  DollarSign,
  Eye,
  Activity,
  UserCheck,
  FileText,
  Calendar,
  GraduationCap,
  TrendingUp
} from 'lucide-vue-next';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card/index';
import Badge from '@/components/ui/badge/Badge.vue';

// Componentes de gestión
import UsuariosGestion from '@/components/CICIT/Gestion/UsuariosGestionFixed.vue';
import TiposParticipanteGestion from '@/components/CICIT/Gestion/TiposParticipanteGestion.vue';
import GestionesGestion from '@/components/CICIT/Gestion/GestionesGestion.vue';
import CursosGestion from '@/components/CICIT/Gestion/CursosGestion.vue';

interface Estadisticas {
  total_usuarios: number;
  total_participantes: number;
  total_cursos: number;
  cursos_activos: number;
  total_inscripciones: number;
  preinscripciones_pendientes: number;
  inscripciones_mes_actual: number;
  certificados_emitidos: number;
  ingresos_totales: number;
  ingresos_mes_actual: number;
  participantes_por_tipo: any[];
  actividad_reciente: any[];
  visitas_hoy: number;
  visitas_mes: number;
}

interface Graficos {
  inscripciones_por_mes: any[];
  ingresos_por_mes: any[];
  participantes_por_tipo: any[];
}

const props = defineProps<{
  estadisticas: Estadisticas;
  graficos: Graficos;
  userThemeConfig?: {
    tema_id: number;
    tamano_fuente: number;
    alto_contraste: boolean;
    modo_automatico: boolean;
  } | null;
  usuarios?: any[];
  // Props para gestión de cursos
  cursosData?: {
    data: any[];
    links: any[];
    meta: any;
  };
  cursosFilters?: {
    search?: string;
    tutor_id?: string;
    gestion_id?: string;
    estado?: string;
  };
  tutores?: any[];
  gestiones?: any[];
  tiposParticipante?: any[];
  cursosEstadisticas?: {
    total: number;
    activos: number;
    en_progreso: number;
    con_inscripciones: number;
  };
}>();

// Estado para contenido dinámico
const activeContent = ref('dashboard');
const contentTitle = ref('Dashboard del Responsable');

// Función para cambiar contenido
const setActiveContent = (content: string, title: string) => {
  activeContent.value = content;
  contentTitle.value = title;
};

// Exponer función globalmente para que el sidebar pueda usarla
declare global {
  interface Window {
    setDashboardContent: (content: string, title: string) => void;
  }
}

window.setDashboardContent = setActiveContent;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard del Responsable',
        href: '/dashboard',
    },
];

// Configuración de tema
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

// Función para obtener descripción del contenido
const getContentDescription = () => {
  switch (activeContent.value) {
    case 'usuarios':
      return 'Gestionar usuarios del sistema CICIT';
    case 'cursos':
      return 'Administrar cursos y certificaciones';
    case 'pagos':
      return 'Gestionar pagos e inscripciones';
    case 'reportes':
      return 'Generar reportes y estadísticas';
    case 'certificados':
      return 'Gestión de certificados y diplomas';
    case 'estadisticas':
      return 'Estadísticas avanzadas del sistema';
    case 'tipos-participante':
      return 'Configurar tipos de participantes';
    case 'gestiones':
      return 'Gestionar gestiones académicas';
    default:
      return 'Gestión del sistema CICIT';
  }
};

// Aplicar configuración del tema al cargar el componente
onMounted(() => {
  applyThemeConfig();
});

// Watcher para cambios en la configuración del tema
watch(() => props.userThemeConfig, () => {
  applyThemeConfig();
}, { deep: true });

// Función para aplicar configuración de tema
const applyThemeConfig = () => {
  if (props.userThemeConfig) {
    const config = props.userThemeConfig;

    // Aplicar tema oscuro/claro
    if (config.tema_id === 2) {
      document.documentElement.classList.add('dark');
      document.body.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
      document.body.classList.remove('dark');
    }

    // Aplicar tamaño de fuente
    document.documentElement.style.fontSize = `${config.tamano_fuente}px`;

    // Aplicar alto contraste
    if (config.alto_contraste) {
      document.documentElement.classList.add('high-contrast');
      document.body.classList.add('high-contrast');
    } else {
      document.documentElement.classList.remove('high-contrast');
      document.body.classList.remove('high-contrast');
    }

    // Forzar actualización de CSS custom properties para el tema
    document.documentElement.style.setProperty('--theme-applied', 'true');
  }
};

// Formatear moneda
const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

// Formatear fecha
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
};

// Lifecycle hook para manejar redirecciones desde otras páginas
onMounted(() => {
  // Verificar si necesitamos activar una sección específica
  const targetSection = localStorage.getItem('activeDashboardSection')
  if (targetSection) {
    localStorage.removeItem('activeDashboardSection') // Limpiar para evitar problemas futuros

    switch (targetSection) {
      case 'cursos':
        setActiveContent('cursos', 'Gestión de Cursos')
        break
      case 'tipos-participante':
        setActiveContent('tipos-participante', 'Tipos de Participante')
        break
      case 'gestiones':
        setActiveContent('gestiones', 'Gestiones')
        break
      default:
        break
    }
  }
})
</script>

<template>
  <Head title="Dashboard - Responsable CICIT" />

  <CICITLayout :breadcrumbs="breadcrumbs" title="Dashboard Responsable">
    <div class="space-y-1" :class="themeClasses">
      <!-- Encabezado dinámico del Dashboard -->
      <div class="flex items-center justify-between mb-1">
        <div>
          <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ contentTitle }}</h1>
          <p class="text-gray-600 dark:text-gray-400 text-xs">
            {{ activeContent === 'dashboard' ? 'Bienvenido al panel de control del sistema CICIT' : getContentDescription() }}
          </p>
        </div>
        <!-- Botón para volver al dashboard principal si estamos en otra sección -->
        <div class="flex items-center gap-2">
          <button
            v-if="activeContent !== 'dashboard'"
            @click="setActiveContent('dashboard', 'Dashboard del Responsable')"
            class="inline-flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
          >
            ← Volver al Dashboard
          </button>
        </div>
      </div>

      <!-- Contenido dinámico -->
      <div class="transition-all duration-300 ease-in-out">
        <!-- Contenido del Dashboard Principal -->
        <div v-if="activeContent === 'dashboard'" class="space-y-1">
          <!-- Header con métricas principales -->
          <div class="grid gap-1 md:grid-cols-2 lg:grid-cols-4">
        <!-- Total Usuarios -->
        <Card class="border-l-4 border-l-blue-500">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
            <CardTitle class="text-xs font-medium">Total Usuarios</CardTitle>
            <Users class="h-3 w-3 text-muted-foreground" />
          </CardHeader>
          <CardContent class="pt-1">
            <div class="text-lg font-bold">{{ estadisticas.total_usuarios }}</div>
            <p class="text-xs text-muted-foreground">
              Usuarios activos del sistema
            </p>
          </CardContent>
        </Card>

        <!-- Total Cursos -->
        <Card class="border-l-4 border-l-green-500">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
            <CardTitle class="text-xs font-medium">Cursos Totales</CardTitle>
            <BookOpen class="h-3 w-3 text-muted-foreground" />
          </CardHeader>
          <CardContent class="pt-1">
            <div class="text-lg font-bold">{{ estadisticas.total_cursos }}</div>
            <p class="text-xs text-muted-foreground">
              <span class="text-green-600">{{ estadisticas.cursos_activos }}</span> activos actualmente
            </p>
          </CardContent>
        </Card>

        <!-- Certificados Emitidos -->
        <Card class="border-l-4 border-l-purple-500">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
            <CardTitle class="text-xs font-medium">Certificados</CardTitle>
            <FileText class="h-3 w-3 text-muted-foreground" />
          </CardHeader>
          <CardContent class="pt-1">
            <div class="text-lg font-bold">{{ estadisticas.certificados_emitidos }}</div>
            <p class="text-xs text-muted-foreground">
              Certificados emitidos totales
            </p>
          </CardContent>
        </Card>

        <!-- Ingresos Totales -->
        <Card class="border-l-4 border-l-yellow-500">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
            <CardTitle class="text-xs font-medium">Ingresos Totales</CardTitle>
            <DollarSign class="h-3 w-3 text-muted-foreground" />
          </CardHeader>
          <CardContent class="pt-1">
            <div class="text-lg font-bold">{{ formatCurrency(estadisticas.ingresos_totales) }}</div>
            <p class="text-xs text-muted-foreground">
              <span class="text-green-600">{{ formatCurrency(estadisticas.ingresos_mes_actual) }}</span> este mes
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Fila de métricas secundarias -->
      <div class="grid gap-1 md:grid-cols-2 lg:grid-cols-4">
        <!-- Inscripciones -->
        <Card class="border-l-4 border-l-indigo-500">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
            <CardTitle class="text-xs font-medium">Inscripciones</CardTitle>
            <UserCheck class="h-3 w-3 text-muted-foreground" />
          </CardHeader>
          <CardContent class="pt-1">
            <div class="text-lg font-bold">{{ estadisticas.total_inscripciones }}</div>
            <p class="text-xs text-muted-foreground">
              <span class="text-blue-600">{{ estadisticas.inscripciones_mes_actual }}</span> este mes
            </p>
          </CardContent>
        </Card>

        <!-- Preinscripciones Pendientes -->
        <Card class="border-l-4 border-l-orange-500">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
            <CardTitle class="text-xs font-medium">Pendientes</CardTitle>
            <Calendar class="h-3 w-3 text-muted-foreground" />
          </CardHeader>
          <CardContent class="pt-1">
            <div class="text-lg font-bold text-orange-600">{{ estadisticas.preinscripciones_pendientes }}</div>
            <p class="text-xs text-muted-foreground">
              Preinscripciones por revisar
            </p>
          </CardContent>
        </Card>

        <!-- Visitas Hoy -->
        <Card class="border-l-4 border-l-cyan-500">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
            <CardTitle class="text-xs font-medium">Visitas Hoy</CardTitle>
            <Eye class="h-3 w-3 text-muted-foreground" />
          </CardHeader>
          <CardContent class="pt-1">
            <div class="text-lg font-bold">{{ estadisticas.visitas_hoy }}</div>
            <p class="text-xs text-muted-foreground">
              <span class="text-green-600">{{ estadisticas.visitas_mes }}</span> este mes
            </p>
          </CardContent>
        </Card>

        <!-- Participantes -->
        <Card class="border-l-4 border-l-rose-500">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-1">
            <CardTitle class="text-xs font-medium">Participantes</CardTitle>
            <GraduationCap class="h-3 w-3 text-muted-foreground" />
          </CardHeader>
          <CardContent class="pt-1">
            <div class="text-lg font-bold">{{ estadisticas.total_participantes }}</div>
            <p class="text-xs text-muted-foreground">
              Total participantes registrados
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Contenido principal en dos columnas -->
      <div class="grid gap-1 lg:grid-cols-2">
        <!-- Participantes por Tipo -->
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="flex items-center gap-2 text-sm">
              <Users class="h-4 w-4" />
              Distribución de Participantes
            </CardTitle>
            <CardDescription class="text-xs">
              Participantes registrados por tipo
            </CardDescription>
          </CardHeader>
          <CardContent class="pt-2">
            <div class="space-y-2">
              <div v-for="tipo in (estadisticas.participantes_por_tipo || [])" :key="tipo.tipo" class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                  <span class="text-xs font-medium">{{ tipo.tipo }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-sm font-bold">{{ tipo.total }}</span>
                  <Badge variant="secondary" class="text-xs">{{ Math.round(tipo.porcentaje) }}%</Badge>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Actividad Reciente -->
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="flex items-center gap-2 text-sm">
              <Activity class="h-4 w-4" />
              Actividad Reciente
            </CardTitle>
            <CardDescription class="text-xs">
              Últimas acciones realizadas en el sistema
            </CardDescription>
          </CardHeader>
          <CardContent class="pt-2">
            <div class="space-y-2">
              <div v-for="actividad in (estadisticas.actividad_reciente || []).slice(0, 6)" :key="actividad.id" class="flex items-start gap-3">
                <div class="h-2 w-2 rounded-full bg-green-500 mt-1 flex-shrink-0"></div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs text-gray-900 truncate">{{ actividad.descripcion }}</p>
                  <p class="text-xs text-gray-500">{{ formatDate(actividad.fecha) }}</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Resumen Ejecutivo -->
      <Card>
        <CardHeader class="pb-2">
          <CardTitle class="flex items-center gap-2 text-sm">
            <TrendingUp class="h-4 w-4" />
            Resumen Ejecutivo
          </CardTitle>
          <CardDescription class="text-xs">
            Análisis general del rendimiento del CICIT
          </CardDescription>
        </CardHeader>
        <CardContent class="pt-2">
          <div class="grid gap-1 md:grid-cols-3">
            <div class="space-y-1">
              <p class="text-xs font-medium text-gray-600">Eficiencia de Conversión</p>
              <p class="text-lg font-bold">
                {{ estadisticas.total_inscripciones > 0 ? Math.round((estadisticas.certificados_emitidos / estadisticas.total_inscripciones) * 100) : 0 }}%
              </p>
              <p class="text-xs text-gray-500">De inscripción a certificación</p>
            </div>
            <div class="space-y-1">
              <p class="text-xs font-medium text-gray-600">Ingreso Promedio</p>
              <p class="text-lg font-bold">
                {{ formatCurrency(estadisticas.total_inscripciones > 0 ? estadisticas.ingresos_totales / estadisticas.total_inscripciones : 0) }}
              </p>
              <p class="text-xs text-gray-500">Por inscripción</p>
            </div>
            <div class="space-y-1">
              <p class="text-xs font-medium text-gray-600">Capacidad Utilizada</p>
              <p class="text-lg font-bold">
                {{ estadisticas.total_cursos > 0 ? Math.round((estadisticas.cursos_activos / estadisticas.total_cursos) * 100) : 0 }}%
              </p>
              <p class="text-xs text-gray-500">Cursos activos vs total</p>
            </div>
          </div>
        </CardContent>
      </Card>
        </div>

        <!-- Contenido de Gestión de Usuarios -->
        <div v-else-if="activeContent === 'usuarios'">
          <UsuariosGestion
            :usuarios="usuarios"
            :userThemeConfig="userThemeConfig || undefined"
          />
        </div>

        <!-- Contenido de otros módulos (placeholders) -->
        <div v-else-if="activeContent === 'cursos'" class="space-y-4">
          <CursosGestion
            :cursos="{ data: [], links: [], meta: {} }"
            :filters="{}"
            :tutores="[]"
            :gestiones="[]"
            :tiposParticipante="[]"
            :estadisticas="{ total: 6, activos: 6, en_progreso: 0, con_inscripciones: 0 }"
            :userThemeConfig="userThemeConfig || undefined"
          />
        </div>

        <div v-else-if="activeContent === 'pagos'" class="space-y-4">
          <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 text-center">
            <DollarSign class="h-12 w-12 text-green-600 mx-auto mb-3" />
            <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-2">Gestión de Pagos</h3>
            <p class="text-green-700 dark:text-green-300">Módulo en desarrollo - Próximamente disponible</p>
          </div>
        </div>

        <div v-else-if="activeContent === 'reportes'" class="space-y-4">
          <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-6 text-center">
            <FileText class="h-12 w-12 text-purple-600 mx-auto mb-3" />
            <h3 class="text-lg font-semibold text-purple-900 dark:text-purple-100 mb-2">Reportes y Estadísticas</h3>
            <p class="text-purple-700 dark:text-purple-300">Módulo en desarrollo - Próximamente disponible</p>
          </div>
        </div>

        <div v-else-if="activeContent === 'certificados'" class="space-y-4">
          <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6 text-center">
            <GraduationCap class="h-12 w-12 text-yellow-600 mx-auto mb-3" />
            <h3 class="text-lg font-semibold text-yellow-900 dark:text-yellow-100 mb-2">Gestión de Certificados</h3>
            <p class="text-yellow-700 dark:text-yellow-300">Módulo en desarrollo - Próximamente disponible</p>
          </div>
        </div>

        <div v-else-if="activeContent === 'estadisticas'" class="space-y-4">
          <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-6 text-center">
            <TrendingUp class="h-12 w-12 text-indigo-600 mx-auto mb-3" />
            <h3 class="text-lg font-semibold text-indigo-900 dark:text-indigo-100 mb-2">Estadísticas Avanzadas</h3>
            <p class="text-indigo-700 dark:text-indigo-300">Módulo en desarrollo - Próximamente disponible</p>
          </div>
        </div>

        <div v-else-if="activeContent === 'tipos-participante'" class="space-y-4">
          <TiposParticipanteGestion :userThemeConfig="userThemeConfig || undefined" />
        </div>

        <div v-else-if="activeContent === 'gestiones'" class="space-y-4">
          <GestionesGestion :userThemeConfig="userThemeConfig || undefined" />
        </div>

        <!-- Contenido por defecto si no coincide ningún caso -->
        <div v-else class="space-y-4">
          <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 rounded-lg p-6 text-center">
            <Activity class="h-12 w-12 text-gray-600 mx-auto mb-3" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Contenido no encontrado</h3>
            <p class="text-gray-700 dark:text-gray-300">La sección solicitada no está disponible</p>
          </div>
        </div>
      </div>
    </div>
  </CICITLayout>
</template>

<style scoped>
/* Estilos específicos para mejorar la visibilidad de los temas */
.high-contrast {
  filter: contrast(var(--contrast-boost, 1.5));
}

.dark .high-contrast {
  filter: contrast(var(--contrast-boost, 1.8)) brightness(1.1);
}

/* Animaciones mejoradas para las transiciones de tema */
* {
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

/* Mejoras para los gradientes en modo oscuro */
.dark .bg-gradient-to-r {
  opacity: 0.9;
}

/* Hover effects más notorios */
.group:hover {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.dark .group:hover {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
}

/* Mejorar contraste de texto en modo oscuro */
.dark .text-gray-600 {
  color: #d1d5db;
}

.dark .text-gray-400 {
  color: #9ca3af;
}

/* Asegurar que los botones sean visibles en todos los temas */
.bg-primary-600 {
  background-color: rgb(37 99 235);
}

.dark .bg-primary-600 {
  background-color: rgb(59 130 246);
}

.hover\:bg-primary-700:hover {
  background-color: rgb(29 78 216);
}

.dark .hover\:bg-primary-700:hover {
  background-color: rgb(37 99 235);
}

/* Mejorar visibilidad de elementos principales */
.bg-white {
  background-color: white;
}

.dark .bg-white {
  background-color: rgb(17 24 39);
}

.text-gray-900 {
  color: rgb(17 24 39);
}

.dark .text-gray-900 {
  color: rgb(243 244 246);
}
</style>
