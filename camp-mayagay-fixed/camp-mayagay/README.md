# Camp Mayagay

Booking system for Camp Mayagay: HTML/CSS/JS front-end, PHP API, **one MySQL database**.
No XAMPP - everything runs in Docker.

## Run

```
cp .env.example .env        # edit passwords (and MAIL_* for email)
docker compose up -d --build
```

Open http://localhost:8080

- First start creates the `camp_mayagay` database from `database/schema.sql`
  and seeds the admin and staff accounts (`ADMIN_*` / `STAFF_*` in `.env`;
  defaults `admin` / `Admin@123`, `staff` / `Staff@123`). **Change them.**
- Guests register from the Register page.
- Optional Java reports service: `docker compose --profile java up -d`

## Run WITHOUT Docker (Windows, your own MySQL)

1. `winget install PHP.PHP.8.3`, then enable `pdo_mysql`, `mbstring`, `openssl` in php.ini.
2. In MySQL Workbench, run `database/setup-local.sql` once.
3. Double-click `setup-local.bat` (creates `.env.local` - set `DB_PASS` - then starts
   http://localhost:8080). Or in VS Code press F5 ("Camp Mayagay (PHP server + Chrome)").

Do not use Live Server / GitHub Pages: they cannot run PHP.

## Email / 2FA

Admin and staff log in with an emailed 6-digit code. Set `MAIL_USERNAME`
and `MAIL_PASSWORD` (Gmail App Password) in `.env`. If they are empty on
the first start, 2FA is switched off so you are not locked out; turn it on
in Security Management once email works. Then `docker compose up -d`.

## Database

Single schema file: `database/schema.sql` (it only runs on an empty data
volume). To reset everything: `docker compose down -v`.
Backup: `docker compose exec db sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" camp_mayagay' > backup.sql`

## Connect VS Code to MySQL

1. Install the **MySQL** extension by Weijan Chen (or "SQLTools" + "SQLTools MySQL/MariaDB").
2. Run `docker compose up -d`.
3. In the extension, add a connection:
   Host `127.0.0.1`, Port `3307` (`DB_HOST_PORT`), User `camp`,
   Password = `DB_PASS` from `.env`, Database `camp_mayagay`.
   (Use user `root` + `DB_ROOT_PASSWORD` for full admin access.)

## Layout

```
php/        API + auth (db.php reads DB_* env vars)
assets/     css, js, images, uploads (uploads live in a Docker volume)
database/   schema.sql
docker/     apache + php config, entrypoint
java/       optional reports service
```
