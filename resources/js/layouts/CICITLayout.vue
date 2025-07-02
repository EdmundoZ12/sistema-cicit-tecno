<script setup lang="ts">
import CICITBreadcrumbs from '@/components/CICIT/CICITBreadcrumbs.vue';
import CICITFooter from '@/components/CICIT/CICITFooter.vue';
import CICITHeader from '@/components/CICIT/CICITHeader.vue';
import CICITSidebar from '@/components/CICIT/CICITSidebar.vue';
import { useDynamicMenu } from '@/composables/useDynamicMenu';
import { useSiteConfig } from '@/composables/useSiteConfig';
import type { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { onMounted, provide, ref } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
    showSidebar?: boolean;
    showHeader?: boolean;
    showFooter?: boolean;
    showBreadcrumbs?: boolean;
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    showSidebar: true,
    showHeader: true,
    showFooter: true,
    showBreadcrumbs: true,
});

// Composables
const { user, userRole, menuItems } = useDynamicMenu();
const { siteConfig, siteStats } = useSiteConfig();
const page = usePage();

// State
const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
const visitCount = ref(0);

// Provide global state
provide('siteConfig', siteConfig);
provide('siteStats', siteStats);
provide('user', user);
provide('userRole', userRole);

// Methods
function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value;
}

function toggleSidebarCollapse() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
}

function closeSidebar() {
    sidebarOpen.value = false;
}

// Manejar búsquedas
const handleSearch = async (searchTerm: string) => {
    if (searchTerm.trim()) {
        window.location.href = `/buscar?q=${encodeURIComponent(searchTerm)}`;
    }
};

// Obtener contador de visitas para la página actual
const fetchVisitCount = async () => {
    try {
        const response = await fetch(`/api/visitas/contador${page.url}`);
        const data = await response.json();
        visitCount.value = data.count;
    } catch (error) {
        console.error('Error cargando contador:', error);
        visitCount.value = siteStats.value.total_visits || 0;
    }
};

onMounted(() => {
    fetchVisitCount();

    // Registrar visita de la página
    fetch('/api/visitas/registrar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            pagina: page.url,
            titulo: props.title
        })
    }).catch(console.error);
});
</script>

<template>
    <div class="min-h-screen bg-background dark:bg-gray-900 grid grid-rows-[auto_1fr_auto]">
        <!-- Header CICIT -->
        <CICITHeader
            v-if="showHeader"
            :user="user"
            :site-config="siteConfig"
            :search-enabled="true"
            @search="handleSearch"
            @toggle-sidebar="toggleSidebar"
            class="relative z-40"
        />

        <!-- Main Layout con Sidebar -->
        <div class="flex overflow-hidden min-h-0">
            <!-- Sidebar con menú dinámico -->
            <CICITSidebar
                v-if="showSidebar"
                :menu-items="menuItems"
                :user="user"
                :user-role="userRole"
                :collapsed="sidebarCollapsed"
                :open="sidebarOpen"
                @toggle="toggleSidebarCollapse"
                @close="closeSidebar"
                class="hidden md:block"
            />

            <!-- Sidebar móvil con overlay -->
            <div v-if="showSidebar" class="md:hidden">
                <!-- Overlay para móvil -->
                <div
                    v-if="sidebarOpen"
                    class="fixed inset-0 z-40 bg-black bg-opacity-50"
                    @click="closeSidebar"
                />

                <!-- Sidebar móvil -->
                <div
                    :class="[
                        'fixed top-0 left-0 z-50 w-64 h-full bg-white dark:bg-gray-900 transform transition-transform duration-300 ease-in-out',
                        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                    ]"
                >
                    <CICITSidebar
                        :menu-items="menuItems"
                        :user="user"
                        :user-role="userRole"
                        :collapsed="false"
                        :open="true"
                        @toggle="toggleSidebarCollapse"
                        @close="closeSidebar"
                    />
                </div>
            </div>

            <!-- Contenido principal -->
            <main class="flex-1 overflow-auto min-w-0">
                <!-- Breadcrumbs -->
                <div
                    v-if="showBreadcrumbs && breadcrumbs.length > 0"
                    class="border-b bg-white dark:bg-gray-800 px-2 py-1 border-gray-200 dark:border-gray-700"
                >
                    <CICITBreadcrumbs :items="breadcrumbs" />
                </div>

                <!-- Contenido de la página -->
                <div class="p-2 bg-gray-50 dark:bg-gray-900 min-h-full">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Footer con contador de visitas -->
        <CICITFooter
            v-if="showFooter"
            :site-config="siteConfig"
            :site-stats="siteStats"
            :visit-count="visitCount"
            :page-path="page.url"
            class="relative z-40"
        />
    </div>
</template>
