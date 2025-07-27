# 📦 CheQQme Data Center

## 📝 Overview
**CheQQme Data Center** is an internal web application built with Laravel and FilamentPHP.  
It acts as a centralized hub for managing and accessing important internal resources such as SharePoint URLs, client project folders, internal documents, and tools.

This project serves a dual purpose:
- To improve team productivity through organized access to company data
- As a farewell gift to the CheQQme team, while I upskill myself in full-stack development

## 🎯 Objectives
- Organize internal links, files, and tools in a structured interface
- Enable the CheQQme team to quickly find essential project data
- Lay the foundation for AI-powered search and assistant features
- Serve as a self-learning playground for Laravel, Filament, and web development in general

---

## 🛠️ Tech Stack

| Layer        | Tools / Frameworks               |
|--------------|----------------------------------|
| Backend      | Laravel 12.x                     |
| Admin Panel  | FilamentPHP v3                   |
| Frontend     | Tailwind CSS (via Filament UI)   |
| Database     | SQLite (temporary, may change)   |
| Dev Tools    | Git, GitHub, Composer, Artisan   |

---

## 📁 Folder Structure

- `app/` → Laravel backend logic
- `app/Filament/Resources/` → Filament admin resources
- `resources/views/` → Blade views (custom auth pages, if any)
- `routes/web.php` → Routes (e.g., `/` redirect, public pages)
- `database/seeders/` → Sample/test data seeders

---

## 🔐 Authentication
The app uses Laravel’s built-in authentication, managed via Filament's admin panel.  
Only authorized users can access the admin area.

---

## 🚧 Feature Roadmap

- ✅ Link management (CRUD: title, URL, description)
- ✅ Admin-only access
- ✅ "Created by" tracking per record
- ⬜ AI assistant for smart search & queries
- ⬜ Tagging / categorization system
- ⬜ Advanced user roles & permissions
- ⬜ Bulk import/export (CSV, XLSX)

---

## ✍️ Author

Crafted by:
- **Amirul** – Creative Designer & Aspiring Developer  
- With huge help from **ChatGPT** – my 24/7 coding buddy  
GitHub: [@Amirul96shafiq](https://github.com/Amirul96shafiq)

---

## 📚 References

- [Laravel Documentation](https://laravel.com/docs/12.x)
- [FilamentPHP Documentation](https://filamentphp.com/docs)

---

## 🧪 Setup Instructions (Coming Soon)
A full installation and deployment guide will be added soon.

---

## 🤝 Contributions
Currently a solo project. Contributions may be open in the future.