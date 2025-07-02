# Flujo de Preinscripción Pública - CICIT

## Descripción General

Este documento describe la implementación del flujo de preinscripción pública para el sistema CICIT (Centro Integral de Certificación e Innovación Tecnológica) de la Universidad Autónoma Gabriel René Moreno.

## Características Implementadas

### 1. Página Principal (`/`)
- **Archivo**: `resources/js/pages/Welcome.vue`
- **Funcionalidad**: Muestra los cursos activos en formato de cards
- **Características**:
  - Diseño responsivo con Tailwind CSS
  - Cards con información básica del curso
  - Botón "Ver Detalles" que redirige al formulario de preinscripción
  - Filtrado automático de cursos activos y disponibles

### 2. Página de Cursos (`/cursos`)
- **Archivo**: `resources/js/pages/Publico/Cursos.vue`
- **Funcionalidad**: Vista pública de todos los cursos disponibles
- **Características**:
  - Lista completa de cursos activos
  - Información detallada de cada curso
  - Sin sidebar administrativo (vista pública)
  - Navegación directa a preinscripción

### 3. Formulario de Preinscripción (`/preinscripcion/{curso?}`)
- **Archivo**: `resources/js/pages/Publico/Preinscripcion/Create.vue`
- **Controlador**: `app/Http/Controllers/Publico/PreinscripcionPublicaController.php`
- **Funcionalidad**: Formulario de preinscripción sin autenticación
- **Características**:
  - Información del curso seleccionado
  - Formulario completo de datos del participante
  - Validación de datos en tiempo real
  - Selector de tipo de participante con precios dinámicos
  - Términos y condiciones obligatorios
  - Verificación de cupos disponibles

### 4. Página de Confirmación (`/preinscripcion/confirmacion`)
- **Archivo**: `resources/js/pages/Publico/Preinscripcion/Confirmacion.vue`
- **Funcionalidad**: Confirmación exitosa de preinscripción
- **Características**:
  - Muestra el ID de preinscripción (destacado)
  - Resumen de datos del participante y curso
  - Botón de descarga de comprobante PDF
  - Instrucciones de próximos pasos
  - Información de contacto

### 5. Generación de Comprobante PDF (`/preinscripcion/{id}/pdf`)
- **Archivo**: `resources/views/pdf/preinscripcion.blade.php`
- **Librería**: DomPDF
- **Funcionalidad**: Genera comprobante descargable
- **Características**:
  - Diseño profesional con logo y branding
  - Información completa del participante y curso
  - ID de preinscripción destacado
  - Instrucciones claras de próximos pasos
  - Información de contacto del CICIT

## Flujo de Navegación

```
1. Usuario ingresa a la página principal (/)
2. Ve los cursos disponibles en cards
3. Hace clic en "Ver Detalles" de un curso
4. Es redirigido al formulario de preinscripción (/preinscripcion/{curso})
5. Completa el formulario con sus datos
6. Envía la preinscripción
7. Es redirigido a la página de confirmación (/preinscripcion/confirmacion)
8. Ve su ID de preinscripción y puede descargar el PDF
9. Descarga el comprobante PDF con toda la información
```

## Validaciones Implementadas

### Validaciones del Formulario
- **Carnet**: Obligatorio, máximo 20 caracteres
- **Nombre**: Obligatorio, máximo 100 caracteres
- **Apellido**: Obligatorio, máximo 100 caracteres
- **Email**: Obligatorio, formato válido, máximo 255 caracteres
- **Teléfono**: Opcional, máximo 20 caracteres
- **Universidad**: Opcional, máximo 255 caracteres
- **Tipo de Participante**: Obligatorio, debe existir en la base de datos
- **Curso**: Obligatorio, debe existir y estar activo
- **Términos y Condiciones**: Obligatorio aceptar
- **Tratamiento de Datos**: Obligatorio aceptar

### Validaciones de Negocio
- Verificación de cupos disponibles
- Prevención de preinscripciones duplicadas
- Validación de curso activo y disponible
- Transacciones de base de datos para consistencia

## Estructura de Base de Datos

### Tablas Principales
- `CURSO`: Información de cursos
- `PARTICIPANTE`: Datos de participantes
- `PREINSCRIPCION`: Registros de preinscripción
- `TIPO_PARTICIPANTE`: Tipos de participantes
- `PRECIO_CURSO`: Precios por tipo de participante

### Relaciones
- `PREINSCRIPCION` → `PARTICIPANTE` (muchos a uno)
- `PREINSCRIPCION` → `CURSO` (muchos a uno)
- `PARTICIPANTE` → `TIPO_PARTICIPANTE` (muchos a uno)
- `PRECIO_CURSO` → `CURSO` (muchos a uno)
- `PRECIO_CURSO` → `TIPO_PARTICIPANTE` (muchos a uno)

## Archivos Principales

### Controladores
- `app/Http/Controllers/Publico/PreinscripcionPublicaController.php`

### Vistas Vue.js
- `resources/js/pages/Welcome.vue`
- `resources/js/pages/Publico/Cursos.vue`
- `resources/js/pages/Publico/Preinscripcion/Create.vue`
- `resources/js/pages/Publico/Preinscripcion/Confirmacion.vue`

### Vistas Blade
- `resources/views/pdf/preinscripcion.blade.php`

### Rutas
- `routes/web.php` (rutas de preinscripción pública)

### Modelos
- `app/Models/Curso.php`
- `app/Models/Participante.php`
- `app/Models/Preinscripcion.php`
- `app/Models/TipoParticipante.php`
- `app/Models/PrecioCurso.php`

## Tecnologías Utilizadas

- **Backend**: Laravel 11
- **Frontend**: Vue.js 3 + Inertia.js
- **Estilos**: Tailwind CSS
- **PDF**: DomPDF
- **Base de Datos**: PostgreSQL
- **Validación**: Laravel Validation + Vue.js

## Características de Seguridad

1. **Validación de Datos**: Validación tanto en frontend como backend
2. **Transacciones**: Uso de transacciones para operaciones críticas
3. **Verificación de Cupos**: Lock de filas para evitar condiciones de carrera
4. **Prevención de Duplicados**: Verificación de preinscripciones existentes
5. **Sanitización**: Limpieza de datos de entrada

## Configuración de Desarrollo

### Requisitos
- PHP 8.1+
- Composer
- Node.js 18+
- NPM
- PostgreSQL

### Instalación
```bash
# Instalar dependencias PHP
composer install

# Instalar dependencias JavaScript
npm install

# Configurar base de datos
php artisan migrate
php artisan db:seed --class=CursoTestSeeder

# Compilar assets
npm run dev

# Iniciar servidor
php artisan serve
```

## Funcionalidades Adicionales Sugeridas

### Para Futuras Implementaciones
1. **Notificaciones por Email**: Envío automático de confirmación
2. **Código QR**: Integración de QR en el PDF para verificación
3. **Dashboard Administrativo**: Panel para gestionar preinscripciones
4. **Reportes**: Estadísticas de preinscripciones por curso
5. **Recordatorios**: Sistema de recordatorios automáticos
6. **Integración de Pagos**: Pasarela de pagos online
7. **Certificados Digitales**: Generación automática de certificados

## Soporte y Mantenimiento

Para consultas sobre el código o implementaciones adicionales:

1. Revisar la documentación de Laravel
2. Consultar la documentación de Vue.js + Inertia.js
3. Verificar logs en `storage/logs/laravel.log`
4. Usar herramientas de debugging de Laravel (Telescope, Debugbar)

## Última Actualización

Fecha: 2 de Julio de 2025
Versión: 1.0.0
Estado: Producción Ready
