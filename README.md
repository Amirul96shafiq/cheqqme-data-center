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

| Area         | Tools / Frameworks                                                   |
| ------------ | -------------------------------------------------------------------- |
| Language     | PHP 8.2.29                                                           |
| Backend      | Laravel 12.25.0                                                      |
| Admin / UI   | Filament v3.3.37, Tailwind CSS v3.4.17                               |
| Realtime UX  | Livewire v3.6.4 (Filament integrated)                                |
| Database     | SQLite (dev) → MySQL/PostgreSQL (future)                             |
| MCP Servers  | Node.js, SQLite, bcrypt 6.0.0, dotenv 17.2.1, Playwright MCP v0.0.37 |
| Build Tools  | Vite v7.1.3, Laravel Vite Plugin v2.0.0, NPM                         |
| Testing      | PHPUnit 11.5.34, Laravel testing utilities, Livewire component tests |
| Activity Log | Spatie Activitylog + Filament Activitylog plugin (enabled)           |
| Kanban Board | Relaticle Flowforge v0.2.1 (custom Action Board page)                |

---

## Major Features (Current)

-   **AI-Powered Chatbot** with OpenAI integration
    -   Floating chat button accessible from anywhere in the app
    -   Context-aware responses about platform features and navigation
    -   Conversation memory and intelligent assistance
    -   Custom CheQQme persona for platform-specific help
-   **Weather System** with intelligent location management
    -   Real-time weather data and 7-day forecasts via greeting modal
    -   Smart location detection: prioritizes user settings over auto-detection
    -   Manual location configuration in Settings page
    -   Automatic geolocation fallback when no location is set
    -   Location-based timezone support for sunrise/sunset times
    -   OpenWeatherMap API integration with 3-hour caching
-   MCP server integration (Node/Express) reading the same SQLite DB as Laravel
    -   Auth via `x-api-key` header; endpoints for users, tasks, comments, clients, projects, documents, important-urls, phone-numbers
    -   Password hash compatible: bcrypt `$2b$` is converted to `$2y$` for Laravel
-   Action Board (Kanban) using Relaticle Flowforge
    -   Columns: To Do / In Progress / To Review / Completed / Archived
    -   Due date color badges (red/yellow/gray/green), single assignee badge with self-highlighting, attachments counter
    -   Inline resource selectors for client, projects, documents, important URLs
-   **Advanced Comments System** with comprehensive features
    -   **Rich Text Editor**: Trix-based editor with strict HTML sanitization (only semantic tags/links)
    -   **@Mentions System**:
        -   Smart mention extraction supporting usernames and full names (longest-prefix match)
        -   Real-time user mention dropdown with search functionality
        -   @Everyone special mention for notifying all users
        -   Mentions rendered as inline badges at view-time with exact matching
        -   Filament database notifications for mentioned users with deep-link to tasks
    -   **Focus Mode**:
        -   Click-to-focus on individual comments for distraction-free reading
        -   Full-height scrollable view with sticky exit button
        -   Auto-hide composer and connecting lines in focus mode
        -   Smart dropdown integration with auto-close functionality
    -   **Comment Reactions**:
        -   Emoji reaction system with comprehensive picker
        -   Searchable emoji library with keyword matching
        -   Recent emojis tracking and quick access
        -   Real-time reaction updates with user attribution
        -   Reaction counts and user tooltips with timestamps
    -   **Threaded Replies**:
        -   Nested comment structure with visual hierarchy
        -   Reply-specific actions and editing capabilities
        -   Visual connecting lines and avatar indicators
    -   **Content Management**:
        -   Inline editing with live preview
        -   Soft delete with restoration capabilities
        -   Leading/trailing whitespace prevention and validation
        -   Activity logging for all comment operations
-   Activity & audit logging
    -   Spatie Activitylog across core models; Task move events logged with old/new status and order
    -   Filament Activitylog plugin page enabled in the admin panel
-   Entities & management UIs: Users, Clients, Projects, Documents, Important URLs, Phone Numbers, Tasks
-   Basic user authentication (Filament panel guarded) with database notifications (5s polling)
-   Consistent environment template (`.env.example`) for quick onboarding

## In Progress / Planned

-   Tagging system (polymorphic tags across Projects / Documents / Links)
-   Advanced authorization (policies & role-based access control)
-   Full‑text / Scout based search + AI powered semantic layer (MCP integration)
-   Bulk import/export (CSV / XLSX)
-   Background job queue (async file processing & notifications)
-   S3 storage + signed download links

---

## First-Time Setup: CheQQme Data Center

### **Prerequisites (If Programming Tools Not Installed)**

**For Windows:**

1. Install **Chocolatey** (Package Manager):

    ```bash
    # Run in PowerShell as Administrator
    Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
    ```

2. Install **PHP 8.2+**:

    ```bash
    choco install php
    choco install composer
    ```

3. Install **Node.js**:

    ```bash
    choco install nodejs
    ```

4. Install **Git**:
    ```bash
    choco install git
    ```

**For macOS:**

1. Install **Homebrew** (Package Manager):

    ```bash
    /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
    ```

2. Install **PHP 8.2+**:

    ```bash
    brew install php@8.2
    brew install composer
    ```

3. Install **Node.js**:

    ```bash
    brew install node
    ```

4. Install **Git**:
    ```bash
    brew install git
    ```

**For Ubuntu/Debian Linux:**

1. Update system:

    ```bash
    sudo apt update && sudo apt upgrade -y
    ```

2. Install **PHP 8.2+**:

    ```bash
    sudo apt install software-properties-common
    sudo add-apt-repository ppa:ondrej/php
    sudo apt update
    sudo apt install php8.2 php8.2-cli php8.2-common php8.2-mbstring php8.2-xml php8.2-zip php8.2-sqlite3 php8.2-curl php8.2-gd php8.2-bcmath
    ```

3. Install **Composer**:

    ```bash
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    ```

4. Install **Node.js**:

    ```bash
    curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
    sudo apt-get install -y nodejs
    ```

5. Install **Git**:
    ```bash
    sudo apt install git
    ```

**For All Systems - Verify Installation:**

```bash
php --version      # Should show PHP 8.2+
composer --version # Should show Composer version
node --version    # Should show Node.js version
npm --version     # Should show npm version
git --version     # Should show Git version
```

---

### **Project Setup (After Tools Are Installed)**

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
    # Create SQLite database file
    touch database/database.sqlite

    # Run migrations to create table structures
    php artisan migrate --no-interaction

    # Run seeders to populate with sample data
    php artisan db:seed --no-interaction

    # Create storage link
    php artisan storage:link
    ```

    **What gets created:**

    - **1 Test User** (email: `test@example.com`, password: `password`)
    - **5 Additional Users** with random data
    - **3 Sample Clients** with company information
    - **Multiple Projects** (1-3 per client)
    - **Documents** (1-2 per project)
    - **Important URLs** (1-2 per client)
    - **Phone Numbers** (1-2 per client)
    - **Tasks** (2-5 per project) with Kanban statuses
    - **Comments** (1-3 per task) with @mention support

    **Alternative seeding options:**

    ```bash
    # Run only specific seeder
    php artisan db:seed --class=SampleDataSeeder --no-interaction

    # Run only basic user seeder
    php artisan db:seed --class=DatabaseSeeder --no-interaction
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
7. **Default Login Credentials:**
    - **Email:** `test@example.com`
    - **Password:** `password`

---

## Weather System

### Overview

The application includes a comprehensive weather system with intelligent location management that provides real-time weather information through a greeting modal interface.

### Location Management Logic

The weather system uses a smart priority system for location detection:

#### 1. Manual Location Settings (Priority Mode)

-   **When**: User has configured location in Settings page
-   **Behavior**: Uses saved coordinates for all weather requests
-   **Process**: No automatic geolocation detection occurs
-   **Example**: Seoul (37.5665, 126.9780) → Always shows Seoul weather

#### 2. Automatic Geolocation Detection (Fallback Mode)

-   **When**: User hasn't configured location settings
-   **Behavior**: Automatically detects current browser location
-   **Process**: Uses browser geolocation API for weather requests
-   **Example**: No saved location → Detects and uses current location

### Location Detection Flow

```
Greeting Modal Opens
        ↓
Check User Location API
        ↓
Has Saved Location?
        ↓
    ┌─── YES ────→ Use Saved Location
    │
    └─── NO ────→ Auto-detect Current Location
```

### Testing Different Locations

#### Sample Coordinates

-   **Seoul, South Korea**: `37.5665, 126.9780`
-   **Tokyo, Japan**: `35.6762, 139.6503`
-   **New York, USA**: `40.7128, -74.0060`
-   **London, UK**: `51.5074, -0.1278`

#### Testing Steps

1. Go to Settings (`/admin/settings`)
2. Expand "Location & Timezone" section
3. Enter coordinates manually or use "Detect Location"
4. Save settings
5. Open greeting modal to see weather for configured location

### Technical Implementation

#### API Endpoints

-   `GET /weather/user-location` - Check if user has saved location
-   `GET /weather/current` - Get current weather data
-   `GET /weather/forecast` - Get 7-day weather forecast
-   `POST /weather/location` - Update user location

#### Key Features

-   **Smart Location Detection**: Prioritizes user settings over auto-detection
-   **Timezone Support**: Location-based sunrise/sunset times
-   **3-Hour Caching**: Reduces API calls with intelligent caching
-   **Error Handling**: Graceful fallback when API unavailable
-   **Coordinate Validation**: Proper bounds checking for latitude/longitude

#### Configuration

```env
OPENWEATHERMAP_API_KEY=your_api_key_here
WEATHER_CACHE_TTL_HOURS=3
WEATHER_DEFAULT_LATITUDE=3.1390
WEATHER_DEFAULT_LONGITUDE=101.6869
```

---

## MCP Server: First-Time Setup

### Data MCP Server (Existing)

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
-   Core endpoints for Tasks, Comments, Phone Numbers, and Important URLs are functional.
-   Password hash compatibility: bcrypt `$2b$` is converted to `$2y$` for Laravel.
-   All endpoints use `x-api-key` header authentication.

### Playwright MCP Server (Browser Automation)

The project now includes Playwright MCP server integration for browser automation, web scraping, E2E testing, and UI validation.

#### Quick Setup

1. **Install Playwright MCP** (already done):

    ```bash
    npm install -g @playwright/mcp
    ```

2. **Configure Cursor IDE**:

    - Go to Cursor Settings → MCP → Add new MCP Server
    - Name: `playwright`
    - Command: `npx @playwright/mcp`

3. **Start the server**:

    ```bash
    # Windows
    scripts\start-playwright-mcp.bat

    # Linux/Mac
    ./scripts/start-playwright-mcp.sh

    # Manual
    npx @playwright/mcp --headless --timeout-action 5000
    ```

4. **Verify installation**:
    ```bash
    node scripts/test-playwright-mcp.js
    ```

#### Use Cases

-   **Trello Integration**: Automate Trello board interactions for task management
-   **Client Data Collection**: Scrape client information from various sources
-   **Document Processing**: Convert web-based documents to PDF
-   **API Testing**: Verify external API endpoints and responses
-   **Dashboard Validation**: Ensure Filament admin panels work correctly
-   **User Workflow Testing**: Test complete user journeys through the application

#### Configuration

The project includes a pre-configured `playwright-mcp.config.json` with optimal settings for the CheQQme Data Center project.

For detailed documentation, see [docs/playwright-mcp-integration.md](docs/playwright-mcp-integration.md).

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

## Advanced Comments System

The application features a comprehensive comments system with modern, interactive capabilities:

### **Core Features**

#### **Rich Text Editor**

-   **Trix Integration**: Professional-grade rich text editor with toolbar
-   **HTML Sanitization**: Strict security with only semantic tags and links allowed
-   **Real-time Preview**: Live content rendering with proper formatting
-   **Keyboard Shortcuts**: Ctrl+Enter for quick comment submission

#### **@Mentions System**

-   **Smart Detection**: Automatic @mention recognition with longest-prefix matching
-   **User Search**: Real-time dropdown with username, email, and full name search
-   **@Everyone Support**: Special mention for notifying all users
-   **Visual Badges**: Mentions rendered as styled inline badges
-   **Notifications**: Automatic Filament notifications with deep-links to tasks

#### **Focus Mode**

-   **Click-to-Focus**: Distraction-free single comment viewing
-   **Full-Height View**: Scrollable full-height interface
-   **Smart UI**: Auto-hide composer and connecting lines
-   **Sticky Controls**: Persistent exit button at top
-   **Dropdown Integration**: Auto-close functionality for seamless UX

#### **Emoji Reactions**

-   **Comprehensive Picker**: Searchable emoji library with keyword matching
-   **Recent Tracking**: Quick access to frequently used emojis
-   **Real-time Updates**: Live reaction counts and user attribution
-   **User Tooltips**: Timestamp and user information on hover
-   **Toggle Functionality**: Add/remove reactions with visual feedback

#### **Threaded Replies**

-   **Nested Structure**: Visual hierarchy with proper indentation
-   **Reply Actions**: Edit, delete, and focus capabilities for replies
-   **Visual Indicators**: Connecting lines and avatar differentiation
-   **Independent Management**: Separate state management for replies

#### **Content Management**

-   **Inline Editing**: Live preview with form validation
-   **Soft Delete**: Safe deletion with restoration capabilities
-   **Whitespace Control**: Prevention of leading/trailing spaces
-   **Activity Logging**: Complete audit trail for all operations

### **Technical Implementation**

#### **Frontend Technologies**

-   **Livewire v3**: Real-time component interactions
-   **Alpine.js**: Client-side reactivity and state management
-   **Trix Editor**: Rich text editing capabilities
-   **Tailwind CSS**: Responsive design and styling

#### **Backend Architecture**

-   **Eloquent Models**: Comment, CommentReaction, CommentEmojiReaction
-   **API Endpoints**: RESTful routes for reactions and mentions
-   **Event System**: Real-time notifications and updates
-   **Activity Logging**: Spatie integration for audit trails

#### **Database Schema**

-   **Comments Table**: Core comment data with mentions array
-   **Reactions Tables**: Separate tables for different reaction types
-   **Soft Deletes**: Safe data retention with restoration
-   **Foreign Keys**: Proper relationships and cascading rules

---

## Testing

-   Feature tests exist for the comment system, mentions extraction, and notifications
-   Livewire interaction tests cover add/edit/delete flows and validation rules
-   Unit tests for core functionality
-   Run: `php artisan test` or `composer test`

**Current Test Coverage:**

-   **Advanced Comment System**:
    -   @mentions extraction and processing
    -   Comment reactions and emoji functionality
    -   Focus mode and threaded replies
    -   Rich text editor integration
    -   User notification system
-   **Task Management**: Kanban operations and workflow
-   **User Authentication**: Authorization and session management
-   **Livewire Components**: Real-time interactions and state management
-   **Database Operations**: Relationships and data integrity

**Future Testing Goals:**

-   Comprehensive CRUD tests for Projects, Documents, Important URLs
-   Policy-based authorization tests
-   API endpoint testing for MCP integration
-   Performance and load testing

---

## Deployment (Basic Outline)

1. **Server Requirements:**

    - Linux server with PHP 8.2+ and required extensions
    - Nginx/Apache web server
    - Database (MySQL/PostgreSQL recommended for production)
    - Redis (optional, for caching and queues)

2. **Application Setup:**

    ```bash
    git clone <repo-url>
    cd cheqqme-data-center
    composer install --no-dev --optimize-autoloader
    cp .env.example .env
    php artisan key:generate
    ```

3. **Database & Storage:**

    ```bash
    php artisan migrate --force
    php artisan storage:link
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

4. **Build Assets:**

    ```bash
    npm ci
    npm run build
    ```

5. **Permissions & Security:**

    ```bash
    chmod -R 755 storage bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache
    ```

6. **Production Services:**
    - Queue worker: `php artisan queue:work --daemon`
    - Scheduler: `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`
    - Process manager: Use Supervisor for queue workers
    - Cache: Configure Redis or file-based caching

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
| MCP Server Integration  | ✅     | Node.js API with Laravel compatibility |
| Playwright MCP Server   | ✅     | Browser automation & E2E testing       |
| Activity / Audit Log    | ✅     | Spatie + Filament plugin enabled       |
| Tagging system          | 🔜     | Polymorphic (tags)                     |
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

**What `composer dev` runs:**

-   Laravel development server (`php artisan serve`)
-   Queue listener (`php artisan queue:listen --tries=1`)
-   Log viewer (`php artisan pail --timeout=0`)
-   Vite dev server (`npm run dev`)

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
