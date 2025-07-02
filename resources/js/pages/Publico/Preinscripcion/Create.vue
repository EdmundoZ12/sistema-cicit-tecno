<template>
  <div class="preinscripcion-container">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Preinscripción al Curso</h1>

    <!-- Información del curso seleccionado -->
    <div v-if="cursoSeleccionado" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
      <div class="flex items-start space-x-4">
        <img
          v-if="cursoSeleccionado.logo_url"
          :src="cursoSeleccionado.logo_url"
          :alt="cursoSeleccionado.nombre"
          class="w-20 h-20 object-cover rounded-lg"
        />
        <div class="flex-1">
          <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">{{ cursoSeleccionado.nombre }}</h2>
          <p class="text-gray-600 dark:text-gray-300 mb-4">{{ cursoSeleccionado.descripcion }}</p>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="flex items-center text-gray-500 dark:text-gray-400">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <strong>Duración:</strong> {{ cursoSeleccionado.duracion_horas }} horas
            </div>
            <div class="flex items-center text-gray-500 dark:text-gray-400">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <strong>Nivel:</strong> {{ cursoSeleccionado.nivel }}
            </div>
            <div class="flex items-center text-gray-500 dark:text-gray-400">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"></path>
              </svg>
              <strong>Fecha de inicio:</strong> {{ cursoSeleccionado.fecha_inicio }}
            </div>
            <div class="flex items-center text-gray-500 dark:text-gray-400">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
              <strong>Cupos disponibles:</strong> {{ cursoSeleccionado.cupos_disponibles }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Formulario de preinscripción -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Datos del Participante</h3>
      
      <form @submit.prevent="submitPreinscripcion">
        <!-- Error general -->
        <div v-if="$page.props.errors.general" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
          <p class="text-red-600 dark:text-red-400">{{ $page.props.errors.general }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Carnet -->
          <div>
            <label for="carnet" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Número de Carnet <span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="carnet"
              v-model="form.carnet"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              :class="{ 'border-red-500': $page.props.errors.carnet }"
              required
            />
            <p v-if="$page.props.errors.carnet" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ $page.props.errors.carnet[0] }}
            </p>
          </div>

          <!-- Nombre -->
          <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Nombre <span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="nombre"
              v-model="form.nombre"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              :class="{ 'border-red-500': $page.props.errors.nombre }"
              required
            />
            <p v-if="$page.props.errors.nombre" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ $page.props.errors.nombre[0] }}
            </p>
          </div>

          <!-- Apellido -->
          <div>
            <label for="apellido" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Apellido <span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="apellido"
              v-model="form.apellido"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              :class="{ 'border-red-500': $page.props.errors.apellido }"
              required
            />
            <p v-if="$page.props.errors.apellido" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ $page.props.errors.apellido[0] }}
            </p>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Correo Electrónico <span class="text-red-500">*</span>
            </label>
            <input
              type="email"
              id="email"
              v-model="form.email"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              :class="{ 'border-red-500': $page.props.errors.email }"
              required
            />
            <p v-if="$page.props.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ $page.props.errors.email[0] }}
            </p>
          </div>

          <!-- Teléfono -->
          <div>
            <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Teléfono
            </label>
            <input
              type="tel"
              id="telefono"
              v-model="form.telefono"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              :class="{ 'border-red-500': $page.props.errors.telefono }"
            />
            <p v-if="$page.props.errors.telefono" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ $page.props.errors.telefono[0] }}
            </p>
          </div>

          <!-- Universidad -->
          <div>
            <label for="universidad" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Universidad
            </label>
            <input
              type="text"
              id="universidad"
              v-model="form.universidad"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              :class="{ 'border-red-500': $page.props.errors.universidad }"
            />
            <p v-if="$page.props.errors.universidad" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ $page.props.errors.universidad[0] }}
            </p>
          </div>
        </div>

        <!-- Tipo de participante -->
        <div class="mt-6">
          <label for="tipo_participante_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Tipo de Participante <span class="text-red-500">*</span>
          </label>
          <select
            id="tipo_participante_id"
            v-model="form.tipo_participante_id"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
            :class="{ 'border-red-500': $page.props.errors.tipo_participante_id }"
            required
          >
            <option value="">Seleccione su tipo de participante</option>
            <option v-for="tipo in tiposParticipante" :key="tipo.id" :value="tipo.id">
              {{ tipo.descripcion }}
            </option>
          </select>
          <p v-if="$page.props.errors.tipo_participante_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ $page.props.errors.tipo_participante_id[0] }}
          </p>
        </div>

        <!-- Términos y condiciones -->
        <div class="mt-6 space-y-4">
          <div class="flex items-start">
            <input
              type="checkbox"
              id="acepta_terminos"
              v-model="form.acepta_terminos"
              class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              :class="{ 'border-red-500': $page.props.errors.acepta_terminos }"
              required
            />
            <label for="acepta_terminos" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
              Acepto los <a href="#" class="text-blue-600 hover:text-blue-800">términos y condiciones</a> del programa de certificación. <span class="text-red-500">*</span>
            </label>
          </div>
          <p v-if="$page.props.errors.acepta_terminos" class="text-sm text-red-600 dark:text-red-400">
            {{ $page.props.errors.acepta_terminos[0] }}
          </p>

          <div class="flex items-start">
            <input
              type="checkbox"
              id="acepta_datos"
              v-model="form.acepta_datos"
              class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              :class="{ 'border-red-500': $page.props.errors.acepta_datos }"
              required
            />
            <label for="acepta_datos" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
              Autorizo el tratamiento de mis datos personales conforme a la <a href="#" class="text-blue-600 hover:text-blue-800">política de privacidad</a>. <span class="text-red-500">*</span>
            </label>
          </div>
          <p v-if="$page.props.errors.acepta_datos" class="text-sm text-red-600 dark:text-red-400">
            {{ $page.props.errors.acepta_datos[0] }}
          </p>
        </div>

        <!-- Botones -->
        <div class="mt-8 flex justify-between">
          <button
            type="button"
            @click="$inertia.visit('/')"
            class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            Cancelar
          </button>
          
          <button
            type="submit"
            :disabled="processing"
            class="px-8 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="processing">Procesando...</span>
            <span v-else>Preinscribirse</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
export default {
  props: {
    cursoSeleccionado: Object,
    tiposParticipante: Array as () => { id: number; codigo: string; descripcion: string }[],
  },
  data() {
    return {
      processing: false,
      form: {
        carnet: '',
        nombre: '',
        apellido: '',
        email: '',
        telefono: '',
        universidad: '',
        tipo_participante_id: '',
        curso_id: this.cursoSeleccionado?.id || '',
        acepta_terminos: false,
        acepta_datos: false,
      },
    };
  },
  methods: {
    submitPreinscripcion() {
      this.processing = true;
      
      // Lógica para enviar el formulario al backend
      this.$inertia.post('/preinscripcion', this.form, {
        onSuccess: () => {
          this.processing = false;
        },
        onError: (errors: Record<string, string[]>) => {
          this.processing = false;
          console.error(errors);
        },
        onFinish: () => {
          this.processing = false;
        },
      });
    },
  },
};
</script>

<style scoped>
.preinscripcion-container {
  max-width: 4xl;
  margin: 0 auto;
  padding: 2rem;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Mejoras en el focus de los elementos de formulario */
input:focus, select:focus, textarea:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Animaciones suaves */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
