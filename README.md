# JobGenie.ai

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

## <a id="tech-stack"></a>🚀 Features

-   **Career pack workflow** – Stores every job, calls Gemini via prompt templates, and captures cover letters, tailored resumes, salary ranges (native + USD), and ATS scores per session.
-   **Job tracking pipeline** – Manage statuses (`Not applied`, `Applied`, `Interview scheduled`, etc.), review timelines, and regenerate content whenever the JD or resume changes.
-   **Conversation helpers** – Interview prep, negotiation, and follow-up generators with full history saved per job.
-   **Prompt management** – Admin UI to edit prompts, clone versions, and toggle activity without redeploying.
-   **LLM configuration overrides** – UI to switch provider/model and manage encrypted API keys overriding `.env`.
-   **PostgreSQL + queues** – Database-backed sessions, conversations, and queue tables (DB driver configured with dedicated `queue_jobs` table).
-   **Dockerized dev stack** – PHP-FPM, Nginx, and PostgreSQL services wired for local development.

🔮 AI Career Co-Pilot

Understand any job instantly. Get actionable insights tailored to your resume and background.

📝 Smart Document Generator

Generate cover letters, resume edits, ATS-friendly summaries, and communication templates.

💬 Interview Trainer

Practice with AI-generated questions, ideal answers, and role-specific preparation guides.

💸 Salary Intelligence

Get realistic salary expectations using job description + user experience + market data logic.

🧩 Prompt Management System

Edit prompts from the UI — no redeploy needed.

🗄️ Job Tracking Dashboard

Track applications, status updates, and interview stages.

🧠 LLM-Ready Architecture

Multi-provider support

Gemini API integrated

Versioned prompt storage

Modular LlmServiceInterface

<!-- — all backed by a prompt-managed LLM stack. -->

JobGenie.ai is an AI-powered web application designed to streamline the job application and hiring process. It empowers **job seekers** to create tailored cover letters and estimate expected salaries based on industry standards, experience, and location. Simultaneously, it assists **employers** in identifying and connecting with top talent by leveraging AI-driven insights. The app fosters career growth for job seekers and simplifies talent acquisition for companies.

---

## <a id="tech-stack"></a>🧰 Tech Stack

| Area                 | Technologies Used                                                            |
| -------------------- | ---------------------------------------------------------------------------- |
| **Backend**          | Laravel 12, PHP 8.2, Service layer, Repositories                             |
| **Frontend**         | Blade, TailwindCSS, Alpine.js, Vite                                          |
| **AI / LLM**         | Google Gemini LLM integration, Pluggable service layer `LlmServiceInterface` |
| **Database**         | PostgreSQL (primary), MySQL compatible, database queue driver.               |
| **Queues / Caching** | Redis or database queues                                                     |
| **Auth**             | Laravel Breeze-style auth (session-based, Blade + Tailwind + Alpine)         |
| **Config Mgmt**      | `.env` + `app_configs` table for runtime overrides                           |
| **Prompt Mgmt**      | `prompts` table, `PromptService`, admin UI                                   |
| **Deployment**       | Native PHP / Nginx, optional Docker setup                                    |
| **Build Tools**      | Composer, NPM, Vite                                                          |

---

## Docker workflow

The repository ships with a production-ready Dockerfile and a local docker-compose stack.

### Build & boot

```bash
docker-compose up --build -d
# first-time setup
docker-compose exec app php artisan migrate --seed
```

-   App: http://localhost:8080
-   Postgres: exposed on `localhost:5432`

The `app` service mounts the current workspace, so local file changes are reflected immediately. Run artisan commands via `docker-compose exec app php artisan <command>` and start Vite with `docker-compose exec app npm run dev`.

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

Visit http://127.0.0.1:8000 to log in.

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

## Testing & queues

-   Queue driver defaults to `database` with a dedicated `queue_jobs` table (`config/queue.php` updated). Run `php artisan queue:work` to process background jobs when you introduce them.
-   PHPUnit configuration ships with Laravel defaults; add feature/unit tests as needed.

---

## Credentials

After seeding, log in with:

-   Email: `admin@example.com`
-   Password: `password`

Update the profile to include your resume text and native currency for the best results.

## 💬 Why JobGenie.ai?

Job searching is stressful, slow, and often lonely.  
JobGenie.ai turns it into a **guided**, **data-backed**, and **AI-assisted** experience.

If you want:

-   Better applications
-   Faster iterations
-   Smarter interviews
-   Stronger negotiation & follow-ups

…then JobGenie.ai is built for you.

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

## <a id="pricing"></a>💸 Pricing (Concept)

| Plan           | Ideal For              | Features                                                       |
| -------------- | ---------------------- | -------------------------------------------------------------- |
| **Free**       | Individual job seekers | Limited monthly career packs, basic ATS & salary insights      |
| **Pro**        | Power users            | Unlimited jobs, deeper ATS analytics, multiple resume profiles |
| **Team**       | Agencies / bootcamps   | Shared workspaces, shared prompts, centralized analytics       |
| **Enterprise** | Platforms / HR tools   | API access, SSO, custom LLM routing, white-label options       |

> The open-source foundation focuses on the engine and flows; SaaS pricing is adaptable per market.

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
-   Portfolio: https://shaonresume.netlify.app
-   LinkedIn: https://linkedin.com/in/shaonmajumder
-   Medium: https://medium.com/@shaonmajumder
-   GitHub: https://github.com/ShaonMajumder

Specialized in scalable APIs, distributed systems, and AI integration (OpenAI, Gemini, MCP), with a track record of leading engineering teams and shipping high-impact platforms across healthcare, fintech, telecom, logistics, garments, and e-commerce.
