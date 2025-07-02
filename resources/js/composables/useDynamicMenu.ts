import type { CICITUser, MenuItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useDynamicMenu() {
    const page = usePage();

    const user = computed<CICITUser>(
        () => page.props.auth.user as CICITUser
    );

    const menuItems = computed<MenuItem[]>(
        () => page.props.menuItems as MenuItem[] || []
    );

    const userRole = computed(() => {
        return user.value?.tipo_participante || 'PARTICIPANTE';
    });

    const filteredMenuItems = computed(() => {
        return filterMenuByRole(menuItems.value, userRole.value);
    });

    function filterMenuByRole(items: MenuItem[], role: string): MenuItem[] {
        return items
            .filter(item => {
                if (!item.es_activo) return false;

                const allowedRoles = item.roles_permitidos.split(',').map(r => r.trim());
                return allowedRoles.includes(role) || allowedRoles.includes('ALL');
            })
            .map(item => ({
                ...item,
                children: item.children ? filterMenuByRole(item.children, role) : undefined
            }))
            .sort((a, b) => a.orden - b.orden);
    }

    const getDefaultMenuItems = (): MenuItem[] => {
        const baseItems: MenuItem[] = [
            {
                id: 1,
                nombre: 'Dashboard',
                icono: 'dashboard',
                ruta: '/dashboard',
                descripcion: 'Panel principal',
                orden: 1,
                es_activo: true,
                roles_permitidos: 'ALL'
            },
            {
                id: 2,
                nombre: 'Mi Perfil',
                icono: 'user',
                ruta: '/profile',
                descripcion: 'Configuración del perfil',
                orden: 2,
                es_activo: true,
                roles_permitidos: 'ALL'
            }
        ];

        const roleSpecificItems: Record<string, MenuItem[]> = {
            'RESPONSABLE': [
                {
                    id: 10,
                    nombre: 'Gestión de Usuarios',
                    icono: 'users',
                    ruta: '/admin/users',
                    descripcion: 'Administrar usuarios del sistema',
                    orden: 10,
                    es_activo: true,
                    roles_permitidos: 'RESPONSABLE'
                },
                {
                    id: 11,
                    nombre: 'Gestión de Cursos',
                    icono: 'book-open',
                    ruta: '/admin/courses',
                    descripcion: 'Administrar cursos disponibles',
                    orden: 11,
                    es_activo: true,
                    roles_permitidos: 'RESPONSABLE'
                },
                {
                    id: 12,
                    nombre: 'Reportes y Estadísticas',
                    icono: 'bar-chart-3',
                    ruta: '/admin/reports',
                    descripcion: 'Ver reportes del sistema',
                    orden: 12,
                    es_activo: true,
                    roles_permitidos: 'RESPONSABLE'
                },
                {
                    id: 13,
                    nombre: 'Configuración del Sitio',
                    icono: 'settings',
                    ruta: '/admin/settings',
                    descripcion: 'Configurar parámetros del sistema',
                    orden: 13,
                    es_activo: true,
                    roles_permitidos: 'RESPONSABLE'
                },
                {
                    id: 14,
                    nombre: 'Gestiones Académicas', // Cambiado para coincidir con el sidebar
                    icono: 'calendar',
                    ruta: '/responsable/gestiones',
                    descripcion: 'Administrar gestiones académicas',
                    orden: 14,
                    es_activo: true,
                    roles_permitidos: 'RESPONSABLE'
                }
            ],
            'ADMINISTRATIVO': [
                {
                    id: 20,
                    nombre: 'Gestión de Inscripciones',
                    icono: 'user-plus',
                    ruta: '/admin/registrations',
                    descripcion: 'Administrar inscripciones',
                    orden: 20,
                    es_activo: true,
                    roles_permitidos: 'ADMINISTRATIVO,RESPONSABLE'
                },
                {
                    id: 21,
                    nombre: 'Gestión de Pagos',
                    icono: 'credit-card',
                    ruta: '/admin/payments',
                    descripcion: 'Administrar pagos y facturación',
                    orden: 21,
                    es_activo: true,
                    roles_permitidos: 'ADMINISTRATIVO,RESPONSABLE'
                },
                {
                    id: 22,
                    nombre: 'Certificados',
                    icono: 'award',
                    ruta: '/admin/certificates',
                    descripcion: 'Gestionar certificados',
                    orden: 22,
                    es_activo: true,
                    roles_permitidos: 'ADMINISTRATIVO,RESPONSABLE'
                }
            ],
            'TUTOR': [
                {
                    id: 30,
                    nombre: 'Mis Cursos',
                    icono: 'book',
                    ruta: '/tutor/courses',
                    descripcion: 'Cursos que imparto',
                    orden: 30,
                    es_activo: true,
                    roles_permitidos: 'TUTOR,RESPONSABLE'
                },
                {
                    id: 31,
                    nombre: 'Gestión de Tareas',
                    icono: 'clipboard-list',
                    ruta: '/tutor/assignments',
                    descripcion: 'Crear y calificar tareas',
                    orden: 31,
                    es_activo: true,
                    roles_permitidos: 'TUTOR,RESPONSABLE'
                },
                {
                    id: 32,
                    nombre: 'Control de Asistencia',
                    icono: 'calendar-check',
                    ruta: '/tutor/attendance',
                    descripcion: 'Registrar asistencia de estudiantes',
                    orden: 32,
                    es_activo: true,
                    roles_permitidos: 'TUTOR,RESPONSABLE'
                }
            ],
            'PARTICIPANTE': [
                {
                    id: 40,
                    nombre: 'Mis Inscripciones',
                    icono: 'bookmark',
                    ruta: '/student/enrollments',
                    descripcion: 'Ver mis cursos inscritos',
                    orden: 40,
                    es_activo: true,
                    roles_permitidos: 'ALL'
                },
                {
                    id: 41,
                    nombre: 'Catálogo de Cursos',
                    icono: 'search',
                    ruta: '/courses/catalog',
                    descripcion: 'Explorar cursos disponibles',
                    orden: 41,
                    es_activo: true,
                    roles_permitidos: 'ALL'
                },
                {
                    id: 42,
                    nombre: 'Mis Certificados',
                    icono: 'award',
                    ruta: '/student/certificates',
                    descripcion: 'Ver certificados obtenidos',
                    orden: 42,
                    es_activo: true,
                    roles_permitidos: 'ALL'
                }
            ]
        };

        const userRoleItems = roleSpecificItems[userRole.value] || roleSpecificItems['PARTICIPANTE'];
        return [...baseItems, ...userRoleItems];
    };

    return {
        user,
        userRole,
        menuItems: computed(() =>
            filteredMenuItems.value.length > 0
                ? filteredMenuItems.value
                : getDefaultMenuItems()
        )
    };
}
