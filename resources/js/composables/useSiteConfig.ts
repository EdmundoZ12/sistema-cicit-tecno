import type { SiteConfiguration, SiteStats } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useSiteConfig() {
    const page = usePage();

    const siteConfig = computed<SiteConfiguration | undefined>(
        () => page.props.siteConfig as SiteConfiguration | undefined
    );

    const siteStats = computed<SiteStats | undefined>(
        () => page.props.siteStats as SiteStats | undefined
    );

    const defaultConfig: SiteConfiguration = {
        id: 1,
        nombre_sitio: 'CICIT - UAGRM',
        logo_url: '/images/cicit-logo.png',
        descripcion_sitio: 'Centro Integral de Certificación e Innovación Tecnológica',
        keywords_sitio: 'certificación, tecnología, cursos, UAGRM',
        meta_description: 'Plataforma de certificación y capacitación tecnológica de la UAGRM',
        color_primario: '#1e40af',
        color_secundario: '#3b82f6',
        contador_visitas_activo: true,
        mantenimiento_activo: false,
        telefono_contacto: '+591 3 336-4000',
        email_contacto: 'cicit@uagrm.edu.bo',
        direccion_fisica: 'Universidad Autónoma Gabriel René Moreno, Santa Cruz, Bolivia',
        redes_sociales: {
            facebook: 'https://facebook.com/uagrm',
            twitter: 'https://twitter.com/uagrm',
            instagram: 'https://instagram.com/uagrm',
            linkedin: 'https://linkedin.com/company/uagrm'
        }
    };

    const defaultStats: SiteStats = {
        total_visits: 0,
        unique_visitors: 0,
        total_users: 0,
        total_courses: 0,
        total_registrations: 0,
        active_courses: 0
    };

    return {
        siteConfig: computed(() => siteConfig.value || defaultConfig),
        siteStats: computed(() => siteStats.value || defaultStats)
    };
}
