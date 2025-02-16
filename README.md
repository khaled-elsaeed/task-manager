
# Task Manager API

## Overview
The **Task Manager API** is a simple Laravel-based application that allows users to manage tasks. It includes endpoints for listing, adding, updating, and deleting tasks. The API is secured using **Laravel Sanctum** for user authentication.

## Table of Contents
1. [Installation](#installation)
2. [API Endpoints](#api-endpoints)

---

## Installation

### 1. Clone the Repository
Clone this repository to your local machine:
```bash
git clone https://github.com/khaled-elsaeed/task-manager.git
cd task-manager
```

### 2. Install Dependencies
Install the required dependencies using Composer:
```bash
composer install
```

### 3. Set Up Environment Variables
Copy the `.env.example` file to `.env` and update the necessary environment variables:
```bash
cp .env.example .env
```
Generate an application key:
```bash
php artisan key:generate
```

### 4. Run Migrations and Seed the Database
Run the migrations and seed the database with default data (including an admin user):
```bash
php artisan migrate --seed
```

### Default admin credentials:
- **Email:** admin@example.com
- **Password:** password123
