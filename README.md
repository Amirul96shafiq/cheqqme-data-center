# 📦 CheQQme Data Center

## 📝 Project Description
**CheQQme Data Center** is an internal web-based application built with Laravel and Filament.  
It serves as a centralized platform for managing and browsing important company resources such as SharePoint URLs, client project folders, and internal tools.

## 🎯 Project Objective
- Organize internal resources in a clean and structured way
- Allow CheQQme team members to quickly locate key project links and documents
- Optionally integrate AI assistant support to search or interact with saved data
- Serve as a farewell gift from me, to the team before transitioning to a new role, meanwhile self learning how to operate as a very very very junior front-end, back-end, or even full-stack developer.

## 🛠️ Tech Stack

<table>
  <thead>
    <tr><th>Layer</th><th>Tools/Frameworks</th></tr>
  </thead>
  <tbody>
    <tr><td>Backend</td><td>Laravel 12.x</td></tr>
    <tr><td>Admin Panel</td><td>FilamentPHP (v3)</td></tr>
    <tr><td>Frontend</td><td>Tailwind CSS (via Filament UI)</td></tr>
    <tr><td>Database</td><td>SQLite (for now, may switch later)</td></tr>
    <tr><td>Dev Tools</td><td>Git, GitHub, GitHub CLI, Composer, Artisan</td></tr>
  </tbody>
</table>

## 📁 Project Structure
- `/app` → Laravel backend logic
- `/resources/views` → Blade views (if any custom)
- `/app/Filament/Resources` → Filament Admin Resources
- `/database/seeders` → Dummy/test data
- `/routes/web.php` → Custom web routes (e.g., redirect `/`)

## 🔐 Authentication
Filament handles user authentication using Laravel’s built-in features.  
Only registered users can access the admin panel.

## 📌 To-Do (Planned Features)
- ✅ Link Management CRUD (title, URL, description)
- ✅ Admin-only access
- ✅ Created-by tracking
- ⬜ AI-powered search assistant
- ⬜ Tagging or categorization system
- ⬜ Advanced user role support

## 🤝 Contribution
Currently under personal development. No outside contributors yet.

## 📚 Useful Docs
- [Laravel Official Docs](https://laravel.com/docs/12.x)
- [Filament Official Docs](https://filamentphp.com/docs)

## ✍️ Author
Built with ❤️ by **Amirul** (Creative Designer (I think?) & Aspiring Dev)  
GitHub: [@Amirul96shafiq](https://github.com/Amirul96shafiq)

## 🧼 How to Use It (Coming Soon)
Full setup guide and deployment notes will be added.