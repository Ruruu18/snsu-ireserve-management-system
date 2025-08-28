# SNSU iReserve Management System - Setup Guide

## 🚀 Clone and Setup on New Device

This guide will help you clone and set up the SNSU iReserve Management System on any new device.

## 📋 Prerequisites

Before you begin, ensure you have the following installed on your system:

- **Git** (latest version)
- **PHP** (8.1 or higher)
- **Composer** (PHP dependency manager)
- **Node.js** (18.x or higher) and **npm**
- **Database** (MySQL, PostgreSQL, or SQLite)

## 🔧 Installation Steps

### 1. Clone the Repository

```bash
# Clone the repository
git clone https://github.com/Ruruu18/snsu-ireserve-management-system.git

# Navigate to the project directory
cd snsu-ireserve-management-system
```

### 2. Install PHP Dependencies

```bash
# Install Laravel dependencies
composer install
```

### 3. Install JavaScript Dependencies

```bash
# Install Node.js dependencies
npm install
```

### 4. Environment Configuration

```bash
# Copy the environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Database

Edit the `.env` file and configure your database settings:

```env
# For SQLite (simplest option)
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

# For MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ireserve_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# For PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ireserve_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Database Setup

```bash
# Create SQLite database file (if using SQLite)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed
```

### 7. Storage Setup

```bash
# Create symbolic link for storage
php artisan storage:link
```

### 8. Build Frontend Assets

```bash
# Build for development
npm run dev

# OR build for production
npm run build
```

## 🚀 Running the Application

### Development Server

```bash
# Start Laravel development server
php artisan serve

# In another terminal, start Vite development server (for hot reloading)
npm run dev
```

The application will be available at: `http://localhost:8000`

### Production Deployment

```bash
# Build production assets
npm run build

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 👥 Default User Accounts

After seeding, you can log in with these default accounts:

### Admin Account

- **Email**: `admin@snsu.edu.ph`
- **Password**: `password`
- **Role**: Administrator

### Faculty Account

- **Email**: `faculty@snsu.edu.ph`
- **Password**: `password`
- **Role**: Faculty

### Student Account

- **Email**: `student@snsu.edu.ph`
- **Password**: `password`
- **Role**: Student

## 🔧 Additional Configuration

### Mail Configuration (Optional)

If you want to enable email notifications, configure mail settings in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@domain.com
MAIL_FROM_NAME="SNSU iReserve"
```

### Queue Configuration (Optional)

For background job processing:

```env
QUEUE_CONNECTION=database
```

Then run:

```bash
php artisan queue:work
```

## 📁 Project Structure

```
ireserve/
├── app/
│   ├── Http/Controllers/     # Application controllers
│   ├── Models/              # Eloquent models
│   └── Middleware/          # Custom middleware
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/            # Database seeders
├── resources/
│   ├── js/                 # Vue.js components and pages
│   └── css/                # Stylesheets
├── routes/                 # Application routes
└── public/                 # Public assets
```

## 🌟 Key Features

- **Role-based Authentication**: Admin, Faculty, and Student roles
- **Equipment Management**: CRUD operations for laboratory equipment
- **Reservation System**: Book and manage equipment reservations
- **Department Management**: Organize users by departments
- **Real-time Dashboard**: Monitor equipment usage and reservations
- **Responsive Design**: Works on desktop and mobile devices

## 🛠️ Development Commands

```bash
# Run tests
php artisan test

# Clear all caches
php artisan optimize:clear

# Generate IDE helper files (if using Laravel IDE Helper)
php artisan ide-helper:generate

# Database refresh (WARNING: Deletes all data)
php artisan migrate:refresh --seed
```

## 🔍 Troubleshooting

### Common Issues

1. **Permission Issues**:

   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

2. **Composer Memory Issues**:

   ```bash
   php -d memory_limit=-1 /usr/local/bin/composer install
   ```

3. **Node.js Version Issues**:

   ```bash
   # Use Node Version Manager (nvm)
   nvm install 18
   nvm use 18
   ```

4. **Database Connection Issues**:
   - Verify database credentials in `.env`
   - Ensure database server is running
   - Check firewall settings

### Getting Help

- Check Laravel logs: `storage/logs/laravel.log`
- Enable debug mode: Set `APP_DEBUG=true` in `.env`
- Check browser console for JavaScript errors

## 📞 Support

For technical support or questions about the iReserve Management System, contact the development team or create an issue in the GitHub repository.

## 📄 License

This project is developed for Surigao del Norte State University (SNSU) internal use.

---

**Happy Coding! 🎉**
