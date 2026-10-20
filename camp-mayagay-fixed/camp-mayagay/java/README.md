# Camp Mayagay - Reports module (Java, optional)

A small dependency-free Java service for the admin **Reports** page. It does
not touch MySQL directly: it forwards the admin's PHP session cookie to
`php/api.php` and aggregates the result (totals, revenue, per-cabin and
per-status breakdown, CSV export). The dashboard works without it.

## Run with Docker (recommended)

From the project root:

```
docker compose --profile java up -d --build
```

It listens on http://localhost:8081 and talks to the PHP container.

## Run locally (JDK 11+)

```
cd java
javac -d out src/ReportsServer.java
PHP_BASE_URL=http://localhost:8080 java -cp out ReportsServer
```

| Variable | Default | Meaning |
|---|---|---|
| `PHP_BASE_URL` | `http://localhost:8080` | Where the PHP app is served |
| `JAVA_PORT` | `8081` | Port of this service |

## Endpoints

| URL | Description |
|---|---|
| `GET /reports/summary` | JSON report |
| `GET /reports/export.csv` | CSV download |
| `GET /reports/health` | Health check |

Log in as admin in the same browser first - the service reuses that session.
