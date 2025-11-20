# <img src="public/favicon.svg" alt="JobGenie.ai" style="height: 1em; vertical-align: middle;"> JobGenie.ai

## Your AI-Powered Career Co-Pilot — Apply Smarter, Interview Stronger, Get Hired Faster.

JobGenie.ai is an AI-powered SaaS platform that transforms the job search into a fast, guided, and confidence-boosting experience.

It works like a personal career coach — available 24/7.

Whether you're a job seeker wanting an edge or an employer needing better insights, JobGenie helps you win the hiring process.

<p align="center">
  <a href="#demo">Demo</a> •
  <a href="#features">Features</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#testing">Testing</a> •
  <a href="#system-design">System Design</a> •
  <a href="#why-it-stands-out">Why it Stands Out</a> •
  <a href="#notes">Notes</a> •
  <a href="#pricing">Pricing</a> •
  <a href="#license">License</a> •
  <a href="#contribute">Contribute</a> •
  <a href="#revenue">Revenue Model</a> •
  <a href="#credit">Credit</a>
</p>

---

## <a id="demo"></a>🎬 Demo

![JobGenie.ai Demo](screenshots/demo-2025-05-19_12-25-57.gif)

Paste a job description, your resume, and your baseline salary — JobGenie instantly generates:

-   🎯 Tailored cover letters
-   📄 Resume rewrites & improvement suggestions
-   📊 ATS optimization insights
-   💰 Salary guidance based on industry/location
-   🎤 Interview prep with Q&A
-   🤝 Recruiter & negotiation scripts
-   🔁 Follow-up messages
-   📚 A full career “prep pack”

---

## <a id="features"></a>🚀 Features

### 1. Core AI Career Pack

For every job session, JobGenie generates:

-   🎯 **Tailored cover letters** aligned with the JD and your profile
-   📄 **Resume rewrites & improvement suggestions** to match required skills
-   📊 **ATS optimization insights** and an **ATS score** for that role
-   💰 **Salary guidance** in your native currency + USD (monthly and yearly)
-   🧮 **Salary baselines** based on:

    -   Market range
    -   Company’s country & job type (remote / hybrid / onsite, full-time / part-time / contract)
    -   Your skills and experience for that role

### 2. Smart Memory & Autofill

JobGenie remembers your key inputs so you don’t start from scratch every time:

-   ✅ Stores **resume text**, **native currency**, and **recent salary** in your profile
-   ✅ Saves **baseline salary**, **job title**, and **company info** per job
-   ✅ When you start a new job session:

    -   Your **last used resume**, **baseline salary**, and **currency** are **auto-filled**
    -   You can **edit or override** them anytime

-   ✅ Each job retains its own history: cover letters, resumes, ATS scores, salary ranges, and notes

This makes rapid-fire applications and iteration across many roles much faster.

### 3. Job Tracking & Interview Workflow

Turn chaotic job hunting into a clean pipeline:

-   🗂️ **Job tracking dashboard**

    -   Statuses like `Not applied`, `Applied`, `Interview scheduled`, `Offer received`, `Rejected`, etc.
    -   View all sessions, ATS scores, and salary insights per job

-   🔁 **Revisit any job**

    -   Regenerate or tweak cover letters & resumes
    -   Update baseline or target salary
    -   Re-apply or follow up when opportunities reopen

-   📅 **Interview & offer stages**

    -   Update status: `Call for interview`, `Offer letter received`, `Rejected`, etc.
    -   Each status unlocks **contextual helpers**:

        -   “Prepare for interview” prompts
        -   Negotiation helpers using your **email/chat transcripts**
        -   Follow-up templates & cadence suggestions

### 4. Conversation Helpers (Per Job)

Every job has its own AI “thread”:

-   🎤 **Interview prep**

    -   Role-specific Q&A
    -   Technical + behavioral questions
    -   Suggested STAR-style answers

-   🤝 **Negotiation helper**

    -   Paste recruiter emails or chat snippets
    -   Get response drafts that balance confidence and politeness
    -   Suggestions on when to push and when to compromise

-   🔁 **Follow-up assistant**

    -   Follow-up messages after interviews, ghosting, or rejections
    -   Re-approach templates when roles reopen
    -   Company revisit notes (website, careers page, application history)

All conversations are stored **per job**, so context is never lost.

### 5. Prompt Management & Experimentation

Built for people who love tuning prompts:

-   🧩 **Prompt management table**

    -   Single `prompts` table for all flows (career pack, ATS, interview, negotiation, follow-up, etc.)
    -   Fields: `slug`, `scope`, `role`, `version`, `is_active`, `content`, `description`, timestamps

-   🧪 **Clone & versioning**

    -   “Clone version” action in the UI
    -   New version gets `version + 1` and becomes active
    -   Older versions remain in history (can be re-activated)

-   🛠️ **Live editing**

    -   Edit system/user prompts from the web UI
    -   No redeploy needed
    -   Cache is flushed when prompts change so new runs use the latest copy

This makes JobGenie a powerful playground for **prompt engineering for careers**.

### 6. LLM & Configuration Layer

-   🔌 **LLM-agnostic architecture**

    -   Pluggable `LlmServiceInterface`
    -   Gemini is wired in by default, but you can add other providers

-   🔑 **Config hierarchy**

    -   Primary keys and models loaded from `.env`
    -   Admin UI allows:

        -   Overriding provider/model
        -   Securely storing encrypted API keys in DB

    -   UI overrides take precedence over `.env` but you can always fall back

### 7. Developer & SaaS Foundations

-   🗄️ **Persistent job sessions**

    -   PostgreSQL-backed storage for jobs, sessions, conversations, prompts, and configs

-   🧵 **Queues**

    -   Database or Redis queue support for background generation

-   🐳 **Dockerized dev stack**

    -   PHP-FPM + Nginx + PostgreSQL for local development

-   👥 **User accounts**

    -   Authenticated, per-user job history & preferences

-   💼 **SaaS-ready design**

    -   Clear separation of `users`, `jobs`, `job_sessions`, `job_conversations`, `prompts`, `app_configs`

JobGenie.ai is designed to serve **individual job seekers** today, and scale into **teams, agencies, and platforms** tomorrow.

---

## <a id="tech-stack"></a>🧰 Tech Stack

| Area                 | Technologies Used                                                                               |
| -------------------- | ----------------------------------------------------------------------------------------------- |
| **Backend**          | Laravel 12, PHP 8.2, Service layer, Repositories                                                |
| **Frontend**         | Blade, TailwindCSS, Alpine.js, Vite                                                             |
| **AI / LLM**         | Google Gemini LLM integration, pluggable service layer `LlmServiceInterface`                    |
| **Database**         | PostgreSQL (primary), MySQL compatible, database queue driver                                   |
| **Queues / Caching** | Redis or database queues                                                                        |
| **Auth**             | Laravel Breeze-style auth (session-based, Blade + Tailwind + Alpine)                            |
| **Config Mgmt**      | `.env` + `app_configs` table for runtime overrides                                              |
| **Prompt Mgmt**      | `prompts` table, `PromptService`, admin UI                                                      |
| **Deployment**       | Native PHP / Nginx, optional Docker setup                                                       |
| **Build Tools**      | Composer, NPM, Vite                                                                             |
| **Payment**          | Stripe Checkout + Billing webhooks                                                              |
| **Testing / QA**     | PHPUnit feature/unit tests, **Laravel Dusk** browser tests, Selenium Standalone Chrome (Docker) |

---

## Docker workflow

The repository ships with a production-ready Dockerfile and a local docker-compose stack.

### Build & boot

```bash
docker-compose up --build -d
# first-time setup
docker-compose exec app php artisan migrate --seed
```

-   App: [http://localhost:8000](http://localhost:8000)
-   Postgres: exposed on `localhost:5432`

The `app` service mounts the current workspace, so local file changes are reflected immediately. Run artisan commands via `docker-compose exec app php artisan <command>` and start Vite with:

```bash
docker-compose exec app npm run dev
```

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

-   Update `.env` with your PostgreSQL credentials (defaults assume Docker Compose service names).
-   Set `LLM_PROVIDER`, `LLM_MODEL_NAME`, and leave `GEMINI_API_KEY` blank until ready to supply a key.

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

Visit [http://127.0.0.1:8000](http://127.0.0.1:8000) to log in.

---

## LLM configuration

-   `.env` provides baseline values: `LLM_PROVIDER=gemini`, `LLM_MODEL_NAME=gemini-1.5-pro`, and `GEMINI_API_KEY`.
-   Admins can override provider/model/API key in **Settings → AI Configuration**. Override keys are encrypted in the `app_configs` table and take precedence over `.env`.
-   The Gemini integration expects JSON responses for career packs; failures are logged and surfaced in the UI.

---

## Prompt Management

Admins (users with `is_admin = true`) can edit prompts in **Admin → Prompts**:

-   Clone a prompt to increment the version and automatically activate the new record.
-   Toggle active/inactive states without deleting history.
-   Editing or cloning a prompt automatically flushes the prompt cache so the new content is used immediately.

Default prompts seeded include:

-   Career pack system/user
-   Interview prep system/user
-   Negotiation helper system/user
-   Follow-up helper system/user

---

## <a id="testing"></a>🧪 Testing

### PHPUnit

Standard Laravel testing is available out of the box:

```bash
# from the host
php artisan test

# or inside Docker
docker-compose exec app php artisan test
```

Use this for fast feature/unit tests that don’t require a browser.

### Browser tests with Laravel Dusk (Docker + Selenium)

JobGenie.ai includes **Laravel Dusk** integration for full end-to-end browser tests.

#### 1. Install Dusk (once per project)

```bash
composer require --dev laravel/dusk
php artisan dusk:install
```

Register the Dusk service provider only for non-production environments (typically already set up):

```php
// app/Providers/AppServiceProvider.php

public function register(): void
{
    if ($this->app->environment('local', 'testing', 'dusk')) {
        $this->app->register(\Laravel\Dusk\DuskServiceProvider::class);
    }
}
```

#### 2. Selenium service (Docker Compose)

In `docker-compose.yml` you can run Chrome via Selenium as a separate service:

```yaml
selenium:
    image: selenium/standalone-chrome:latest
    container_name: jobgenie-selenium
    restart: unless-stopped
    shm_size: "2gb"
    environment:
        - SE_NODE_MAX_SESSIONS=1
        - SE_NODE_SESSION_TIMEOUT=300
        - SE_VNC_NO_PASSWORD=1
    ports:
        - "4444:4444" # WebDriver endpoint
        - "7900:7900" # VNC / noVNC web UI
    networks:
        - jobgenie
```

You **do not** need Chromium inside the `app` container when using this approach — all real browser work happens in the `selenium` service.

Bring everything up:

```bash
docker-compose up --build -d
```

#### 3. Dusk environment `.env.dusk.local`

Dusk uses a dedicated environment file so you can isolate test configuration:

```env
# .env.dusk.local

APP_ENV=dusk
APP_URL=http://localhost:8000
APP_KEY=base64:your-copied-app-key-here

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=jobgenie
DB_USERNAME=jobgenie
DB_PASSWORD=secret

QUEUE_CONNECTION=sync
```

> Important: `APP_KEY` **must** be present; copy it from your main `.env` to avoid 500 errors like `No application encryption key has been specified` during Dusk runs.

#### 4. Dusk base test configuration

`tests/DuskTestCase.php` is configured to talk to the Selenium container:

```php
<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseMigrations;

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = new ChromeOptions();

        $options->addArguments([
            // Comment out for visible browser in Selenium VNC
            // '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ]);

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

        return RemoteWebDriver::create(
            'http://selenium:4444/wd/hub', // selenium service from docker-compose
            $capabilities
        );
    }
}
```

-   All Dusk tests run with a **fresh migrated database** via `DatabaseMigrations`.
-   The browser session is created against the `selenium` container.
-   You can connect to `http://localhost:7900` in a browser to **watch tests live** when headless is disabled.

#### 5. Example login test

`tests/Browser/LoginTest.php`:

```php
<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    public function test_admin_can_log_in_from_login_page(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password')]
        );

        $this->browse(function (Browser $browser) use ($user): void {
            $browser
                ->visit('/login')
                ->waitFor('#email', 5)
                ->type('#email', $user->email)
                ->type('#password', 'password')
                ->press('Log in')
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard');
        });
    }
}
```

#### 6. Running Dusk

From the host:

```bash
# run all Dusk tests inside the app container
docker-compose exec app php artisan dusk

# or a single test class
docker-compose exec app php artisan dusk --filter=LoginTest
```

If a test fails, the last browser screenshot and console logs are stored under `tests/Browser/screenshots` and `tests/Browser/console` to help you debug.

---

## Testing & queues (backend)

-   Queue driver defaults to `database` with a dedicated `queue_jobs` table (`config/queue.php` updated).
-   Run `php artisan queue:work` (or `docker-compose exec app php artisan queue:work`) to process background jobs when you introduce them.
-   PHPUnit configuration ships with Laravel defaults; extend with your own feature/unit tests as needed.

---

## Credentials

After seeding, log in with:

-   Email: `admin@example.com`
-   Password: `password`

Update the profile to include your resume text and native currency for the best results.

---

## <a id="why-it-stands-out"></a>🛡️ Why JobGenie.ai Stands Out

-   ⚙️ **Built for real job search workflows**
    Not just a playground for prompts—every feature is aligned with a real pipeline: JD → application → interviews → offer or rejection → re-approach.

-   🧠 **Prompt-centric & UI-tunable**
    Prompts are first-class citizens in the database and UI, making it easy to experiment without redeploying.

-   🔌 **LLM-agnostic core**
    Start with Gemini, plug in any other LLM later with a single service interface.

-   🗂️ **Job-aware memory**
    Every job keeps its own history: sessions, conversations, ATS insights, salary context.

-   🚀 **SaaS-ready design**
    Clean separation of users, jobs, prompts, configs — ready for subscriptions, teams, and usage-based billing.

---

## <a id="notes"></a>🧠 Development Notes (WIP)

-   Planned features and improvements:

    -   SMTP failure should guide the app to set up SMTP through the web interface
    -   If an API key is missing, the app should show a message to set it up through the web interface
    -   Explore whether we can rewrite selected `.env` values through the web admin (with careful safeguards)
    -   If there is no subscription plan, the app should show a message to configure plans via the web interface
    -   There is no dedicated marketing landing page yet; add a public-facing page + Google auth onboarding
    -   Add registration/login with Google

-   🚧 Adding richer analytics for:

    -   Per-job success probability signals
    -   Which prompts lead to higher ATS scores

-   🔜 Multi-tenant mode for teams and agencies
-   📊 Advanced ATS insights grouped by:

    -   Skills, responsibilities, culture fit, and seniority level

-   📤 Export options:

    -   PDF / DOCX career packs
    -   Email-ready templates

---

## <a id="pricing"></a>💵 Pricing (Concept)

| Plan               | Mode     | Ideal For              | Highlights                                                                   |
| ------------------ | -------- | ---------------------- | ---------------------------------------------------------------------------- |
| **JobSeeker Free** | Prepaid  | Individual job seekers | 20k prepaid AI tokens with a hard stop, billing portal, invoice archive      |
| **Pro**            | Postpaid | Power users            | 200k included tokens + overage metering, Gemini/OpenAI ready                 |
| **Team**           | Postpaid | Agencies / bootcamps   | Shared workspaces, pooled token reporting, centralized invoices              |
| **Token Starter**  | Prepaid  | Token bundle buyers    | Purchase fixed token packs per month with low/out-of-token alerts            |
| **Enterprise**     | Hybrid   | Platforms / HR tools   | API access, SSO, custom LLM routing, Stripe-based billing hooks, white-label |

> The open-source foundation focuses on the engine and flows; SaaS pricing is adaptable per market.

### Billing & Subscriptions

-   Full subscription domain model (plans, subscriptions, invoices, AI usage records) backed by PostgreSQL migrations.
-   Stripe Checkout integration (hosted redirect + Elements fallback) with Laravel services for account, subscription, and invoice syncing.
-   Customer billing portal (`/billing`) showing active plan, prepaid/postpaid labels, remaining tokens, invoices, and one-click upgrades.
-   Admin billing console for managing plans, AI pricing overrides, and invoice monitoring.
-   AI metering that records input/output tokens, costs per provider/model, prepaid token enforcement, and low/out-of-token notifications.
-   Monthly invoice generator (`php artisan billing:generate-monthly-invoices`) that combines subscription fees + AI usage and emails customers.

---

## <a id="license"></a>📜 License

JobGenie.ai is currently structured as a **portfolio / starter SaaS project**.

When taking it public, you can choose a license such as:

-   **MIT** – for maximal open-source adoption
-   **AGPL-3.0** – to ensure improvements to hosted versions are shared
-   **Commercial / dual-licensed** – for protecting proprietary SaaS offerings

Pick the license that best matches your goals for community vs. commercial use.

---

## <a id="contribute"></a>🤝 Contribute

Ideas and contributions are welcome, especially around:

-   Better prompt design for ATS and salary guidance
-   New interview patterns for specific roles (backend, data, PM, etc.)
-   Additional LLM integrations and evaluation tooling

You can fork, adapt, and extend JobGenie.ai into your own product or internal tool.

---

## <a id="revenue"></a>💰 Revenue Model (If Commercialized)

-   **Freemium SaaS**

    -   Free tier with limited packs and basic insights
    -   Paid tiers for volume, analytics, and more advanced workflows

-   **B2B integrations**

    -   Offer the engine to job boards, bootcamps, HR SaaS as a white-label component

-   **Enterprise customizations**

    -   Dedicated hosting, data residency, compliance, and deep ATS integrations

This creates a sustainable path while still enabling an open-source or hybrid core.

---

## <a id="credit"></a>👨‍💻 Built & Maintained By

👔 Actively exploring CTO-track, Staff/Principal Engineer, System Architect, and Engineering Leadership roles
📨 Let’s connect for high-impact backend, AI, platform, or architecture-led positions

**Shaon Majumder**
Senior Software Engineer / Engineering Manager → CTO-Track | AI & Scalability
Open source contributor | Laravel ecosystem expert | System design & architecture advocate

-   Email: `smazoomder@gmail.com`
-   Portfolio: [https://shaonresume.netlify.app](https://shaonresume.netlify.app)
-   LinkedIn: [https://linkedin.com/in/shaonmajumder](https://linkedin.com/in/shaonmajumder)
-   Medium: [https://medium.com/@shaonmajumder](https://medium.com/@shaonmajumder)
-   GitHub: [https://github.com/ShaonMajumder](https://github.com/ShaonMajumder)

Specialized in scalable APIs, distributed systems, and AI integration (OpenAI, Gemini, MCP), with a track record of leading engineering teams and shipping high-impact platforms across healthcare, fintech, telecom, logistics, garments, and e-commerce.
