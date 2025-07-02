<template>
  <CICITLayout>
    <Head title="Cursos Públicos - CICIT" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <h1 class="text-3xl font-bold mb-6">Cursos Disponibles</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div class="bg-blue-50 p-6 rounded-lg">
                <h3 class="text-xl font-semibold mb-3">Programación Web</h3>
                <p class="text-gray-600 mb-4">Aprende desarrollo web moderno con HTML, CSS, JavaScript y frameworks.</p>
                <div class="text-sm text-blue-600">
                  <p>Duración: 40 horas</p>
                  <p>Modalidad: Presencial</p>
                </div>
              </div>

              <div class="bg-green-50 p-6 rounded-lg">
                <h3 class="text-xl font-semibold mb-3">Base de Datos</h3>
                <p class="text-gray-600 mb-4">Diseño y administración de bases de datos relacionales.</p>
                <div class="text-sm text-green-600">
                  <p>Duración: 30 horas</p>
                  <p>Modalidad: Virtual</p>
                </div>
              </div>

              <div class="bg-purple-50 p-6 rounded-lg">
                <h3 class="text-xl font-semibold mb-3">Inteligencia Artificial</h3>
                <p class="text-gray-600 mb-4">Introducción a IA, Machine Learning y Python.</p>
                <div class="text-sm text-purple-600">
                  <p>Duración: 50 horas</p>
                  <p>Modalidad: Híbrida</p>
                </div>
              </div>
            </div>

            <div class="mt-8 text-center">
              <p class="text-gray-600">¿Interesado en nuestros cursos?</p>
              <Link :href="route('contacto')" class="inline-block mt-2 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Contáctanos
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CICITLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import CICITLayout from '@/layouts/CICITLayout.vue'
</script>
