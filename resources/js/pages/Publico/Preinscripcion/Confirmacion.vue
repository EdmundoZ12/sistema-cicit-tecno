<template>
  <div class="confirmacion-container">
    <!-- Header de confirmación -->
    <div class="text-center mb-8">
      <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
      </div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">¡Preinscripción Exitosa!</h1>
      <p class="text-lg text-gray-600 dark:text-gray-300">Tu preinscripción ha sido registrada correctamente</p>
    </div>

    <!-- ID de Preinscripción Destacado -->
    <div class="bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg p-6 mb-8 text-center shadow-lg">
      <div class="text-white">
        <h2 class="text-sm font-medium uppercase tracking-wide mb-2">Código de Preinscripción</h2>
        <div class="text-4xl font-bold mb-2">
          ID: {{ String(datos.id).padStart(6, '0') }}
        </div>
        <p class="text-yellow-100">
          <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Guarda este código para futuras consultas administrativas
        </p>
      </div>
    </div>

    <!-- Botón de descarga PDF -->
    <div class="text-center mb-8">
      <a
        :href="`/preinscripcion/${datos.id}/pdf`"
        target="_blank"
        class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 text-white text-base font-medium rounded-md transition-colors shadow-lg"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Descargar Comprobante PDF
      </a>
      <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
        Recomendamos descargar y guardar el comprobante para tus registros
      </p>
    </div>

    <!-- Información de la preinscripción -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Detalles de tu Preinscripción</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Participante</label>
          <p class="text-gray-900 dark:text-white font-medium">{{ datos.participante }}</p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Curso</label>
          <p class="text-gray-900 dark:text-white font-medium">{{ datos.curso }}</p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Instructor</label>
          <p class="text-gray-900 dark:text-white">{{ datos.tutor }}</p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha de Inicio</label>
          <p class="text-gray-900 dark:text-white">{{ datos.fecha_inicio }}</p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Tipo de Participante</label>
          <p class="text-gray-900 dark:text-white">{{ datos.tipo_participante }}</p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Precio</label>
          <p class="text-gray-900 dark:text-white font-semibold text-lg">{{ datos.precio }}</p>
        </div>
      </div>
    </div>

    <!-- Pasos siguientes -->
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 p-6 mb-8">
      <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Próximos Pasos
      </h3>
      
      <div class="space-y-3">
        <div v-for="(paso, index) in pasos_siguientes" :key="index" class="flex items-start">
          <div class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium mr-3 mt-0.5">
            {{ index + 1 }}
          </div>
          <p class="text-blue-800 dark:text-blue-200">{{ paso }}</p>
        </div>
      </div>
    </div>

    <!-- Información importante -->
    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800 p-6 mb-8">
      <h3 class="text-lg font-semibold text-yellow-900 dark:text-yellow-100 mb-3 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        Información Importante
      </h3>
      
      <ul class="space-y-2 text-yellow-800 dark:text-yellow-200">
        <li class="flex items-start">
          <span class="w-2 h-2 bg-yellow-600 rounded-full mr-3 mt-2 flex-shrink-0"></span>
          <span>Esta preinscripción debe ser confirmada mediante el pago correspondiente antes del inicio del curso.</span>
        </li>
        <li class="flex items-start">
          <span class="w-2 h-2 bg-yellow-600 rounded-full mr-3 mt-2 flex-shrink-0"></span>
          <span>Recibirás notificaciones por correo electrónico con las instrucciones de pago y detalles adicionales.</span>
        </li>
        <li class="flex items-start">
          <span class="w-2 h-2 bg-yellow-600 rounded-full mr-3 mt-2 flex-shrink-0"></span>
          <span>Tu cupo quedará reservado por un período limitado mientras completas el proceso de inscripción.</span>
        </li>
      </ul>
    </div>

    <!-- Botones de acción -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <InertiaLink
        :href="route('home')"
        class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
        </svg>
        Volver al Inicio
      </InertiaLink>
      
      <InertiaLink
        :href="route('cursos.publicos')"
        class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white text-base font-medium rounded-md transition-colors"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
        Ver Más Cursos
      </InertiaLink>
    </div>

    <!-- Información de contacto -->
    <div class="mt-12 text-center">
      <p class="text-gray-600 dark:text-gray-400 mb-2">¿Tienes preguntas sobre tu preinscripción?</p>
      <InertiaLink 
        :href="route('contacto')" 
        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
      >
        Contáctanos
      </InertiaLink>
    </div>
  </div>
</template>

<script lang="ts">
import { Link } from '@inertiajs/vue3';

interface DatosPreinscripcion {
  id: number;
  participante: string;
  curso: string;
  tutor: string;
  fecha_inicio: string;
  precio: string;
  tipo_participante: string;
}

export default {
  components: {
    InertiaLink: Link,
  },
  props: {
    datos: {
      type: Object as () => DatosPreinscripcion,
      required: true,
    },
    pasos_siguientes: {
      type: Array as () => string[],
      default: () => [
        'Recibirás un correo electrónico de confirmación con los detalles de tu preinscripción.',
        'Te contactaremos con las instrucciones de pago para completar tu inscripción.',
        'Una vez confirmado el pago, recibirás acceso completo al curso y materiales.',
        'Antes del inicio del curso, te enviaremos información sobre horarios y ubicación.',
      ],
    },
  },
};
</script>

<style scoped>
.confirmacion-container {
  max-width: 4xl;
  margin: 0 auto;
  padding: 2rem;
}

.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
