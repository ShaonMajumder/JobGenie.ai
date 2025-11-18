## JobGenie.ai

JobGenie.ai is a Laravel-powered SaaS application that acts as a career co-pilot for job seekers. Paste a job description, baseline salary, and resume to instantly generate a tailored cover letter, resume revisions, salary guidance, ATS feedback, interview prep, negotiation scripts, and follow-up helpers — all backed by a prompt-managed LLM stack.

### Features
- **Career pack workflow** – Stores every job, calls Gemini via prompt templates, and captures cover letters, tailored resumes, salary ranges (native + USD), and ATS scores per session.
- **Job tracking pipeline** – Manage statuses (`Not applied`, `Applied`, `Interview scheduled`, etc.), review timelines, and regenerate content whenever the JD or resume changes.
- **Conversation helpers** – Interview prep, negotiation, and follow-up generators with full history saved per job.
- **Prompt management** – Admin UI to edit prompts, clone versions, and toggle activity without redeploying.
- **LLM configuration overrides** – UI to switch provider/model and manage encrypted API keys overriding `.env`.
- **PostgreSQL + queues** – Database-backed sessions, conversations, and queue tables (DB driver configured with dedicated `queue_jobs` table).
- **Dockerized dev stack** – PHP-FPM, Nginx, and PostgreSQL services wired for local development.

### Tech Stack
- Laravel 12, PHP 8.2, Breeze authentication (Blade + Tailwind + Alpine).
- PostgreSQL for persistence, database queue driver.
- Google Gemini LLM integration via pluggable service layer.
- TailwindCSS for UI, Vite for asset compilation.

---

## Local Development

### 1. Clone & install dependencies
```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
```

### 2. Configure environment
- Update `.env` with your PostgreSQL credentials (defaults assume Docker Compose service names).
- Set `LLM_PROVIDER`, `LLM_MODEL_NAME`, and leave `GEMINI_API_KEY` blank until ready to supply a key.

### 3. Database & seeds
```bash
php artisan migrate --seed
```
This seeds the default prompt library and creates a sample admin user (`admin@example.com` / `password`).

### 4. Run the dev stack
```bash
php artisan serve
npm run dev
```
Visit http://127.0.0.1:8000 to log in.

---

## Docker workflow
The repository ships with a production-ready Dockerfile and a local docker-compose stack.

### Build & boot
```bash
docker-compose up --build -d
# first-time setup
docker-compose exec app composer install
docker-compose exec app npm install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
```
- App: http://localhost:8080
- Postgres: exposed on `localhost:5432`

The `app` service mounts the current workspace, so local file changes are reflected immediately. Run artisan commands via `docker-compose exec app php artisan <command>` and start Vite with `docker-compose exec app npm run dev`.

---

## LLM configuration
- `.env` provides baseline values: `LLM_PROVIDER=gemini`, `LLM_MODEL_NAME=gemini-1.5-pro`, and `GEMINI_API_KEY`.
- Admins can override provider/model/API key in **Settings → AI Configuration**. Override keys are encrypted in the `app_configs` table and take precedence over `.env`.
- The Gemini integration expects JSON responses for career packs; failures are logged and surfaced in the UI.

---

## Prompt Management
Admins (users with `is_admin = true`) can edit prompts in **Admin → Prompts**:
- Clone a prompt to increment the version and automatically activate the new record.
- Toggle active/inactive states without deleting history.
- Editing or cloning a prompt automatically flushes the prompt cache so the new content is used immediately.

Default prompts seeded include:
- Career pack system/user
- Interview prep system/user
- Negotiation helper system/user
- Follow-up helper system/user

---

## Testing & queues
- Queue driver defaults to `database` with a dedicated `queue_jobs` table (`config/queue.php` updated). Run `php artisan queue:work` to process background jobs when you introduce them.
- PHPUnit configuration ships with Laravel defaults; add feature/unit tests as needed.

---

## Credentials
After seeding, log in with:
- Email: `admin@example.com`
- Password: `password`

Update the profile to include your resume text and native currency for the best results.
