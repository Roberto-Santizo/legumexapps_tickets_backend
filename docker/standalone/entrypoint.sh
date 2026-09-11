#!/usr/bin/env bash
# Entrypoint de la imagen all-in-one: prepara el datadir de mariadb y los
# secretos persistentes, y luego cede el control a supervisor.
set -e

MYSQL_DATADIR="${MYSQL_DATADIR:-/var/lib/mysql/data}"
PERSIST_DIR="$(dirname "$MYSQL_DATADIR")"
SECRETS_FILE="$PERSIST_DIR/app-secrets.env"
SOCKET=/run/mysqld/mysqld.sock

# --- Directorios de runtime -------------------------------------------------
mkdir -p /run/mysqld /run/nginx /var/lib/nginx/tmp "$PERSIST_DIR" \
         /var/www/html/storage/app/public
chown -R mysql:mysql /run/mysqld "$PERSIST_DIR"
chown -R www-data:www-data /var/lib/nginx /var/www/html/storage /var/www/html/bootstrap/cache

# Los assets de la imagen mandan sobre lo que hubiera en public/ (montajes).
if [ -d /var/www/html/public-dist ]; then
    cp -a /var/www/html/public-dist/. /var/www/html/public/
fi

# Adjuntos de tickets: public/storage -> storage/app/public.
ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage

# --- Datadir de mariadb -----------------------------------------------------
if [ ! -d "$MYSQL_DATADIR/mysql" ]; then
    echo "Inicializando mariadb en $MYSQL_DATADIR..."
    mkdir -p "$MYSQL_DATADIR"
    chown mysql:mysql "$MYSQL_DATADIR"
    mariadb-install-db --user=mysql --datadir="$MYSQL_DATADIR" --skip-test-db >/dev/null
fi

# Servidor temporal (sólo socket unix) para crear usuario y base si hacen falta
# y dejar la contraseña alineada con DB_PASSWORD en cada arranque. root@localhost
# entra por unix_socket, asi que este paso nunca depende de la contraseña.
echo "Preparando usuario y base de datos..."
su-exec mysql mariadbd --datadir="$MYSQL_DATADIR" --skip-networking --socket="$SOCKET" &
tmp_pid=$!

retries=60
until mariadb-admin --socket="$SOCKET" ping >/dev/null 2>&1; do
    retries=$((retries - 1))
    if [ "$retries" -le 0 ]; then
        echo "mariadb temporal no arranco a tiempo." >&2
        exit 1
    fi
    sleep 1
done

db_user="${DB_USERNAME:-legumex}"
db_name="${DB_DATABASE:-legumexapps_tickets_db}"
db_pass="${DB_PASSWORD:-mysecretpassword}"

# La app conecta por TCP a 127.0.0.1, asi que el usuario se crea para ese host
# (con skip-name-resolve, 127.0.0.1 no casa con 'localhost').
mariadb --socket="$SOCKET" -uroot <<SQL
CREATE DATABASE IF NOT EXISTS \`${db_name}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${db_user}'@'127.0.0.1' IDENTIFIED BY '${db_pass}';
ALTER USER '${db_user}'@'127.0.0.1' IDENTIFIED BY '${db_pass}';
GRANT ALL PRIVILEGES ON *.* TO '${db_user}'@'127.0.0.1' WITH GRANT OPTION;
FLUSH PRIVILEGES;
SQL

mariadb-admin --socket="$SOCKET" -uroot shutdown
wait "$tmp_pid" || true

# --- Secretos persistentes --------------------------------------------------
# Se guardan junto a los datos: si el volumen sobrevive, las sesiones y los
# tokens siguen siendo validos tras reiniciar el contenedor.
# Precedencia: variables de entorno del usuario > fichero persistido > nuevas.
if [ -f "$SECRETS_FILE" ]; then
    stored_app_key="$(sed -n "s/^APP_KEY='\(.*\)'$/\1/p" "$SECRETS_FILE")"
    stored_jwt_secret="$(sed -n "s/^JWT_SECRET='\(.*\)'$/\1/p" "$SECRETS_FILE")"
    APP_KEY="${APP_KEY:-$stored_app_key}"
    JWT_SECRET="${JWT_SECRET:-$stored_jwt_secret}"
fi

if [ -z "$APP_KEY" ]; then
    APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    echo "APP_KEY generada y guardada en el volumen de datos."
fi

if [ -z "$JWT_SECRET" ]; then
    JWT_SECRET="$(php -r 'echo bin2hex(random_bytes(32));')"
    echo "JWT_SECRET generado y guardado en el volumen de datos."
fi

export APP_KEY JWT_SECRET MYSQL_DATADIR

umask 077
cat > "$SECRETS_FILE" <<SECRETS
APP_KEY='${APP_KEY}'
JWT_SECRET='${JWT_SECRET}'
SECRETS
chown mysql:mysql "$SECRETS_FILE"
umask 022

if [ -z "$MSGRAPH_TENANT_ID" ] || [ -z "$MSGRAPH_CLIENT_ID" ] || [ -z "$MSGRAPH_CLIENT_SECRET" ] || [ -z "$MSGRAPH_FROM" ]; then
    echo "ADVERTENCIA: faltan variables MSGRAPH_*. Crear y cerrar tickets fallara al enviar correo." >&2
fi

exec "$@"
