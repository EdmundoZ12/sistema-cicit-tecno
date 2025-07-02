<template>
  <CICITLayout>
    <Head title="Sobre Nosotros - CICIT" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <h1 class="text-3xl font-bold mb-6">Sobre CICIT</h1>

            <div class="prose max-w-none">
              <p class="text-lg text-gray-700 mb-6">
                El Centro Integral de Certificación e Innovación Tecnológica (CICIT) es una unidad especializada
                de la Universidad Autónoma Gabriel René Moreno (UAGRM) dedicada a la formación, capacitación y
                certificación en tecnologías de la información.
              </p>

              <h2 class="text-2xl font-semibold mb-4">Nuestra Misión</h2>
              <p class="mb-6">
                Formar profesionales competentes en tecnologías de la información mediante programas de certificación
                de alta calidad, contribuyendo al desarrollo tecnológico de la región.
              </p>

              <h2 class="text-2xl font-semibold mb-4">Nuestra Visión</h2>
              <p class="mb-6">
                Ser el centro de referencia en certificación tecnológica en el oriente boliviano, reconocido por
                la excelencia académica y la innovación en metodologías de enseñanza.
              </p>

              <h2 class="text-2xl font-semibold mb-4">Valores</h2>
              <ul class="list-disc pl-6 mb-6">
                <li>Excelencia académica</li>
                <li>Innovación tecnológica</li>
                <li>Compromiso social</li>
                <li>Integridad profesional</li>
                <li>Mejora continua</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CICITLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import CICITLayout from '@/layouts/CICITLayout.vue'
</script>
