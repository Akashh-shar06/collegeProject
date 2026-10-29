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

## Deploy on Render (Docker + external MySQL)

Render's managed database product is PostgreSQL, while this project currently connects to MySQL using PDO MySQL. PostgreSQL is not a drop-in replacement. Keep MySQL and use a MySQL-compatible provider that allows connections from your Render web service.

### 1. Create the hosted database

1. Choose a MySQL-compatible hosting provider and create a database plus a database user.
2. From the provider's dashboard, copy the connection details: host, port (usually `3306`), database name, username and password.
3. Make sure the provider allows connections from Render. Use IP allowlisting or other network/security settings required by that provider; do not expose the database publicly without access controls.
4. Keep the database credentials private. Do not put them in source code or commit a real `.env` file.

### 2. Import the project tables

The SQL files are in the repository's `database/` folder.

- Import `database/schema.sql` first. It creates the tables and currently selects a database named `college_erp_db`. If your provider assigned a different database name or does not allow `CREATE DATABASE` / `USE` commands, create/select the database in the provider's dashboard and remove or adjust those two statements in a private copy of the SQL before importing it.
- Import `database/seed.sql` only if you want the example/demo records. It also selects `college_erp_db`, so apply the same database-name adjustment if needed.
- Apply any required SQL files in `database/migrations/` in date order if your schema version requires them. Do not blindly rerun migrations on an already initialized database.
- The seed file contains demo users and a shared sample password (`Admin@123`). Do not use these demo accounts on a public production site. For a real deployment, use a clean database and create secure, unique administrator credentials; change/remove any sample accounts before allowing public access.

You can import SQL using your database provider's web tool (if offered), such as phpMyAdmin, or a MySQL client connected to the hosted database. Importing means running the SQL so MySQL creates the tables and, optionally, inserts sample rows.

### 3. Configure Render environment variables

Create a Render **Web Service**, connect this repository, choose branch `main`, and select **Docker** as the runtime. Then add these environment variables in the Render service's Environment settings:

| Key | Value |
| --- | --- |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | Your actual Render service URL, including `https://` |
| `DB_HOST` | Host from your MySQL provider |
| `DB_PORT` | Port from your MySQL provider (usually `3306`) |
| `DB_NAME` | Database name from your provider |
| `DB_USER` | Database username from your provider |
| `DB_PASS` | Database password from your provider |
| `SESSION_LIFETIME` | `7200` |

Use the exact variable names above; the application reads these names in `config/config.php`. Do not share screenshots that reveal database passwords.

### 4. Deploy and test

1. Save the environment variables and deploy.
2. Open the Render URL and check that the login page loads.
3. Test login, logout, database-backed pages, and the key workflows with non-demo accounts.
4. Check Render logs if the app cannot connect to MySQL. Confirm the host, port, database name, credentials, network allowlist and imported schema.
5. Confirm tenant isolation and role permissions before using real student or staff information.

**Important:** Adding a Dockerfile and deploying the container does not automatically create or import the database. Uploaded files stored inside a container may also be lost when the service is redeployed; configure persistent/object storage if the application needs durable uploads.

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
- Do not use the shared sample password from `database/seed.sql` in production.
- Verify tenant isolation and role-based access with separate accounts before production use.
