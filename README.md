# Campus360 Enterprise ERP

A PHP/MySQL school and college ERP project with tenant-aware student, staff, academic, fee, leave, timetable and examination workflows.

## Requirements

- PHP 8.1+ (the project uses union types and other modern PHP features)
- MySQL 8+ or a compatible MariaDB version
- Apache with `mod_rewrite` enabled (for the included rewrite rules)
- PHP PDO MySQL extension

## Local setup (XAMPP)

1. Copy the project into your web root, for example `htdocs/collegeProject`.
2. Create a MySQL database named `college_erp_db` (or choose another name in your environment settings).
3. Copy `.env.example` to `.env` and set the database host, name, username and password for your local environment. Do not commit `.env` or production secrets.
4. Import `database/schema.sql`, then `database/seed.sql` into the database. Apply relevant files in `database/migrations/` in date order if the database predates those changes.
5. Open `http://localhost/collegeProject/public/` in your browser.

Use only credentials that you created or explicitly configured in your local database. Never deploy with shared/default passwords.

## Automated tests

Run from the repository root:

```bash
php run_tests.php
```

Run a single test group:

```bash
php run_tests.php --module=CSRF
php run_tests.php --module=AuthController
```

The existing test suite includes database-backed scenarios and historically contains Windows/XAMPP-specific subprocess commands. On non-Windows environments, those tests may require portability fixes and a configured local database. A passing unit suite is not a substitute for end-to-end verification in a configured environment.

## Security and deployment checklist

- Configure a unique database user with only the required privileges.
- Set `APP_ENV=production` and `APP_DEBUG=false` in production.
- Serve the application over HTTPS.
- Keep `.env`, database dumps, uploaded documents, and personal/student information out of public repositories.
- Back up the database before applying migrations.
- Verify tenant isolation and role-based access with separate accounts before production use.
