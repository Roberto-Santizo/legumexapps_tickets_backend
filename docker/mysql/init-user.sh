#!/bin/bash
# Corre una sola vez, al inicializar el volumen de datos (docker-entrypoint-initdb.d).
# La imagen oficial de MySQL no permite MYSQL_USER=root, y el .env del proyecto
# suele usar root: aqui se crea el usuario de la app sea cual sea, con acceso
# desde cualquier host de la red de compose.
set -e

db_user="${DB_USERNAME:-legumex}"
db_pass="${DB_PASSWORD:-mysecretpassword}"
db_name="${MYSQL_DATABASE}"

mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" <<SQL
CREATE USER IF NOT EXISTS '${db_user}'@'%' IDENTIFIED BY '${db_pass}';
ALTER USER '${db_user}'@'%' IDENTIFIED BY '${db_pass}';
GRANT ALL PRIVILEGES ON \`${db_name}\`.* TO '${db_user}'@'%';
FLUSH PRIVILEGES;
SQL
