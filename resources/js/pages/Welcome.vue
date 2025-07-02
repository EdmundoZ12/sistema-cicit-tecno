<script setup lang="ts">
import CICITLayout from '@/layouts/CICITLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

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
