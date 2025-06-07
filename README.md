# Champions League Simulation

A Laravel + Vue.js application that simulates a Champions League tournament with 4 teams.

## Features

- League table with Premier League scoring rules
- Match simulation based on team strengths
- Week-by-week match results
- Championship predictions after week 4 using Monte Carlo simulation
- Edit match results functionality
- Play all matches automatically

## Tech Stack

- **Backend**: Laravel (PHP)
- **Frontend**: Vue.js 3 with Tailwind CSS
- **Database**: MySQL/SQLite
- **Build Tool**: Vite

## Installation

1. Clone the repository
2. Install dependencies:
```bash
composer install
npm install
```

3. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Run migrations and seed data:
```bash
php artisan migrate:fresh --seed
```

5. Initialize league fixtures:
```bash
php artisan league:init
```

6. Build frontend assets:
```bash
npm run build
```

## Running the Application

1. Start the Laravel server:
```bash
php artisan serve
```

2. For development, run Vite:
```bash
npm run dev
```

Visit `http://localhost:8000` to view the application.

## Testing

Run the test suite:
```bash
php artisan test
```

## API Endpoints

- `GET /api/standings` - Get current league standings
- `GET /api/current-week` - Get current week information
- `GET /api/matches` - Get all matches
- `GET /api/predictions` - Get championship predictions (after week 4)
- `POST /api/simulate-week` - Simulate specific week matches
- `POST /api/simulate-all` - Simulate all remaining matches
- `POST /api/reset-league` - Reset the league
- `PUT /api/matches/{id}` - Update match result