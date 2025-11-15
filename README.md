# Mini School Attendance System

A school attendance system built with Laravel 10 + Vue 3 + Sanctum + MySQL.

## Screenshots

<img width="1917" height="926" alt="image" src="https://github.com/user-attachments/assets/038a0642-d39c-4f55-b22b-36c208837676" />

<img width="1915" height="927" alt="image" src="https://github.com/user-attachments/assets/67a487ff-aa16-46dd-b17b-0e6156b3efa8" />

<img width="1917" height="930" alt="image" src="https://github.com/user-attachments/assets/d17b42ea-b8b0-49a9-aed7-79903e99d6ca" />

<img width="1916" height="935" alt="image" src="https://github.com/user-attachments/assets/5605724d-0d51-4431-83f5-ff37d70c0ae4" />


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
- Proper Redis setup
- Micro services
- AI related Face recognition attendance or fingerprint
- Staff Management
- Switch to React cause React is better
