# Smart Ticket Triage & Dashboard

A production-ready Laravel 11 + Vue 3 single-page application (SPA) designed to simulate a help-desk system. This project empowers support teams to efficiently submit, classify, manage, and analyze support tickets using AI-powered categorization, real-time analytics, and intuitive overrides.

---

## Goal

Enable support teams to streamline their workflow by submitting support tickets, automatically classifying them with AI, viewing and filtering tickets in a searchable list, overriding AI classifications when necessary, adding internal notes, and monitoring overall progress through a live analytics dashboard.

---

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.2), SQLite, Laravel Queues  
- **AI Integration:** [openai-php/laravel](https://github.com/openai-php/laravel) (OpenAI API)  
- **Frontend:** Vue 3 (Options API), Vite, Vue Router  
- **Styling:** Plain CSS with BEM methodology  
- **Extras:** Chart.js (for dashboard visualizations), Faker (for seed data)

---

## Features

- Submit support tickets with subject, body, and requester email  
- AI-powered classification of tickets into categories (e.g., Billing, Bug, Access)  
- Searchable and paginated ticket list with filtering options  
- Detailed ticket view with ability to override AI classification and add or edit internal notes  
- Bulk classification of tickets queued asynchronously via Laravel Queues  
- Rate limiting on ticket submissions to prevent abuse  
- Real-time dashboard displaying ticket statistics, AI classification progress, and category breakdowns  
- Toggle between dark and light themes for user preference  
- Seeded demo data for quick setup and testing  
- CSV export of the ticket list, either as a client-side export of the current view or via a backend `?format=csv` endpoint  
- Meets the handout’s *Single Build* requirement by bundling the Vue SPA into Laravel’s `/public` directory

---

## Assumptions & Trade-offs

- SQLite is used for simplicity and ease of setup; suitable for demonstration and small-scale usage but may not scale for large production environments.  
- AI classification relies on OpenAI API calls, which introduces latency and potential cost considerations.  
- Real-time updates are implemented using polling or Laravel broadcasting depending on environment constraints.  
- Styling prioritizes clarity and functionality over visual polish, using BEM conventions for maintainability.  
- The frontend leverages Vue 3 Options API for readability and ease of onboarding.  
- Designed primarily for local development and demonstration rather than full production deployment.

---

## What I’d Do With More Time

- Implement WebSocket-based real-time updates for ticket status and dashboard metrics to improve responsiveness.  
- Add user authentication and role-based access control to secure ticket management and dashboard views.  
- Enhance AI classification with custom training or feedback loops to improve accuracy over time.  
- Improve UI/UX design with more polished styling, animations, and accessibility features.  
- Add comprehensive test coverage for backend APIs and frontend components.  
- Support multi-language localization and internationalization.  
- Integrate email notifications for ticket updates and escalations.

---

## Setup (10 Steps)

1. **Clone the repository**

   ```bash
   git clone https://github.com/JoshCodeAligator/STTD-Take-Home.git
   cd STTD-Take-Home
   ```

2. **Install backend dependencies**

   ```bash
   composer install
   ```

3. **Install frontend dependencies**

   ```bash
   cd spa-vue
   npm install
   cd ..
   ```

4. **Copy and configure environment file**

   ```bash
   cp .env.example .env
   ```

   Then, update `.env` with your OpenAI API key and correct database path:

   ```
   OPENAI_API_KEY=your_openai_api_key_here
   DB_CONNECTION=sqlite
   DB_DATABASE=/absolute/path/to/database/database.sqlite
   ```

5. **Create the SQLite database file**

   ```bash
   touch database/database.sqlite
   ```

6. **Run database migrations and seed demo data**

   ```bash
   php artisan migrate --seed
   ```

7. **Start the Laravel backend server**

   ```bash
   php artisan serve
   ```

   The API will be available at: `http://localhost:8000`

8. **Run the queue worker**

   Open a new terminal and run:

   ```bash
   php artisan queue:work
   ```

9. **Start the Vue frontend development server (Development Mode)**

   ```bash
   cd spa-vue
   npm run dev
   ```

   This will serve the SPA at `http://localhost:5173` for rapid development with hot reloads.

   **OR**

   **Build the SPA bundle for production (Submission Mode)**

   ```bash
   npm run build
   ```

   After building, run `php artisan serve` and `php artisan queue:work` to serve both the SPA and API from Laravel.

10. **Access the application**

    - In **Development Mode**, open your browser to [http://localhost:5173](http://localhost:5173) to submit and manage support tickets.

    - In **Submission Mode (Single Build)**, access the SPA served by Laravel at [http://localhost:8000](http://localhost:8000).

---

## Accessing the Application

- **Frontend (Vue SPA):**  
  - In Development Mode, visit [http://localhost:5173](http://localhost:5173) to submit and manage support tickets.  
  - In Submission Mode (Single Build), the SPA is served from Laravel’s `/public` directory at [http://localhost:8000](http://localhost:8000).

- **Backend API (Laravel):**  
  API endpoints are served at [http://localhost:8000](http://localhost:8000).

- **Dashboard:**  
  Accessible within the frontend SPA at `/dashboard` for real-time ticket analytics and AI progress.

---

## Repository

[https://github.com/JoshCodeAligator/STTD-Take-Home](https://github.com/JoshCodeAligator/STTD-Take-Home)