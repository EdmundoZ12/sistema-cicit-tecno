<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-slate-900 dark:to-slate-800 p-6 md:p-10">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <div class="flex flex-col items-center gap-2 font-medium">
                        <div class="mb-1 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 dark:bg-blue-500 shadow-lg">
                            <AppLogoIcon class="size-8 fill-current text-white" />
                        </div>
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">CICIT</div>
                    </div>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium text-gray-900 dark:text-white">{{ title }}</h1>
                        <p class="text-center text-sm text-gray-600 dark:text-gray-400">{{ description }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-gray-200 dark:border-slate-700 p-8">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
