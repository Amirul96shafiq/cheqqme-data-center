![CheQQme Data Center](public/logos/logo-dark-vertical.png)

## Overview

**CheQQme Data Center** is an internal knowledge & operations hub built with Laravel + Filament, now featuring MCP server integration for AI/semantic search. It centralizes:

-   Important / frequently used URLs (SharePoint, tools, internal dashboards)
-   Client & project records
-   Internal documents & reference files
-   Action Tasks (Kanban style board)

It improves discoverability, reduces context switching, and lays groundwork for future AI-assisted search and MCP-powered automation.

## Core Objectives

-   Centralize scattered project & resource information
-   Provide fast navigation and lightweight task tracking (Action Board)
-   Serve as a structured dataset for upcoming AI / semantic search features
-   Personal full‑stack learning sandbox (Laravel, Filament, modern tooling)

---

## Tech Stack

| Area         | Tools / Frameworks                                              |
| ------------ | --------------------------------------------------------------- |
| Language     | PHP 8.2                                                         |
| Backend      | Laravel 12.20.0                                                 |
| Admin / UI   | Filament v3.3.31, Tailwind CSS v3.4                             |
| Realtime UX  | Livewire v3.6 (Filament integrated)                             |
| Database     | SQLite (dev) → MySQL/PostgreSQL (future)                        |
| MCP Server   | Node.js (Express 5), SQLite, bcrypt 6, dotenv 17                |
| Build Tools  | Vite, NPM                                                       |
| Testing      | PHPUnit 11, Laravel testing utilities, Livewire component tests |
| Activity Log | Spatie Activitylog + Filament Activitylog plugin (enabled)      |
| Kanban Board | Relaticle Flowforge v0.2 (custom Action Board page)             |

---

## Major Features (Current)

-   **AI-Powered Chatbot** with OpenAI integration
    -   Floating chat button accessible from anywhere in the app
    -   Context-aware responses about platform features and navigation
    -   Conversation memory and intelligent assistance
    -   Custom CheQQme persona for platform-specific help
-   MCP server integration (Node/Express) reading the same SQLite DB as Laravel
    -   Auth via `x-api-key` header; endpoints for users, tasks, comments, clients, projects, documents, important-urls, phone-numbers
    -   Password hash compatible: bcrypt `$2b$` is converted to `$2y$` for Laravel
-   Action Board (Kanban) using Relaticle Flowforge
    -   Columns: To Do / In Progress / To Review / Completed / Archived
    -   Due date color badges (red/yellow/gray/green), single assignee badge with self-highlighting, attachments counter
    -   Inline resource selectors for client, projects, documents, important URLs
-   Comments with @mentions on tasks
    -   Rich text with strict sanitization (only semantic tags/links)
    -   Mention extraction supports usernames and full names (longest-prefix match)
    -   Mentions rendered as inline badges at view-time; exact match only (no over-highlighting)
    -   Filament database notifications for mentioned users with deep-link to the task
    -   Leading/trailing whitespace in comments is prevented and validated
-   Activity & audit logging
    -   Spatie Activitylog across core models; Task move events logged with old/new status and order
    -   Filament Activitylog plugin page enabled in the admin panel
-   Entities & management UIs: Users, Clients, Projects, Documents, Important URLs, Phone Numbers, Tasks
-   Basic user authentication (Filament panel guarded) with database notifications (5s polling)
-   Consistent environment template (`.env.example`) for quick onboarding

## In Progress / Planned

-   Tagging system (polymorphic tags across Projects / Documents / Links)
-   Advanced authorization (policies & role-based access control)
-   Activity & audit logging (create/update/delete trails)
-   Full‑text / Scout based search + AI powered semantic layer (MCP integration)
-   Bulk import/export (CSV / XLSX)
-   Background job queue (async file processing & notifications)
-   S3 storage + signed download links

---

## First-Time Setup: CheQQme Data Center

1. Clone the repository:
    ```bash
    git clone <repo-url> cheqqme-data-center
    cd cheqqme-data-center
    ```
2. Install PHP and Node.js dependencies:
    ```bash
    composer install
    npm install
    ```
3. Create your environment file:
    ```bash
    cp .env.example .env
    php artisan key:generate
    # Edit .env and fill in any required values (DB, mail, MCP details)
    ```
4. Prepare the database and storage:
    ```bash
    php artisan migrate --seed   # if seeders available
    php artisan storage:link
    # If using SQLite, ensure database/database.sqlite exists
    ```
5. (Optional) Set up mail service for local testing:
    - The default `.env` uses [Mailtrap](https://mailtrap.io/) for safe email testing.
    - Sign up at Mailtrap and copy your SMTP credentials.
    - Update these values in your `.env`:
        ```properties
        MAIL_MAILER=smtp
        MAIL_HOST=sandbox.smtp.mailtrap.io
        MAIL_PORT=2525
        MAIL_USERNAME=your_mailtrap_username
        MAIL_PASSWORD=your_mailtrap_password
        MAIL_ENCRYPTION=tls
        MAIL_FROM_ADDRESS=noreply@cheqqme.local
        MAIL_FROM_NAME="CheQQme Data Center"
        ```
    - Emails sent by the app will appear in your Mailtrap inbox.
6. Access the app at [http://127.0.0.1:8000](http://127.0.0.1:8000) (Filament admin panel at /admin).

---

---

## MCP Server: First-Time Setup

1. Navigate to the `mcp-server` folder:
    ```bash
    cd mcp-server
    ```
2. Install dependencies:
    ```bash
    npm install
    ```
3. Copy the environment template and set your API key:
    ```bash
    cp .env.example .env
    # Edit .env and fill MCP_API_KEY with your chosen key
    ```
4. Start the MCP server:
    ```bash
    node index.js
    ```
5. The MCP API will be available at `http://127.0.0.1:5000/api` by default.

Refer to the main `.env` for connecting Laravel to MCP (MCP_ENDPOINT, MCP_API_KEY).

Example request:

```bash
curl -H "x-api-key: YOUR_KEY" http://127.0.0.1:5000/api/users
```

MCP API notes (current state):

-   Users GET/POST/PUT/DELETE work against the shared SQLite DB.
-   Some write endpoints for Tasks, Comments, Phone Numbers, and Important URLs are placeholders and reference column names that differ from the current Laravel schema. Prefer GET endpoints for these resources until aligned.

---

## Project Structure (Highlights)

```
app/
	Filament/          # Resources, Pages (incl. ActionBoard), Widgets
	Models/            # Core Eloquent models (Task, Project, Document, etc.)
resources/
	views/             # Blade templates
database/
	migrations/        # Schema definitions
	seeders/           # Database seeders and examples
mcp-server/           # Node.js MCP server (API endpoints, .env ignored)
routes/
	web.php            # Web routes (Filament auto-registers its own)
public/              # Public assets (built via Vite)
storage/             # Logs, cache, uploads (ignored from git)
```

---

## Selected Routes

-   `GET /admin` Filament panel (auto-registered resources for Users, Clients, Projects, Documents, Important URLs, Phone Numbers, Tasks)
-   `GET /admin/action-board` Action Board (Kanban)
-   `GET /action-board/assigned-active-count` JSON count for current user’s active assignments (used for nav badge)
-   Comments API (auth): `POST /comments`, `PATCH /comments/{comment}`, `DELETE /comments/{comment}`
-   Notifications: `POST /notifications/{id}/mark-as-read`

---

## Action Board (Kanban)

Custom Filament Page (`admin/action-board`) powered by Flowforge. Cards display:

-   **Due date badges**: red (<1 day), yellow (1–6 days), gray (7–13 days), green (≥14 days)
-   **Assignee badge**: shows username/short name; highlighted when assigned to the current user
-   **Attachments**: counter badge on the form, file storage under `storage/app/tasks`

Create uses a streamlined modal; edit navigates to the Task Resource edit page.

---

## Testing

-   Feature tests exist for the comment system, mentions extraction, and notifications
-   Livewire interaction tests cover add/edit/delete flows and validation rules
-   Run: `php artisan test`

Future: add comprehensive CRUD tests for Projects, Documents, Important URLs, and policy tests.

---

## Deployment (Basic Outline)

1. Provision server (Linux) with PHP 8.x + Nginx + database
2. Clone repo & run `composer install --no-dev`
3. Copy `.env.example` → `.env`, configure production values
4. Run: `php artisan key:generate` (one time)
5. Run migrations: `php artisan migrate --force`
6. Build assets: `npm ci && npm run build`
7. Set correct permissions for `storage` & `bootstrap/cache`
8. Configure queue worker & scheduler (cron: `* * * * * php /path/artisan schedule:run >> /dev/null 2>&1`)
9. Use a process manager (Supervisor) for `php artisan queue:work`

---

## Authentication & Security

-   Laravel auth scaffolding + Filament guard
-   MCP server password hashes compatible with Laravel (bcrypt `$2b$` → `$2y$`)
-   Sensitive files/folders (`.env`, `database/`, `storage/`, etc.) are ignored from git tracking
-   Next steps: add Policies (Project, Document, URL) & roles field on `users` table
-   Consider rate limiting if public endpoints added later

---

## Roadmap (Detailed)

Legend: ✅ Done · 🛠 In Progress · 🔜 Planned

| Feature                 | Status | Notes                                  |
| ----------------------- | ------ | -------------------------------------- |
| **AI-Powered Chatbot**  | ✅     | OpenAI integration with custom persona |
| CRUD for Important URLs | ✅     | Core implemented                       |
| Projects & Clients      | ✅     | Base models & forms                    |
| Documents storage       | ✅     | Local disk; S3 planned                 |
| Action Board (Tasks)    | ✅     | Kanban columns + attribute badges      |
| Basic Auth (Filament)   | ✅     | Panel restricted                       |
| Environment template    | ✅     | `.env.example` structured              |
| Tagging system          | 🔜     | Polymorphic (tags)                     |
| Activity / Audit Log    | ✅     | Spatie + Filament plugin enabled       |
| Role-based Policies     | 🔜     | Granular access control                |
| Full‑text / AI Search   | 🔜     | Scout + embeddings layer               |
| Bulk Import / Export    | 🔜     | CSV/XLSX via Laravel Excel             |
| Background Queue        | 🔜     | For heavy file ops                     |
| S3 Integration          | 🔜     | Move FILESYSTEM_DRIVER to s3           |

---

## Local Development

In one terminal (Laravel app, queue worker, logs, Vite):

```bash
composer dev
```

In another terminal (MCP server):

```bash
cd mcp-server && node index.js
```

Notes:

-   Filament database notifications are enabled with 5s polling.
-   Comments sanitize HTML and strictly disallow leading/trailing whitespace.
-   Task resources include client/project/document/URL linkages stored as JSON arrays on the task.

---

## Contributions

Currently a solo project; outside contributions may open later. Feel free to fork for learning (respect proprietary data & branding).

---

## References

-   [Laravel Docs](https://laravel.com/docs/12.x)
-   [Filament Docs](https://filamentphp.com/docs)

---

## Author

Crafted by **Amirul** (Creative Designer & Aspiring Developer) with assistance from an AI coding companion.

---

## Notice

This repository is public for demonstration & portfolio purposes. Not licensed for commercial redistribution. All sensitive files and folders are now ignored from git tracking. Remove or anonymize any sensitive data before sharing.
