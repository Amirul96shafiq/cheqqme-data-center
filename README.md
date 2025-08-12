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

| Area         | Tools / Frameworks                             |
| ------------ | ---------------------------------------------- |
| Language     | PHP 8.x                                        |
| Backend      | Laravel 12.x                                   |
| Admin / UI   | FilamentPHP v3, Tailwind CSS                   |
| Realtime UX  | Livewire (Filament integrated)                 |
| Database     | SQLite (dev) → MySQL/PostgreSQL (future)       |
| MCP Server   | Node.js (Express), SQLite, bcrypt, dotenv      |
| Build Tools  | Vite, NPM                                      |
| Testing      | PHPUnit, Laravel test utilities                |
| Activity Log | spatie/laravel-activitylog (planned wiring)    |
| Kanban Board | Relaticle Flowforge (custom Action Board page) |

---

## Major Features (Current)

-   MCP server integration for context API and semantic search
-   Password hash compatibility between MCP and Laravel (bcrypt $2b$ → $2y$)
-   API endpoints for user creation, important URLs, and tasks (tested via Postman)
-   URL / Link management with metadata & creator tracking
-   Project & Client entities (foundation for relationships)
-   Document records (file storage ready for local → S3 migration)
-   Action Board (Kanban) for internal tasks (columns: To Do / In Progress / To Review / Completed / Archived)
-   Basic user authentication (Filament panel guarded)
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

---

## Project Structure (Highlights)

```
app/
	Filament/          # Resources, Pages (incl. ActionBoard), Widgets
	Models/            # Core Eloquent models (Task, Project, Document, etc.)
resources/
	views/             # Blade templates
database/
	migrations/        # Schema definitions (not tracked in repo)
	seeders/           # (Add seeders for demo data, not tracked)
mcp-server/           # Node.js MCP server (API endpoints, .env ignored)
routes/
	web.php            # Web routes (Filament auto-registers its own)
public/              # Public assets (built via Vite)
storage/             # Logs, cache, uploads (ignored from git)
```

---

## Action Board (Kanban)

Implemented via a custom Filament Page extending a Kanban board component. Columns reflect task lifecycle. Each card shows due date color indicators and assignment status. Future upgrades: inline comment thread, drag ordering persistence optimization, filters & swimlanes.

---

## Testing Strategy (Planned)

-   Feature tests: CRUD for Projects, Documents, Important URLs
-   Livewire component tests: Task comments & board interactions
-   Policy tests once authorization is introduced

Add seeds + factories to simplify realistic scenario coverage.

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
-   MCP server password hashes compatible with Laravel (bcrypt $2b$ → $2y$)
-   Sensitive files/folders (`.env`, `database/`, `storage/`, etc.) are ignored from git tracking
-   Next steps: add Policies (Project, Document, URL) & roles field on `users` table
-   Consider rate limiting if public endpoints added later

---

## Roadmap (Detailed)

Legend: ✅ Done · 🛠 In Progress · 🔜 Planned

| Feature                 | Status | Notes                             |
| ----------------------- | ------ | --------------------------------- |
| CRUD for Important URLs | ✅     | Core implemented                  |
| Projects & Clients      | ✅     | Base models & forms               |
| Documents storage       | ✅     | Local disk; S3 planned            |
| Action Board (Tasks)    | ✅     | Kanban columns + attribute badges |
| Basic Auth (Filament)   | ✅     | Panel restricted                  |
| Environment template    | ✅     | `.env.example` structured         |
| Tagging system          | 🔜     | Polymorphic (tags)                |
| Activity / Audit Log    | 🔜     | Wire spatie/activitylog           |
| Role-based Policies     | 🔜     | Granular access control           |
| Full‑text / AI Search   | 🔜     | Scout + embeddings layer          |
| Bulk Import / Export    | 🔜     | CSV/XLSX via Laravel Excel        |
| Background Queue        | 🔜     | For heavy file ops                |
| S3 Integration          | 🔜     | Move FILESYSTEM_DRIVER to s3      |

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
