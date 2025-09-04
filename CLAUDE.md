# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Real Plateros is a modern point-of-sale and management system for a bakery/restaurant business. It's built with Laravel 10.x (PHP backend) and Vue.js 3 (frontend) using Inertia.js for seamless SPA experience.

## Technology Stack

**Backend:**
- PHP 8.1+ with Laravel 10.x
- MySQL/PostgreSQL database
- Laravel Sanctum for authentication
- Spatie Laravel Permission for role-based access
- ESC/POS PHP for thermal printer support
- Inertia.js for frontend-backend communication

**Frontend:**
- Vue.js 3 with Composition API
- Inertia.js (no separate API endpoints needed)
- Tailwind CSS for styling
- Pinia for state management
- Chart.js for data visualization
- SweetAlert2 for user notifications
- Heroicons and Lucide Vue for icons

## Development Commands

### Setup Commands
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Start development servers
php artisan serve        # Backend (usually http://localhost:8000)
npm run dev             # Frontend with HMR (Vite)
```

### Build Commands
```bash
# Build for production
npm run build

# Laravel optimizations for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Testing Commands
```bash
# Run PHP tests
php artisan test

# Code formatting (Laravel Pint)
./vendor/bin/pint
```

## Application Architecture

### Backend Structure
- **Controllers:** Handle business logic for different modules (Ventas, Inventario, Hornos, etc.)
- **Models:** Eloquent models for database entities (User, Venta, Inventario, Ticket, etc.)
- **Key Controllers:**
  - `DashboardController`: Main dashboard and navigation
  - `VentaController`: Sales processing and management
  - `InventarioController`: Inventory and ticket management
  - `HornoController`: Bakery oven operations
  - `GestorVentasController`: Sales management interface
  - `ControlProduccionController`: Production control
  - `PrintController`: Thermal printer operations

### Frontend Structure
- **Pages:** Vue components organized by feature (Dashboard, Inventario, Hornear, GestorVentas, etc.)
- **Components:** Reusable Vue components
- **Layouts:** Base layouts for different page types
- **Stores:** Pinia stores for state management

### Key Features
- **Sales Management:** Point of sale interface with ticket processing
- **Inventory Control:** Product tracking and stock management
- **Bakery Operations:** Oven management and production tracking
- **User Roles:** Role-based access control (admin, gestor, etc.)
- **Thermal Printing:** Receipt and ticket printing capabilities
- **Dashboard Analytics:** Sales reports and charts

## Database Architecture

Key entities include:
- `users` - User accounts with role-based permissions
- `ventas` - Sales transactions
- `inventario` - Product inventory
- `tickets` - Order tickets for kitchen
- `hornos` - Bakery ovens
- `control_produccion` - Production tracking

## Important Development Notes

### Authentication & Authorization
- Uses Laravel Sanctum for session-based auth
- Spatie Permission package for roles/permissions
- Middleware protects routes based on user roles
- Main roles: admin, gestor (manager)

### Frontend Patterns
- All pages use Inertia.js (no separate API calls needed)
- Pinia stores handle global state
- SweetAlert2 for consistent user notifications
- Chart.js integration for sales analytics

### Thermal Printing
- Uses mike42/escpos-php library
- Configure printer IP/port in .env file
- Print functionality integrated throughout the application

### Code Conventions
- Follow Laravel best practices for PHP code
- Use Vue 3 Composition API for frontend components
- Tailwind CSS for styling (utility-first approach)
- Keep business logic in controllers, not in views

## Environment Configuration

Key .env variables:
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=realplateros

# Thermal Printer
PRINTER_IP=192.168.1.100
PRINTER_PORT=9100
```

## Common Development Tasks

- **Adding new sales features:** Work with `VentaController` and related Vue pages
- **Inventory changes:** Modify `InventarioController` and `resources/js/Pages/Inventario/`
- **User management:** Use `UsuarioController` and Spatie Permission integration
- **New reports:** Add to `DashboardController` and create corresponding Vue components
- **Printer integration:** Extend `PrintController` for new print templates

## Production Deployment

1. Run `npm run build` to compile assets
2. Set up proper file permissions for `storage/` and `bootstrap/cache/`
3. Configure web server to serve from `public/` directory
4. Ensure thermal printer network connectivity
5. Run Laravel optimization commands for production