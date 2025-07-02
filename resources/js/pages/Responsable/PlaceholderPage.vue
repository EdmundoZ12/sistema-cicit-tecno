<script setup lang="ts">
import CICITLayout from '@/layouts/CICITLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Construction } from 'lucide-vue-next';

defineProps<{
  title: string;
  description: string;
  module: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'En Desarrollo', href: '#' },
];
</script>

<template>
  <CICITLayout :breadcrumbs="breadcrumbs">
    <Head :title="title" />

    <div class="flex items-center justify-center min-h-[60vh]">
      <Card class="w-full max-w-md">
        <CardHeader class="text-center">
          <div class="mx-auto mb-4 h-12 w-12 rounded-full bg-orange-100 dark:bg-orange-900 flex items-center justify-center">
            <Construction class="h-6 w-6 text-orange-600 dark:text-orange-400" />
          </div>
          <CardTitle class="text-2xl">{{ title }}</CardTitle>
          <CardDescription>{{ description }}</CardDescription>
        </CardHeader>
        <CardContent class="text-center space-y-4">
          <Badge variant="secondary" class="mb-4">
            Módulo: {{ module }}
          </Badge>

          <p class="text-sm text-gray-600 dark:text-gray-400">
            Esta funcionalidad se encuentra en desarrollo y estará disponible próximamente.
          </p>

          <div class="pt-4">
            <Button asChild>
              <Link :href="route('dashboard')">
                <ArrowLeft class="h-4 w-4 mr-2" />
                Volver al Dashboard
              </Link>
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </CICITLayout>
</template>
