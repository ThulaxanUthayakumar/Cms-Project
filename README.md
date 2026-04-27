# Prism CMS

A clean, role-based PHP Admin Dashboard & Content Management System powered by MySQL.

---

## Features

- **Role-based access** — Admin, Editor, Viewer
- **Posts management** — Create, edit, delete, filter, paginate
- **Categories** — Full CRUD
- **Users management** — Admin only
- **Site Settings** — Stored in DB
- **Activity Log** — Tracks logins and content changes
- **CSRF protection** on all forms
- **Flash messages** for user feedback
- **Responsive sidebar layout**

---

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache or Nginx with mod_rewrite (or PHP built-in server)

---

## Setup

### 1. Import the database

```bash
mysql -u root -p < schema.sql
```

This creates the `cms_db` database, tables, and seeds default data.

### 2. Configure the database

Edit `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cms_db');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

Also update `APP_URL` to match your server path:

```php
define('APP_URL', 'http://localhost/cms');
```

### 3. Serve the project

**Option A — Apache**
Place the project in your `htdocs` or `www` folder and visit `http://localhost/cms`.

**Option B — PHP built-in server**
```bash
cd cms
php -S localhost:8000
```
Then visit `http://localhost:8000`.

---

## Default Login

| Field    | Value         |
|----------|---------------|
| Username | `admin`       |
| Password | `admin123`    |

> ⚠️ Change the admin password immediately after first login via the **Users** page.

---

## Project Structure

```
cms/
├── index.php              # Login page
├── schema.sql             # Database schema + seed data
├── includes/
│   ├── config.php         # DB config, helpers, auth
│   ├── header.php         # Sidebar + topbar layout
│   └── footer.php         # Closing tags
└── public/
    ├── dashboard.php      # Stats + recent posts
    ├── posts.php          # Posts list + search
    ├── post_edit.php      # Create/edit post
    ├── categories.php     # Categories CRUD
    ├── users.php          # Users CRUD (admin)
    ├── settings.php       # Site settings (admin)
    └── logout.php         # Logout
```

---

## Roles

| Role   | Dashboard | Posts (own) | Posts (all) | Users | Settings |
|--------|-----------|-------------|-------------|-------|----------|
| Admin  | ✅        | ✅          | ✅          | ✅    | ✅       |
| Editor | ✅        | ✅          | ❌          | ❌    | ❌       |
| Viewer | ✅        | ❌          | ❌          | ❌    | ❌       |

---

## Extending

- Add **media uploads** — create `public/media.php` with `move_uploaded_file()`
- Add **rich text editor** — drop in TinyMCE or Quill on `post_edit.php`
- Add **API endpoints** — create `api/` folder returning JSON
- Add **tags** — new `tags` and `post_tags` tables with many-to-many join
