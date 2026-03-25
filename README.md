# Laravel Multilingual Blog

A web-based **Multilingual Blog** built using **Laravel**, designed to manage Multi Language posts, comments, browsing.

---

## 📌 Features

- documented by swagger
- User authentication (Register / verifyEmail )
- User authentication (Login / Login no Password)
- User authentication (Login / Register) by Google and Github
- display ,update user Profile and his favicon
- Create, update, delete ,display user public posts and privet posts
- Create, update, delete ,display comments
- Browsing Multi language all posts and comments
- Search user posts
- choose a react on posts and comments
- User authorization display Users and Change role or ban or activate or destroy User
- User authorization Roles (create / update / delete) 
- User authorization permissions

---

## 🛠️ Technologies Used

- Laravel 11+
- PHP 8.2+
- MySQL
- L5 Swagger UI
- Laravel Phone package 
---
---
## ⚙️ Installation

### 1️⃣ Clone the Repository

```bash
git clone https://github.com/ahmed4-75/multilingual-blog.git
cd laravel-multilingual_blog
```

### 2️⃣ Install Dependencies
```bash
composer install
```

### 3️⃣ Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```
---
---
### 🗄️ Database Configuration

### 1️⃣ Update .env file :
```bash
B_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Blog
DB_USERNAME=root
DB_PASSWORD=
```
### 2️⃣ Run migrations:
```bash
php artisan migrate
```
### 3️⃣ Run Command:
```bash
php artisan create:owner
```
Answer the questions to create your first User, and his role is "owner", and it has all Permissions

### 4️⃣ Run Seeders
```bash
php artisan db:seed --class=ReactSeeder
```
---
---
### 🚀 Run the Application
```bash
php artisan serve
```
### Open in browser: