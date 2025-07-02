import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

// CICIT specific types
export interface CICITUser extends User {
    nombre?: string;
    apellido?: string;
    rol?: string;
    tipo_participante?: string;
    configuracion_usuario?: UserConfiguration;
    roles?: string[];
}

// User interface compatible with component props
export interface ComponentUser {
    id: number;
    name?: string; // From base User interface
    nombre?: string;
    apellido?: string;
    rol?: string;
    email: string;
}

export interface UserConfiguration {
    id: number;
    user_id: number;
    tema_favorito: string;
    tamanio_fuente: string;
    alto_contraste: boolean;
    modo_auto_dia_noche: boolean;
    idioma_preferido: string;
    mostrar_animaciones: boolean;
    mostrar_notificaciones: boolean;
}

export interface MenuItem {
    id: number;
    nombre: string;
    titulo?: string; // Alias for compatibility
    icono: string;
    ruta: string;
    descripcion?: string;
    orden: number;
    es_activo: boolean;
    activo?: boolean; // Alias for compatibility
    parent_id?: number;
    padre_id?: number; // Alias for compatibility
    rol?: string; // Add rol for compatibility
    roles_permitidos: string;
    children?: MenuItem[];
    hijos?: MenuItem[]; // Alias for compatibility
    action_type?: 'navigation' | 'content'; // Tipo de acción: navegación o contenido dinámico
    content_key?: string; // Clave para identificar el contenido dinámico
}

export interface SiteConfiguration {
    id: number;
    nombre_sitio: string;
    logo_url: string;
    descripcion_sitio: string;
    keywords_sitio: string;
    meta_description: string;
    color_primario: string;
    color_secundario: string;
    contador_visitas_activo: boolean;
    mantenimiento_activo: boolean;
    mensaje_mantenimiento?: string;
    telefono_contacto?: string;
    email_contacto?: string;
    direccion_fisica?: string;
    redes_sociales?: Record<string, string>;
}

export interface SiteStats {
    total_visits: number;
    unique_visitors: number;
    total_users: number;
    total_courses: number;
    total_registrations: number;
    active_courses: number;
}

export interface SearchResult {
    id: number;
    type: 'course' | 'user' | 'content';
    title: string;
    description?: string;
    url: string;
    relevance: number;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote?: { message: string; author: string };
    auth: Auth & { user: CICITUser };
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    // CICIT specific props
    menuItems?: MenuItem[];
    siteConfig?: SiteConfiguration;
    siteStats?: SiteStats;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
        info?: string;
    };
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
