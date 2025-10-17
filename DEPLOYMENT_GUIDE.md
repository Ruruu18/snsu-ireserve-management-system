# Deployment Guide - SNSU iReserve Management System

This guide will help you clone and set up the project on a new device, or update an existing installation.

## Repository URL
```
https://github.com/Ruruu18/snsu-ireserve-management-system.git
```

---

## Option 1: Fresh Installation (New Device)

### Prerequisites

Make sure you have these installed:
- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8.0 or higher
- Git

### Step 1: Clone the Repository

```bash
# Navigate to your desired directory
cd ~/Desktop/Projects

# Clone the repository
git clone https://github.com/Ruruu18/snsu-ireserve-management-system.git

# Enter the project directory
cd snsu-ireserve-management-system
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Install Node Dependencies

```bash
npm install
```

### Step 4: Set Up Environment File

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 5: Configure Environment Variables

Edit the `.env` file with your database and email settings:

```env
# Application
APP_NAME="SNSU iReserve"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=Asia/Manila

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ireserve
DB_USERNAME=root
DB_PASSWORD=your_password

# Queue
QUEUE_CONNECTION=database

# Email (Gmail SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="SNSU iReserve"
```

### Step 6: Create Database

```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE ireserve;
exit;
```

### Step 7: Run Migrations

```bash
# Run all migrations
php artisan migrate

# Optional: Seed the database with sample data
php artisan db:seed
```

### Step 8: Create Storage Link

```bash
php artisan storage:link
```

### Step 9: Build Frontend Assets

```bash
# For development
npm run dev

# Or for production
npm run build
```

### Step 10: Start the Application

Open 3 separate terminals:

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Queue Worker (for email notifications):**
```bash
php artisan queue:work
```

**Terminal 3 - Vite Dev Server (if using npm run dev):**
```bash
npm run dev
```

### Step 11: Access the Application

Open your browser and go to:
```
http://localhost:8000
```

---

## Option 2: Update Existing Installation

If you already have the project cloned and want to get the latest updates:

### Step 1: Navigate to Project Directory

```bash
cd path/to/snsu-ireserve-management-system
```

### Step 2: Save Your Local Changes (if any)

```bash
# Check current status
git status

# If you have uncommitted changes, stash them
git stash

# Or commit them
git add .
git commit -m "Local changes before update"
```

### Step 3: Pull Latest Changes

```bash
# Fetch and pull latest changes from main branch
git pull origin main
```

### Step 4: Update Dependencies

```bash
# Update PHP dependencies
composer install

# Update Node dependencies
npm install
```

### Step 5: Run New Migrations (if any)

```bash
# Run any new migrations
php artisan migrate

# If migration fails, you might need to refresh (⚠️ This will delete data!)
# php artisan migrate:fresh --seed
```

### Step 6: Clear Caches

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 7: Rebuild Assets

```bash
# Rebuild frontend assets
npm run build

# Or for development
npm run dev
```

### Step 8: Restart Queue Worker

If you have a queue worker running, restart it:

```bash
# Stop the current queue worker (Ctrl+C)
# Then start it again
php artisan queue:work
```

### Step 9: Restore Your Changes (if stashed)

```bash
# If you stashed changes in Step 2
git stash pop
```

---

## Option 3: Quick Update Script

Create a file called `update.sh` in your project root:

```bash
#!/bin/bash

echo "🔄 Updating SNSU iReserve System..."

# Pull latest changes
echo "📥 Pulling latest changes..."
git pull origin main

# Update dependencies
echo "📦 Updating PHP dependencies..."
composer install --no-interaction

echo "📦 Updating Node dependencies..."
npm install

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Build assets
echo "🎨 Building assets..."
npm run build

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache

echo "✅ Update complete!"
echo "⚠️  Remember to restart your queue worker: php artisan queue:work"
```

Make it executable and run:

```bash
chmod +x update.sh
./update.sh
```

---

## Troubleshooting

### Database Connection Issues

```bash
# Check MySQL is running
mysql --version

# Test database connection
php artisan tinker
DB::connection()->getPdo();
exit
```

### Permission Issues

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Migration Issues

```bash
# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# Fresh start (⚠️ Deletes all data!)
php artisan migrate:fresh --seed
```

### Email Not Sending

```bash
# Test email configuration
php artisan email:test your-email@example.com

# Check queue jobs
php artisan queue:work --stop-when-empty

# Check failed jobs
php artisan queue:failed

# Clear failed jobs
php artisan queue:flush
```

### Cache Issues

```bash
# Clear everything
php artisan optimize:clear

# Or individually
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Frontend Build Issues

```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Clear Vite cache
rm -rf node_modules/.vite

# Rebuild
npm run build
```

---

## Development Workflow

### Daily Development

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start queue worker
php artisan queue:work

# Terminal 3: Start Vite dev server
npm run dev
```

### Before Committing Changes

```bash
# Check status
git status

# Stage changes
git add .

# Commit with message
git commit -m "Your commit message"

# Push to remote
git push origin main
```

### Working with Branches

```bash
# Create new branch
git checkout -b feature/your-feature-name

# Make changes and commit
git add .
git commit -m "Add new feature"

# Push branch to remote
git push origin feature/your-feature-name

# Merge back to main (after review)
git checkout main
git merge feature/your-feature-name
git push origin main
```

---

## Production Deployment Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Use production database credentials
- [ ] Set up proper email configuration
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Run `npm run build` (not `npm run dev`)
- [ ] Set up Supervisor for queue workers
- [ ] Configure proper file permissions
- [ ] Set up SSL/HTTPS
- [ ] Configure backups
- [ ] Set up monitoring and logging

---

## Common Commands Reference

### Artisan Commands

```bash
# View all routes
php artisan route:list

# Create new controller
php artisan make:controller NameController

# Create new model with migration
php artisan make:model ModelName -m

# Create new migration
php artisan make:migration create_table_name

# View migration status
php artisan migrate:status

# Rollback migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Create new seeder
php artisan make:seeder NameSeeder

# Test email
php artisan email:test your@email.com
```

### Git Commands

```bash
# Check status
git status

# Pull latest changes
git pull origin main

# Push changes
git push origin main

# View commit history
git log --oneline

# Discard local changes
git checkout -- .

# Create and switch to branch
git checkout -b branch-name
```

### Composer Commands

```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Dump autoload
composer dump-autoload
```

### NPM Commands

```bash
# Install dependencies
npm install

# Development build with watch
npm run dev

# Production build
npm run build

# Update dependencies
npm update
```

---

## Support

If you encounter any issues:

1. Check the `storage/logs/laravel.log` file for errors
2. Refer to the `EMAIL_TESTING_GUIDE.md` for email issues
3. Refer to the `IMPLEMENTATION_GUIDE.md` for feature documentation
4. Check the Laravel documentation: https://laravel.com/docs

---

**Last Updated**: October 17, 2025
**Version**: 1.0.0
