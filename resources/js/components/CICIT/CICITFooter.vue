<template>
  <footer class="bg-gray-50 border-t border-gray-200">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Información CICIT -->
        <div class="md:col-span-2">
          <div class="flex items-center space-x-3 mb-4">
            <img
              class="h-8 w-auto"
              src="/images/logos/cicit-logo.svg"
              alt="CICIT"
              @error="handleLogoError"
            >
            <div>
              <h3 class="text-lg font-semibold text-gray-900">CICIT</h3>
              <p class="text-sm text-gray-600">Centro Integral de Certificación e Innovación Tecnológica</p>
            </div>
          </div>
          <p class="text-sm text-gray-600 mb-2">
            Universidad Autónoma Gabriel René Moreno (UAGRM)
          </p>
          <p class="text-sm text-gray-600">
            Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones
          </p>
        </div>

        <!-- Enlaces rápidos -->
        <div>
          <h4 class="text-sm font-semibold text-gray-900 mb-3">Enlaces Rápidos</h4>
          <ul class="space-y-2 text-sm">
            <li>
              <Link :href="route('cursos.publicos')" class="text-gray-600 hover:text-primary-600">
                Cursos Disponibles
              </Link>
            </li>
            <li>
              <Link :href="route('certificados.verificar')" class="text-gray-600 hover:text-primary-600">
                Verificar Certificado
              </Link>
            </li>
            <li>
              <Link :href="route('contacto')" class="text-gray-600 hover:text-primary-600">
                Contacto
              </Link>
            </li>
            <li>
              <Link :href="route('acerca')" class="text-gray-600 hover:text-primary-600">
                Acerca del CICIT
              </Link>
            </li>
          </ul>
        </div>

        <!-- Estadísticas y contador -->
        <div>
          <h4 class="text-sm font-semibold text-gray-900 mb-3">Estadísticas del Sitio</h4>
          <div class="space-y-2 text-sm text-gray-600">
            <!-- Contador de visitas de la página actual -->
            <div class="flex items-center space-x-2">
              <EyeIcon class="h-4 w-4" />
              <span>Esta página: {{ formatNumber(visitCount) }} visitas</span>
            </div>

            <!-- Estadísticas generales -->
            <div class="flex items-center space-x-2">
              <UsersIcon class="h-4 w-4" />
              <span>Total participantes: {{ formatNumber(totalParticipants) }}</span>
            </div>

            <div class="flex items-center space-x-2">
              <AcademicCapIcon class="h-4 w-4" />
              <span>Certificados emitidos: {{ formatNumber(totalCertificates) }}</span>
            </div>

            <div class="flex items-center space-x-2">
              <BookOpenIcon class="h-4 w-4" />
              <span>Cursos activos: {{ formatNumber(activeCourses) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer inferior -->
      <div class="mt-8 pt-6 border-t border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <!-- Copyright y versión -->
          <div class="text-sm text-gray-500">
            <p>© {{ currentYear }} CICIT - UAGRM. Todos los derechos reservados.</p>
            <p class="mt-1">
              Sistema v{{ systemVersion }} |
              Desarrollado por {{ developedBy }} |
              <button
                @click="showSystemInfo = !showSystemInfo"
                class="text-primary-600 hover:text-primary-500 underline"
              >
                Info del Sistema
              </button>
            </p>
          </div>

          <!-- Redes sociales y contacto -->
          <div class="mt-4 md:mt-0 flex items-center space-x-4">
            <span class="text-sm text-gray-500">Síguenos:</span>
            <a
              v-for="social in socialLinks"
              :key="social.name"
              :href="social.url"
              target="_blank"
              rel="noopener noreferrer"
              class="text-gray-400 hover:text-primary-500"
            >
              <span class="sr-only">{{ social.name }}</span>
              <component :is="social.icon" class="h-5 w-5" />
            </a>
          </div>
        </div>

        <!-- Panel de información del sistema (colapsable) -->
        <div v-if="showSystemInfo" class="mt-4 p-3 bg-gray-100 rounded-md text-xs text-gray-600">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
              <strong>Página actual:</strong><br>
              {{ pagePath }}
            </div>
            <div>
              <strong>Usuario:</strong><br>
              {{ currentUser || 'Invitado' }}
            </div>
            <div>
              <strong>Última actualización:</strong><br>
              {{ lastUpdated }}
            </div>
            <div>
              <strong>Tiempo de sesión:</strong><br>
              {{ sessionTime }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  EyeIcon,
  UsersIcon,
  AcademicCapIcon,
  BookOpenIcon
} from '@heroicons/vue/24/outline'

interface Props {
  visitCount: number
  pagePath: string
}

defineProps<Props>()
const page = usePage()

const showSystemInfo = ref(false)
const totalParticipants = ref(0)
const totalCertificates = ref(0)
const activeCourses = ref(0)
const sessionStartTime = ref(Date.now())

// Información del sistema
const currentYear = new Date().getFullYear()
const systemVersion = ref('1.0.0')
const developedBy = ref('Grupo 7SA - Tecnología Web')
const lastUpdated = ref(new Date().toLocaleDateString('es-ES'))

// Usuario actual
const currentUser = computed(() => {
  const user = page.props.auth?.user as any
  return user ? `${user.nombre} ${user.apellido}` : null
})

// Tiempo de sesión
const sessionTime = computed(() => {
  const diff = Date.now() - sessionStartTime.value
  const minutes = Math.floor(diff / 60000)
  return `${minutes} min`
})

// Redes sociales del CICIT
const socialLinks = [
  {
    name: 'Facebook',
    url: 'https://facebook.com/CICIT.UAGRM',
    icon: 'svg' // Aquí irían los iconos de redes sociales
  },
  {
    name: 'Instagram',
    url: 'https://instagram.com/cicit_uagrm',
    icon: 'svg'
  },
  {
    name: 'LinkedIn',
    url: 'https://linkedin.com/company/cicit-uagrm',
    icon: 'svg'
  }
]

// Formatear números
const formatNumber = (num: number): string => {
  return num.toLocaleString('es-ES')
}

// Cargar estadísticas generales
const loadGeneralStats = async () => {
  try {
    const response = await fetch('/api/estadisticas/generales')
    const data = await response.json()

    totalParticipants.value = data.total_participantes || 0
    totalCertificates.value = data.certificados_emitidos || 0
    activeCourses.value = data.cursos_activos || 0
  } catch (error) {
    console.error('Error cargando estadísticas:', error)
  }
}

// Cargar configuración del sitio
const loadSiteConfig = async () => {
  try {
    const response = await fetch('/api/configuracion/sistema')
    const data = await response.json()

    systemVersion.value = data.version_sistema || '1.0.0'
    developedBy.value = data.desarrollado_por || 'Grupo 7SA - Tecnología Web'
  } catch (error) {
    console.error('Error cargando configuración:', error)
  }
}

const handleLogoError = (event: Event) => {
  const target = event.target as HTMLImageElement
  target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiByeD0iNiIgZmlsbD0iIzM3NEJGRiIvPgo8dGV4dCB4PSIxNiIgeT0iMjAiIGZvbnQtZmFtaWx5PSJzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmb250LXdlaWdodD0iYm9sZCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiPkM8L3RleHQ+Cjwvc3ZnPgo='
}

onMounted(() => {
  loadGeneralStats()
  loadSiteConfig()
})
</script>
