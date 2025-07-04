<script setup lang="ts">
import CICITLayout from '@/layouts/CICITLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Definir el tipo para los cursos
interface Curso {
    id: number;
    nombre: string;
    descripcion: string;
    duracion_horas: number;
    nivel: string;
    logo_url?: string;
    tutor: string;
    fecha_inicio: string;
    fecha_fin: string;
    cupos_disponibles: number;
    aula: string;
}

// Props
defineProps<{
    cursosDisponibles: Curso[];
}>();

// Breadcrumbs para la página de bienvenida
const breadcrumbs = [
    {
        title: 'Inicio',
        href: '/',
    },
];
</script>

<template>
    <Head title="Bienvenido a CICIT">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <CICITLayout
        :breadcrumbs="breadcrumbs"
        title="Bienvenido a CICIT"
        :show-sidebar="false"
        :show-breadcrumbs="false"
    >
        <!-- Hero Section -->
        <div class="relative bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 rounded-lg overflow-hidden">
            <div class="absolute inset-0 bg-grid-gray-100 bg-grid-16 dark:bg-grid-gray-800" style="mask-image: radial-gradient(ellipse at center, black 50%, transparent 100%);"></div>
            <div class="relative px-6 py-16 sm:px-12 sm:py-20 lg:px-16">
                <div class="max-w-4xl mx-auto text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl lg:text-6xl">
                        Centro Integral de Certificación e
                        <span class="text-blue-600 dark:text-blue-400">Innovación Tecnológica</span>
                    </h1>
                    <p class="mt-6 text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                        Plataforma de certificación y capacitación tecnológica de la Universidad Autónoma Gabriel René Moreno
                    </p>
                    <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors"
                        >
                            Ir al Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors"
                            >
                                Iniciar Sesión
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="mt-16 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div class="relative p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    Cursos de Certificación
                </h3>
                <p class="text-gray-600 dark:text-gray-300">
                    Amplia variedad de cursos tecnológicos con certificación oficial reconocida.
                </p>
            </div>

            <div class="relative p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    Calidad Garantizada
                </h3>
                <p class="text-gray-600 dark:text-gray-300">
                    Programas académicos de alta calidad respaldados por la UAGRM.
                </p>
            </div>

            <div class="relative p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    Innovación Tecnológica
                </h3>
                <p class="text-gray-600 dark:text-gray-300">
                    Mantente al día con las últimas tendencias y tecnologías del mercado.
                </p>
            </div>
        </div>

        <!-- Cursos Disponibles Section -->
        <div v-if="cursosDisponibles && cursosDisponibles.length > 0" class="mt-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    Cursos Disponibles
                </h2>
                <p class="mt-4 text-xl text-gray-600 dark:text-gray-300">
                    Descubre nuestros próximos cursos de certificación y capacítate en las últimas tecnologías
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="curso in cursosDisponibles"
                    :key="curso.id"
                    class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-shadow duration-300"
                >
                    <!-- Imagen del curso -->
                    <div class="h-48 bg-gradient-to-br from-blue-400 to-purple-500 relative">
                        <img
                            v-if="curso.logo_url"
                            :src="curso.logo_url"
                            :alt="curso.nombre"
                            class="w-full h-full object-cover"
                        />
                        <div class="absolute inset-0 bg-black bg-opacity-20"></div>
                        <div class="absolute top-4 right-4 bg-white dark:bg-gray-800 px-3 py-1 rounded-full text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ curso.nivel }}
                        </div>
                    </div>

                    <!-- Contenido del curso -->
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            {{ curso.nombre }}
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-3">
                            {{ curso.descripcion }}
                        </p>

                        <!-- Información del curso -->
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Instructor: {{ curso.tutor }}
                            </div>
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ curso.duracion_horas }} horas
                            </div>
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Inicio: {{ curso.fecha_inicio }}
                            </div>
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                {{ curso.cupos_disponibles }} cupos disponibles
                            </div>
                        </div>

                        <!-- Botón de preinscripción -->
                        <div class="flex justify-between items-center">
                            <Link
                                :href="route('preinscripcion.create', curso.id)"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white text-sm font-medium rounded-md transition-colors"
                            >
                                Preinscribirse
                            </Link>
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                Aula: {{ curso.aula }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón para ver todos los cursos -->
            <div class="text-center mt-12">
                <Link
                    :href="route('cursos.publicos')"
                    class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                    Ver Todos los Cursos
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </Link>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="mt-16 bg-blue-600 dark:bg-blue-700 rounded-lg px-6 py-12 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">
                ¿Listo para comenzar tu certificación?
            </h2>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                Únete a miles de estudiantes que ya han transformado su carrera profesional con nuestros cursos.
            </p>
            <Link
                v-if="$page.props.auth.user"
                :href="route('dashboard')"
                class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50 dark:bg-gray-100 dark:hover:bg-gray-200 transition-colors"
            >
                Ver Dashboard
            </Link>
        </div>
    </CICITLayout>
</template>
