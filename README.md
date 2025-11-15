# Mini School Attendance System

A school attendance system built with Laravel 10 + Vue 3 + Sanctum + MySQL.

## Features

- Student management with photo support
- Bulk attendance recording
- Real-time statistics and monthly reports
- Dashboard with charts
- Search and filter students

## Project Structure

```
mini-school-man/
├── backend/          # Laravel backend with Vue 3 frontend
└── README.md         # This file
```

## Prerequisites

- PHP 8.1+
- Composer
- Node.js & npm
- MySQL
- Redis

## Installation

```bash
cd backend
composer install
npm install
cp .env.example .env
# Configure your database in .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

## Running the Application

```bash
# Terminal 1 - Backend
php artisan serve

# Terminal 2 - Frontend
npm run dev
```

Access at http://localhost:8000

**Default Login:**
- Email: `admin@school.com`
- Password: `password`

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/login` | Login |
| POST | `/api/logout` | Logout |
| GET | `/api/students` | List students |
| POST | `/api/attendance/bulk` | Record attendance |
| GET | `/api/attendance/today` | Today's stats |
| GET | `/api/attendance/monthly-report` | Monthly report |

## Environment Variables

See `.env.example` for all required variables. Key ones:

```env
DB_DATABASE=school_attendance
CACHE_DRIVER=redis
VITE_API_URL=http://localhost:8000/api
```

## Technology Stack

- **Backend**: Laravel 10, Sanctum, Redis
- **Frontend**: Vue 3, Vue Router, Tailwind CSS, Chart.js
- **Build**: Vite

## AI Development

This project was built with AI assistance. See [AI_WORKFLOW.md](AI_WORKFLOW.md) for details.

## Notes

- Backend and frontend are integrated in the backend folder
- Uses Redis for caching attendance statistics
- Service layer architecture with event-driven cache invalidation

## Future Implementations

- Setup Docker
- Separate backend and frontend folders for easier maintenance
- Add more school management features