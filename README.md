# RepuestosERP Core

Sistema de gestion empresarial para repuestos, inventario, ventas y administracion multiempresa. La aplicacion esta construida con Laravel y utiliza Filament para el panel administrativo.

## Stack tecnologico

- PHP 8.3+
- Laravel 12
- Filament 4
- PostgreSQL
- Spatie Laravel Permission
- Vite, Tailwind CSS y Axios

## Requisitos

- PHP 8.3 o superior con las extensiones requeridas por Laravel
- Composer
- Node.js y npm
- PostgreSQL 14 o superior

## Instalacion

1. Clona el repositorio y entra en la carpeta del proyecto.
2. Instala las dependencias de PHP y JavaScript:

   ```bash
   composer install
   npm install
   ```

3. Crea el archivo de entorno:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   En Windows PowerShell puedes usar:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

4. Configura en `.env` la conexion a PostgreSQL (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`). No publiques este archivo ni sus credenciales.
5. Ejecuta las migraciones:

   ```bash
   php artisan migrate
   ```

6. Enlaza el almacenamiento publico y compila los recursos:

   ```bash
   php artisan storage:link
   npm run build
   ```

## Desarrollo local

Para iniciar el servidor de Laravel:

```bash
php artisan serve
```

En otra terminal, inicia Vite para recargar los recursos automaticamente:

```bash
npm run dev
```

Tambien puedes iniciar los procesos habituales del proyecto con Composer:

```bash
composer run dev
```

La aplicacion estara disponible normalmente en `http://localhost:8000`.

## Pruebas y calidad

```bash
php artisan test
vendor/bin/pint --test
```

Para aplicar el formateo de Laravel Pint:

```bash
vendor/bin/pint
```

## Estructura principal

- `app/Models`: modelos de empresas, sucursales, productos, inventario, pedidos y pagos.
- `app/Filament`: recursos y widgets del panel administrativo.
- `app/Http`: controladores y middleware HTTP.
- `database/migrations`: estructura de la base de datos.
- `database/seeders`: datos iniciales y de prueba.
- `resources`: vistas Blade, JavaScript y estilos fuente.
- `routes`: rutas web y de consola.
- `tests`: pruebas unitarias y funcionales.

## Licencia

Este proyecto utiliza la licencia MIT.