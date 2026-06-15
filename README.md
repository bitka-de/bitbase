# BitBase

Modernes Laravel-Projekt mit:

- eigener Public-Layoutstruktur
- Fortify-Authentifizierung (Login mit Benutzername)
- abgesichertem Admin-Bereich mit Rollen-Middleware
- Stylebook-Seite fur Design-Referenz
- eigenen 404- und 500-Fehlerseiten

## Tech Stack

- PHP 8.3
- Laravel 13.8
- Laravel Fortify
- Blade + Vite
- Tailwind CSS v4
- Blade Heroicons

## Features

- Public Seitenlayout mit Header/Footer und SEO-Meta-Sections
- JSON-LD Partial fur strukturierte Daten
- eigener Auth-Flow mit eigenem Login-Layout
- Admin-Dashboard unter geschutzter Route
- role-basierte Zugangskontrolle uber Middleware (`admin`)
- Stylebook unter `/stylebook`
- Fallback-404 und custom 500-Error-Page

## Routen

- `/` -> Startseite
- `/login` -> Fortify Login (custom View)
- `/admin` -> Admin Dashboard (nur `auth` + `admin`)
- `/stylebook` -> Design- und Komponentenubersicht
- Fallback -> 404

## Schnellstart

### 1. Voraussetzungen

- PHP 8.3+
- Composer
- Node.js + npm
- Datenbank (z. B. SQLite, MySQL, MariaDB)

### 2. Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
```

### 3. Datenbank einrichten

```bash
php artisan migrate --seed
```

### 4. Entwicklung starten

```bash
composer run dev
```

Alternative mit getrennten Prozessen:

```bash
php artisan serve
npm run dev
```

### 5. Production Build

```bash
npm run build
```

## Default Admin Account (lokal)

Wird uber den Seeder erstellt:

- Benutzername: `admin`
- E-Mail: `admin@example.com`
- Passwort: `pass123`
- Rolle: `admin`

Hinweis: Zugangsdaten nur fur lokale Entwicklung verwenden.

## Auth & Admin Details

- Fortify nutzt `name` als Username-Feld (`config/fortify.php`).
- Nach Login erfolgt Redirect auf `/admin`.
- Admin-Schutz lauft uber Middleware `EnsureUserIsAdmin`.
- Middleware-Alias `admin` ist in `bootstrap/app.php` registriert.
- Rolle ist uber Migration im Feld `users.role` umgesetzt.

## Projektstruktur (relevant)

- `routes/web.php` - Public-, Admin-, Stylebook- und Fallback-Routen
- `resources/views/layouts/default.blade.php` - Public Basislayout
- `resources/views/layouts/auth.blade.php` - Auth Layout
- `resources/views/layouts/admin.blade.php` - Admin Layout
- `resources/views/pages/home.blade.php` - Startseite
- `resources/views/pages/admin/dashboard.blade.php` - Dashboard
- `resources/views/pages/stylebook.blade.php` - Stylebook
- `resources/views/errors/404.blade.php` - Not Found Seite
- `resources/views/errors/500.blade.php` - Server Error Seite
- `resources/views/partials/seo/json-ld.blade.php` - strukturierte Daten
- `resources/css/app.css` - zentrales Theme / Design Tokens / Komponenten

## QA / Validierung

Nutzliche Befehle nach Anderungen:

```bash
php artisan view:cache
npm run build
php artisan test
```

## Lizenz

Dieses Projekt basiert auf Laravel (MIT License).
# bitbase
