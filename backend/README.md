# Laravel 10 + Vue 3 + Sanctum + MySQL

## Setup Instructions

### 1. Install Dependencies
```bash
cd backend
composer install
npm install
```

### 2. Configure Environment
Update `.env` file with your database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Run Migrations
```bash
php artisan migrate
```

### 4. Create a Test User (Optional)
```bash
php artisan tinker
```
Then run:
```php
User::create(['name' => 'Test User', 'email' => 'test@example.com', 'password' => bcrypt('password')]);
```

### 5. Start Development Servers

Terminal 1 - Laravel:
```bash
php artisan serve
```

Terminal 2 - Vite:
```bash
npm run dev
```

### 6. Access Application
- Frontend: http://localhost:8000
- Login with: test@example.com / password

## Project Structure
```
backend/
├── resources/
│   ├── js/
│   │   ├── views/
│   │   │   ├── Home.vue
│   │   │   └── Login.vue
│   │   ├── router/
│   │   │   └── index.js
│   │   ├── App.vue
│   │   ├── app.js
│   │   └── bootstrap.js
│   └── views/
│       └── app.blade.php
├── routes/
│   ├── api.php (Sanctum auth endpoints)
│   └── web.php (SPA catch-all route)
└── vite.config.js
```

## API Endpoints
- POST `/api/login` - Login and get token
- GET `/api/user` - Get authenticated user (requires token)
- POST `/api/logout` - Logout and revoke token
