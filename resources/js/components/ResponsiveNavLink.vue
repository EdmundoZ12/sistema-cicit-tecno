<template>
  <Link
    :href="href"
    :class="classes"
  >
    <slot />
  </Link>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

interface Props {
  href: string
  active?: boolean
}

const props = defineProps<Props>()

const classes = computed(() =>
  props.active
    ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-blue-400 dark:border-blue-600 text-start text-base font-medium text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/50 focus:outline-none focus:text-blue-800 dark:focus:text-blue-200 focus:bg-blue-100 dark:focus:bg-blue-900/75 focus:border-blue-700 dark:focus:border-blue-300 transition duration-150 ease-in-out'
    : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 focus:border-gray-300 dark:focus:border-gray-600 transition duration-150 ease-in-out'
)
</script>
