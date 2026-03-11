# 🧠 Quizzard

A quiz-making mobile app for teachers and students. Teachers can create quizzes with multiple question types, and students can take them and see their results instantly.

---

## 📱 Screenshots

> Login → Student Dashboard → Quiz Taking → Results

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel (PHP 8.4) REST API |
| Frontend | Flutter (Dart) |
| Database | MySQL 8.0 |
| Web Server | Nginx |
| Containerization | Docker |

---

## 📋 Prerequisites

Install the following before getting started:

- [Docker Desktop](https://www.docker.com/products/docker-desktop)
- [Flutter SDK](https://docs.flutter.dev/get-started/install)
- [Git](https://git-scm.com/downloads)
- [Android Studio](https://developer.android.com/studio) — for emulator or USB debugging setup
- [Postman](https://www.postman.com/downloads) *(optional, for API testing)*

---

## 🚀 Getting Started

### Step 1 — Clone the Repository

```bash
git clone https://github.com/rods-12/Quizzard.git
cd Quizzard
```

---

### Step 2 — Find Your Computer's WiFi IP

The Flutter app needs your machine's local IP to communicate with the Laravel backend.

**Windows:**
```bash
ipconfig
```
Look for **IPv4 Address** under your WiFi adapter.
Example: `192.168.1.10`

**Mac/Linux:**
```bash
ifconfig
```

---

### Step 3 — Update the Flutter API Base URL

Open `frontend/lib/services/auth_service.dart` and update this line:

```dart
// Change this:
static const String baseUrl = 'http://172.21.22.155:8000/api';

// To your own IP:
static const String baseUrl = 'http://YOUR_WIFI_IP:8000/api';
```

> ⚠️ Your phone and computer **must be on the same WiFi network**.

---

### Step 4 — Start Docker Containers

Make sure Docker Desktop is running, then:

```bash
docker compose up -d --build
```

Verify all 3 containers are running:

```bash
docker compose ps
```

Expected output:
```
NAME              STATUS
quizzard_app      running   (PHP/Laravel)
quizzard_nginx    running   (Web Server on port 8000)
quizzard_db       running   (MySQL on port 3306)
```

---

### Step 5 — Install Laravel Dependencies

```bash
docker compose exec app composer install
```

---

### Step 6 — Configure Laravel Environment

```bash
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
```

Open `backend/.env` and verify these values:

```env
APP_NAME=Quizzard
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=quizzard
DB_USERNAME=quizzard_user
DB_PASSWORD=quizzard_pass
```

---

### Step 7 — Set Up the Database

Run migrations to create all tables:

```bash
docker compose exec app php artisan migrate:fresh
```

Fix MySQL user permissions *(run once)*:

```bash
docker compose exec db mysql -u root -proot -e "GRANT ALL PRIVILEGES ON *.* TO 'quizzard_user'@'%' WITH GRANT OPTION; FLUSH PRIVILEGES;"
```

Seed default users:

```bash
docker compose exec app php artisan db:seed --class=AdminUserSeeder
```

---

### Step 8 — Verify the Backend

Open your browser and visit:
```
http://localhost:8000/api/login
```

You should see a JSON response — this means Laravel is running correctly. ✅

---

### Step 9 — Set Up Flutter

```bash
cd frontend
flutter pub get
```

Connect your Android device via USB with **USB Debugging** enabled, then find your device ID:

```bash
flutter devices
```

Example output:
```
0779725237100211 • Infinix X695 • android-arm64
```

---

### Step 10 — Run the App

```bash
flutter run -d YOUR_DEVICE_ID
```

Or on an emulator:

```bash
flutter run
```

---

## 👤 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@quizzard.com | Admin@1234 |
| Teacher | teacher@quizzard.com | Teacher@1234 |
| Student | student@quizzard.com | Student@1234 |

> **Note:** New registrations are set to `pending` status and must be activated by an Admin before they can log in.

---

## 📁 Project Structure

```
Quizzard/
├── backend/                        # Laravel PHP API
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       ├── AuthController.php
│   │   │       ├── StudentController.php
│   │   │       ├── TeacherController.php
│   │   │       ├── QuizController.php
│   │   │       └── QuestionController.php
│   │   └── Models/
│   │       ├── User.php
│   │       ├── Quiz.php
│   │       ├── Question.php
│   │       ├── AnswerOption.php
│   │       ├── QuizAttempt.php
│   │       └── StudentAnswer.php
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   │       └── AdminUserSeeder.php
│   └── routes/
│       └── api.php
├── frontend/                       # Flutter Mobile App
│   └── lib/
│       ├── main.dart
│       ├── screens/
│       │   ├── login_screen.dart
│       │   ├── register_screen.dart
│       │   ├── student_dashboard_screen.dart
│       │   ├── teacher_dashboard_screen.dart
│       │   ├── quiz_taking_screen.dart
│       │   └── question_preview_screen.dart
│       ├── widgets/
│       │   ├── multiple_choice_widget.dart
│       │   ├── multiple_choice_result_widget.dart
│       │   ├── true_false_widget.dart
│       │   ├── true_false_result_widget.dart
│       │   ├── identification_widget.dart
│       │   ├── identification_result_widget.dart
│       │   ├── matching_widget.dart
│       │   └── matching_result_widget.dart
│       └── services/
│           └── auth_service.dart
├── nginx/
│   └── default.conf
├── docker-compose.yml
└── README.md
```

---

## 🔌 API Endpoints

### Public Routes
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/login` | Login and get token |
| POST | `/api/register` | Register new account |

### Protected Routes *(Bearer Token required)*
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/logout` | Logout |
| GET | `/api/me` | Get current user |
| GET | `/api/student/dashboard` | Student dashboard data |
| GET | `/api/teacher/dashboard` | Teacher dashboard data |
| GET | `/api/quizzes/{id}` | Get quiz with questions |
| POST | `/api/quizzes/{id}/start` | Start a quiz attempt |
| POST | `/api/quizzes/{id}/submit` | Submit and auto-score quiz |
| GET | `/api/quizzes/{id}/questions` | List questions |
| POST | `/api/quizzes/{id}/questions/multiple-choice` | Create MC question |
| POST | `/api/quizzes/{id}/questions/true-false` | Create T/F question |
| POST | `/api/quizzes/{id}/questions/identification` | Create identification question |
| POST | `/api/quizzes/{id}/questions/matching` | Create matching question |
| PUT | `/api/quizzes/{id}/questions/{qId}` | Update question |
| DELETE | `/api/quizzes/{id}/questions/{qId}` | Delete question |

---

## 🗄 Database Schema

```
users
  id, name, email, password, role[admin/teacher/student],
  status[pending/active/deactivated], failed_login_attempts,
  locked_until, profile_picture, bio

quizzes
  id, teacher_id → users, title, description,
  is_published, cover_image

questions
  id, quiz_id → quizzes, question_text,
  question_type[multiple_choice/true_false/identification/matching],
  media_path, media_type, points, order

answer_options
  id, question_id → questions, option_text,
  is_correct, match_pair, order

quiz_attempts
  id, student_id → users, quiz_id → quizzes,
  score, total_points, status[in_progress/completed],
  started_at, completed_at

student_answers
  id, attempt_id → quiz_attempts, question_id → questions,
  answer_given, is_correct, points_earned
```

---

## ❓ Question Types

| Type | Description |
|------|-------------|
| Multiple Choice | 4 options, exactly 1 correct answer |
| True or False | Choose between True or False |
| Identification | Type in the correct answer (case-insensitive) |
| Matching | Match Column A items to Column B items |

---

## 🔐 Security Features

- Token-based authentication via Laravel Sanctum
- Account lockout after 5 failed login attempts (15 min cooldown)
- Strong password enforcement (8+ chars, upper, lower, number, special)
- New accounts require Admin approval before login
- `is_correct` field is never sent to students during quiz taking

---

## 🧰 Useful Commands

```bash
# Start all containers
docker compose up -d

# Stop all containers
docker compose down

# Rebuild containers
docker compose up -d --build

# Reset database
docker compose exec app php artisan migrate:fresh
docker compose exec app php artisan db:seed --class=AdminUserSeeder

# Clear Laravel cache
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear

# View Laravel error logs
docker compose exec app cat storage/logs/laravel.log

# View container logs
docker logs quizzard_app

# List all API routes
docker compose exec app php artisan route:list

# Access MySQL directly
docker compose exec db mysql -u root -proot

# Flutter - clean build
cd frontend
flutter clean
flutter pub get
flutter run -d YOUR_DEVICE_ID
```

---

## 🐛 Troubleshooting

**Flutter app can't connect to the API**
- Ensure your phone and PC are on the **same WiFi network**
- Re-check the IP in `auth_service.dart` matches your `ipconfig` output
- Confirm Docker containers are running: `docker compose ps`

**504 Gateway Timeout**
- Check Laravel logs: `docker compose exec app cat storage/logs/laravel.log`
- Restart containers: `docker compose down && docker compose up -d`

**Database connection errors**
- Run migrations fresh: `docker compose exec app php artisan migrate:fresh`
- Re-grant MySQL permissions (see Step 7)

**Flutter build errors**
```bash
flutter clean
flutter pub get
flutter run
```

**`composer install` fails**
- Make sure the app container is running: `docker compose up -d`

---

## 👥 Contributors

- **rods-12** — Lead Developer

---

## 📄 License

This project is for educational purposes.