# Real Plateros 🏪

Sistema de gestión y punto de venta moderno desarrollado con Laravel y Vue.js.

## 🚀 Características

- 💻 Panel de administración intuitivo
- 🛍️ Gestión de ventas y productos
- 👥 Sistema de roles y permisos
- 📊 Gráficos y reportes
- 🖨️ Soporte para impresoras térmicas
- 🔐 Autenticación segura
- 📱 Interfaz responsive

## 🛠️ Tecnologías

### Backend
- PHP 8.1+
- Laravel 10.x
- MySQL/PostgreSQL
- Laravel Sanctum
- Spatie Permission

### Frontend
- Vue.js 3
- Inertia.js
- Tailwind CSS
- Pinia
- Chart.js
- SweetAlert2

## 📋 Requisitos Previos

- PHP >= 8.1
- Composer
- Node.js >= 16
- NPM o Yarn
- MySQL/PostgreSQL
- Servidor web (Apache/Nginx)

## 🚀 Instalación

1. Clonar el repositorio
```bash
git clone [URL_DEL_REPOSITORIO]
cd realplateros
```

2. Instalar dependencias de PHP
```bash
composer install
```

3. Instalar dependencias de Node.js
```bash
npm install
```

4. Configurar el entorno
```bash
cp .env.example .env
php artisan key:generate
```

5. Configurar la base de datos en el archivo `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=realplateros
DB_USERNAME=root
DB_PASSWORD=
```

6. Ejecutar migraciones y seeders
```bash
php artisan migrate --seed
```

7. Compilar assets
```bash
npm run build
```

8. Iniciar el servidor de desarrollo
```bash
php artisan serve
npm run dev
```

## 🔧 Configuración Adicional

### Impresora Térmica
Para configurar la impresora térmica, asegúrate de tener instalados los drivers necesarios y configura los parámetros en el archivo `.env`:

```env
PRINTER_IP=192.168.1.100
PRINTER_PORT=9100
```

### Permisos de Directorios
Asegúrate de que los siguientes directorios tengan permisos de escritura:

```bash
chmod -R 775 storage bootstrap/cache
```

## 📦 Estructura del Proyecto

```
realplateros/
├── app/                # Lógica principal de la aplicación
├── config/            # Archivos de configuración
├── database/          # Migraciones y seeders
├── public/            # Archivos públicos
├── resources/         # Assets y vistas
├── routes/            # Definición de rutas
└── tests/             # Tests automatizados
```

## 🧪 Testing

Para ejecutar los tests:

```bash
php artisan test
```

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👥 Contribución

1. Fork el proyecto
2. Crea tu rama de características (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📞 Soporte

Para soporte, por favor contacta al equipo de desarrollo o abre un issue en el repositorio.

---

Desarrollado con ❤️ por el equipo de Real Plateros
