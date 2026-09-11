# Legumexapps Tickets — Backend

## Docker Compose (php-fpm + nginx + mysql + queue + scheduler)

```bash
cp .env.example .env   # rellenar APP_KEY, JWT_SECRET y MSGRAPH_*
docker compose up -d --build
```

API en `http://localhost:8000` (`APP_PORT`), MySQL 8.4 expuesto en `3307` (`DB_PORT_FORWARD`). `DB_USERNAME`/`DB_PASSWORD`/`DB_DATABASE` se leen del `.env`.

## Imagen all-in-one (sin compose, MariaDB embebida)

```bash
docker run -d --name legumex-tickets -p 8000:80 \
  -v legumex_tickets_dbdata:/var/lib/mysql \
  -v legumex_tickets_storage:/var/www/html/storage \
  -e MSGRAPH_TENANT_ID=... -e MSGRAPH_CLIENT_ID=... -e MSGRAPH_CLIENT_SECRET=... -e MSGRAPH_FROM=... \
  robertosantizo/legumexapps_tickets_backend:latest
```
