<template>
  <div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Cursos Disponibles</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="curso in cursos" :key="curso.id" class="card">
        <div class="card-body">
          <h2 class="card-title">{{ curso.nombre }}</h2>
          <p>{{ curso.descripcion }}</p>
          <p><strong>Fecha de inicio:</strong> {{ curso.fecha_inicio }}</p>
          <p><strong>Cupos disponibles:</strong> {{ curso.cupos_disponibles }}</p>
          <button @click="verDetalles(curso.id)" class="btn btn-primary">Ver Detalles</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { router } from '@inertiajs/vue3';

interface Curso {
  id: number;
  nombre: string;
  descripcion: string;
  fecha_inicio: string;
  cupos_disponibles: number;
}

export default {
  data() {
    return {
      cursos: [] as Curso[],
    };
  },
  mounted() {
    this.obtenerCursos();
  },
  methods: {
    async obtenerCursos() {
      try {
        const response = await fetch('/api/cursos-disponibles');
        this.cursos = await response.json();
      } catch (error) {
        console.error('Error al obtener los cursos:', error);
      }
    },
    verDetalles(cursoId: number) {
      router.visit(`/preinscripcion/${cursoId}`);
    },
  },
};
</script>

<style scoped>
.card {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 16px;
  background-color: #fff;
}
.card-title {
  font-size: 1.25rem;
  font-weight: bold;
}
.btn {
  display: inline-block;
  padding: 8px 16px;
  background-color: #007bff;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.btn:hover {
  background-color: #0056b3;
}
</style>
