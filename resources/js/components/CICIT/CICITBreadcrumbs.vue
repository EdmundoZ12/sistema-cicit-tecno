<template>
  <nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2">
      <li>
        <div>
          <Link :href="route('dashboard')" class="text-gray-400 hover:text-gray-500">
            <HomeIcon class="h-5 w-5 flex-shrink-0" />
            <span class="sr-only">Dashboard</span>
          </Link>
        </div>
      </li>

      <li v-for="(item, index) in items" :key="index">
        <div class="flex items-center">
          <ChevronRightIcon class="h-5 w-5 flex-shrink-0 text-gray-400" />

          <Link
            v-if="item.href && index < items.length - 1"
            :href="item.href"
            class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700"
          >
            {{ item.title }}
          </Link>

          <span
            v-else
            class="ml-2 text-sm font-medium text-gray-900"
            aria-current="page"
          >
            {{ item.title }}
          </span>
        </div>
      </li>
    </ol>
  </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { HomeIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

interface BreadcrumbItem {
  title: string
  href?: string
}

interface Props {
  items: BreadcrumbItem[]
}

defineProps<Props>()
</script>
