# Mini School Attendance System

A modern, full-stack web application for managing student attendance in educational institutions. Built with Laravel 10+ backend and Vue 3 frontend, featuring advanced capabilities like service layers, event-driven architecture, Redis caching, and comprehensive testing.

## 🎯 Features

### Core Functionality
- **Student Management**: Create, read, update, and delete student records with photo support
- **Bulk Attendance Recording**: Mark attendance for entire classes efficiently
- **Real-Time Statistics**: View today's attendance summary with live percentage calculations
- **Monthly Reports**: Generate comprehensive attendance reports with analytics
- **Search & Filter**: Find students by name, ID, or filter by class/section
- **Dashboard**: Visual overview with charts and quick action buttons

### Technical Highlights
- **Service Layer Architecture**: Clean separation of business logic following SOLID principles
- **Event-Driven Design**: Automatic cache invalidation and activity logging via Laravel events
- **Redis Caching**: Optimized performance with 1-hour TTL for statistics
- **API Authentication**: Secure token-based auth using Laravel Sanctum
- **Responsive UI**: Mobile-friendly interface with Tailwind CSS
- **Custom Artisan Command**: Generate reports via CLI for automation
- **Comprehensive Testing**: Unit tests for critical business logic

## 📋 Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [API Endpoints](#api-endpoints)
- [Artisan Commands](#artisan-commands)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [Technology Stack](#technology-stack)
- [AI Development](#ai-development)

## 🔧 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP**: 8.1 or higher
- **Composer**: Latest version
- **Node.js**: 16.x or higher
- **npm**: 8.x or higher
- **MySQL**: 5.7 or higher (or PostgreSQL 12+)
- **Redis**: 6.x or higher (for caching)

### Optional
- **Docker**: For containerized deployment
- **Git**: For version control

## 📦 Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd mini-school-attendance-system
```

### 2. Backend Setup

```bash
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Install Sanctum (if not already installed)
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 3. Frontend Setup

```bash
# Install Node.js dependencies (from backend directory)
npm install
```

## ⚙️ Configuration

### 1. Database Configuration

Edit the `.env` file in the `backend` directory:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_attendance
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 2. Redis Configuration

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
CACHE_DRIVER=redis
```

### 3. Application Configuration

```env
APP_NAME="School Attendance System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Frontend API URL
VITE_API_URL=http://localhost:8000/api
```

### 4. CORS Configuration

Ensure your frontend URL is in the `SANCTUM_STATEFUL_DOMAINS`:

```env
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

### 5. Create Database

```bash
# Create the database
mysql -u root -p
CREATE DATABASE school_attendance;
exit;
```

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Seed Database (Optional)

Populate the database with demo data:

```bash
php artisan db:seed
```

This creates:
- 1 admin user and 2 teachers
- 30 students across 3 classes
- Sample attendance records for the last 7 days

**Default Login Credentials:**
- Email: `admin@school.com`
- Password: `password`

## 🚀 Running the Application

### Development Mode

You need two terminal windows:

**Terminal 1 - Laravel Backend:**
```bash
cd backend
php artisan serve
```
The API will be available at `http://localhost:8000`

**Terminal 2 - Vite Dev Server:**
```bash
cd backend
npm run dev
```
The frontend will be available at `http://localhost:8000` (served by Laravel with Vite HMR)

### Production Build

```bash
cd backend

# Build frontend assets
npm run build

# Serve with production web server (Nginx/Apache)
# Or use Laravel's built-in server
php artisan serve --host=0.0.0.0 --port=8000
```

### Accessing the Application

1. Open your browser and navigate to `http://localhost:8000`
2. Login with the seeded credentials (or create a new user)
3. Start managing attendance!

## 📡 API Endpoints

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/login` | Authenticate user and get token | No |
| POST | `/api/logout` | Revoke current token | Yes |
| GET | `/api/me` | Get authenticated user details | Yes |

### Students

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/students` | List all students (paginated) | Yes |
| GET | `/api/students?search=john` | Search students by name/ID | Yes |
| GET | `/api/students?class=Class 10A` | Filter students by class | Yes |
| POST | `/api/students` | Create new student | Yes |
| GET | `/api/students/{id}` | Get single student | Yes |
| PUT | `/api/students/{id}` | Update student | Yes |
| DELETE | `/api/students/{id}` | Delete student | Yes |

### Attendance

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/attendance/bulk` | Record bulk attendance | Yes |
| GET | `/api/attendance/today` | Get today's statistics | Yes |
| GET | `/api/attendance/monthly-report` | Generate monthly report | Yes |

### API Request Examples

**Login:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@school.com","password":"password"}'
```

**Record Bulk Attendance:**
```bash
curl -X POST http://localhost:8000/api/attendance/bulk \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "date": "2024-11-15",
    "class": "Class 10A",
    "section": "A",
    "attendances": [
      {"student_id": 1, "status": "Present", "note": ""},
      {"student_id": 2, "status": "Absent", "note": "Sick leave"}
    ]
  }'
```

**Get Monthly Report:**
```bash
curl -X GET "http://localhost:8000/api/attendance/monthly-report?month=11&year=2024&class=Class 10A" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 🛠️ Artisan Commands

### Generate Attendance Report

Generate a formatted attendance report via command line:

```bash
php artisan attendance:generate-report {month} {class} {--year=}
```

**Examples:**
```bash
# Generate report for November 2024, Class 10A
php artisan attendance:generate-report 11 "Class 10A" --year=2024

# Generate report for current year
php artisan attendance:generate-report 11 "Class 10A"
```

The report will be:
- Displayed in the console with formatted table
- Saved to `storage/reports/attendance-{class}-{month}-{year}.txt`

### Other Useful Commands

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Run database migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Run tests
php artisan test
```

## 🧪 Testing

### Run All Tests

```bash
cd backend
php artisan test
```

### Run Specific Test Suite

```bash
# Run unit tests only
php artisan test --testsuite=Unit

# Run feature tests only
php artisan test --testsuite=Feature

# Run specific test file
php artisan test tests/Unit/AttendanceServiceTest.php
```

### Test Coverage

The project includes unit tests for:
- AttendanceService bulk recording
- Duplicate attendance prevention
- Monthly report generation with calculations
- Attendance percentage calculations

## 📁 Project Structure

```
mini-school-attendance-system/
├── backend/
│   ├── app/
│   │   ├── Console/
│   │   │   └── Commands/
│   │   │       └── GenerateAttendanceReport.php
│   │   ├── Events/
│   │   │   └── AttendanceRecorded.php
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── StudentController.php
│   │   │   │   └── AttendanceController.php
│   │   │   └── Resources/
│   │   │       ├── StudentResource.php
│   │   │       └── AttendanceResource.php
│   │   ├── Listeners/
│   │   │   ├── LogAttendanceActivity.php
│   │   │   └── InvalidateAttendanceCache.php
│   │   ├── Models/
│   │   │   ├── Student.php
│   │   │   ├── Attendance.php
│   │   │   └── User.php
│   │   └── Services/
│   │       ├── AttendanceService.php
│   │       └── StudentService.php
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── resources/
│   │   ├── css/
│   │   │   └── app.css
│   │   ├── js/
│   │   │   ├── components/
│   │   │   │   ├── AttendanceChart.vue
│   │   │   │   ├── AttendanceMarker.vue
│   │   │   │   ├── NavigationBar.vue
│   │   │   │   ├── Pagination.vue
│   │   │   │   ├── StatisticsCard.vue
│   │   │   │   └── StudentCard.vue
│   │   │   ├── composables/
│   │   │   │   ├── useAuth.js
│   │   │   │   ├── useStudents.js
│   │   │   │   └── useAttendance.js
│   │   │   ├── router/
│   │   │   │   └── index.js
│   │   │   ├── services/
│   │   │   │   ├── api.js
│   │   │   │   ├── authService.js
│   │   │   │   ├── studentService.js
│   │   │   │   └── attendanceService.js
│   │   │   ├── views/
│   │   │   │   ├── AttendanceRecordingView.vue
│   │   │   │   ├── DashboardView.vue
│   │   │   │   ├── Login.vue
│   │   │   │   └── StudentListView.vue
│   │   │   ├── App.vue
│   │   │   └── app.js
│   │   └── views/
│   │       └── app.blade.php
│   ├── routes/
│   │   ├── api.php
│   │   └── web.php
│   ├── tests/
│   │   └── Unit/
│   │       └── AttendanceServiceTest.php
│   ├── .env.example
│   ├── composer.json
│   ├── package.json
│   └── vite.config.js
├── AI_WORKFLOW.md
└── README.md
```

## 🔨 Technology Stack

### Backend
- **Framework**: Laravel 10+
- **Language**: PHP 8.1+
- **Authentication**: Laravel Sanctum
- **Database**: MySQL 5.7+ / PostgreSQL 12+
- **Cache**: Redis 6.x
- **Testing**: PHPUnit

### Frontend
- **Framework**: Vue 3 (Composition API)
- **Router**: Vue Router 4
- **HTTP Client**: Axios
- **Charts**: Chart.js + vue-chartjs
- **Styling**: Tailwind CSS 4
- **Build Tool**: Vite 5
- **State Management**: Pinia (optional)

### Development Tools
- **Package Manager**: Composer (PHP), npm (JavaScript)
- **Code Quality**: PHP CS Fixer, ESLint
- **Version Control**: Git

## 🤖 AI Development

This project was developed with significant AI assistance, achieving a **66% reduction in development time**. For detailed information about the AI-assisted development process, see [AI_WORKFLOW.md](AI_WORKFLOW.md).

**Key Highlights:**
- ~75% of codebase generated with AI assistance
- 28.5 hours saved compared to traditional development
- AI excelled at scaffolding, pattern implementation, and test generation
- Human oversight critical for business logic, security, and UX

## 🔐 Security Considerations

- All API routes (except login) are protected with Sanctum authentication
- CSRF protection enabled for web routes
- SQL injection prevention via Eloquent ORM
- XSS protection through output escaping
- Rate limiting on API endpoints (60 requests/minute)
- Password hashing with bcrypt
- Input validation on all endpoints

## 📝 Environment Variables

### Required Variables

```env
# Application
APP_NAME="School Attendance System"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_attendance
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
CACHE_DRIVER=redis

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1

# Frontend
VITE_API_URL=http://localhost:8000/api
```

See `.env.example` for complete list of available variables.

## 🐛 Troubleshooting

### Common Issues

**Issue: "SQLSTATE[HY000] [1045] Access denied"**
- Solution: Check database credentials in `.env` file

**Issue: "Class 'Redis' not found"**
- Solution: Install PHP Redis extension: `pecl install redis`

**Issue: "Vite manifest not found"**
- Solution: Run `npm run build` or ensure `npm run dev` is running

**Issue: "CORS policy error"**
- Solution: Add your frontend URL to `SANCTUM_STATEFUL_DOMAINS` in `.env`

**Issue: "Token mismatch"**
- Solution: Clear cache with `php artisan cache:clear` and regenerate key

## 📄 License

This project is open-source and available under the MIT License.

## 👥 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📧 Support

For issues and questions, please open an issue on the GitHub repository.

---

**Built with ❤️ using Laravel, Vue.js, and AI assistance**