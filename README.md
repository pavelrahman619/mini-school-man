# Mini School Management System

A school management system built with Laravel 10 + Vue 3 + Sanctum + MySQL.

## Project Structure

```
mini-school-man/
├── backend/          # Laravel backend with Vue 3 frontend
└── README.md         # This file
```

## Quick Start

For detailed setup instructions, see [Backend README](backend/README.md).

### Prerequisites
- PHP 8.1+
- Composer
- Node.js & npm
- MySQL

### Quick Setup
```bash
cd backend
composer install
npm install
cp .env.example .env
# Configure your database in .env
php artisan migrate
php artisan serve
# In another terminal:
npm run dev
```

Access the application at http://localhost:8000

## Technology Stack
- **Backend**: Laravel 10
- **Frontend**: Vue 3 with Vue Router
- **Authentication**: Laravel Sanctum
- **Database**: MySQL
- **Build Tool**: Vite

## Notes
- ~~Kernel.php not in Laravel 12, use bootstrap/app.php instead~~
- Backend and frontend are currently integrated in the backend folder

## Future Implementations
- Setup Docker
- Separate backend and frontend folders for easier maintenance
- Add more school management features