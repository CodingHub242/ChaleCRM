# Zoho Bigin Clone - CRM Application

A full-featured CRM application similar to Zoho Bigin, built with Ionic/Angular frontend and Laravel backend.

## Features

- **Dashboard** - Overview of CRM metrics and statistics
- **Contacts** - Manage customer contacts with lead status
- **Companies** - Manage business accounts
- **Deals/Pipeline** - Track sales opportunities through stages
- **Tasks** - Task management with priorities and assignments
- **Activities** - Track calls, meetings, notes, and emails
- **Products** - Product catalog for quotes and invoices
- **Quotes** - Create and send quotes to customers
- **Invoices** - Generate invoices and track payments
- **Authentication** - User registration and login with JWT

## Tech Stack

### Frontend
- Angular 20
- Ionic 8
- Capacitor

### Backend
- Laravel 10+
- MySQL/PostgreSQL
- Laravel Sanctum (API Authentication)

## Setup Instructions

### Prerequisites

- Node.js 18+
- PHP 8.1+
- Composer
- MySQL or PostgreSQL

---

### Backend Setup (Laravel)

1. **Create a new Laravel project:**
```bash
composer create-project laravel/laravel bigin-backend
cd bigin-backend
```

2. **Install Laravel Sanctum:**
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

3. **Configure database (.env):**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bigin_crm
DB_USERNAME=root
DB_PASSWORD=
```

4. **Copy the models:**
- Copy `laravel-backend/app/Models/*.php` to `app/Models/`

5. **Copy the controller:**
- Copy `laravel-backend/app/Http/Controllers/ApiController.php` to `app/Http/Controllers/`

6. **Copy the routes:**
- Copy `laravel-backend/routes/api.php` content to `routes/api.php`

7. **Run migrations:**
```bash
php artisan migrate
```

8. **Start the server:**
```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

---

### Frontend Setup (Ionic/Angular)

1. **Navigate to the frontend directory:**
```bash
cd zoho-bigin-clone
```

2. **Install dependencies:**
```bash
npm install
```

3. **Update API URL:**
Edit `src/environments/environment.ts`:
```typescript
export const environment = {
  production: false,
  apiUrl: 'https://ecg.codepps.online/api',  // Your Laravel API URL
};
```

4. **Run the app:**
```bash
npm start
```

The app will be available at `http://localhost:4200`

### Building for Mobile (Optional)

1. **Add platforms:**
```bash
npx cap add android
npx cap add ios
```

2. **Build and sync:**
```bash
npm run build
npx cap sync
```

3. **Open in IDE:**
```bash
npx cap open android
npx cap open ios
```

---

## API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout
- `GET /api/auth/user` - Get current user

### Dashboard
- `GET /api/dashboard` - Get dashboard statistics

### Contacts
- `GET /api/contacts` - List contacts
- `GET /api/contacts/{id}` - Get contact details
- `POST /api/contacts` - Create contact
- `PUT /api/contacts/{id}` - Update contact
- `DELETE /api/contacts/{id}` - Delete contact

### Companies
- `GET /api/companies` - List companies
- `POST /api/companies` - Create company
- `PUT /api/companies/{id}` - Update company
- `DELETE /api/companies/{id}` - Delete company

### Deals
- `GET /api/deals` - List deals
- `GET /api/deals/{id}` - Get deal details
- `POST /api/deals` - Create deal
- `PUT /api/deals/{id}` - Update deal
- `PUT /api/deals/{id}/stage` - Update deal stage
- `DELETE /api/deals/{id}` - Delete deal

### Tasks
- `GET /api/tasks` - List tasks
- `POST /api/tasks` - Create task
- `PUT /api/tasks/{id}` - Update task
- `PUT /api/tasks/{id}/complete` - Complete task
- `DELETE /api/tasks/{id}` - Delete task

### Activities
- `GET /api/activities` - List activities
- `POST /api/activities` - Create activity
- `DELETE /api/activities/{id}` - Delete activity

### Products
- `GET /api/products` - List products
- `POST /api/products` - Create product
- `PUT /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product

### Quotes
- `GET /api/quotes` - List quotes
- `POST /api/quotes` - Create quote
- `PUT /api/quotes/{id}` - Update quote
- `DELETE /api/quotes/{id}` - Delete quote

### Invoices
- `GET /api/invoices` - List invoices
- `POST /api/invoices` - Create invoice
- `PUT /api/invoices/{id}` - Update invoice
- `DELETE /api/invoices/{id}` - Delete invoice

### Users
- `GET /api/users` - List users (for assignment)

---

## Deal Stages

The default deal pipeline stages are:
1. New
2. Qualification
3. Needs Analysis
4. Proposal
5. Negotiation
6. Closed Won
7. Closed Lost

---

## License

MIT License
