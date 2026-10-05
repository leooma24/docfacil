#!/usr/bin/env bash
# Deploy de DocFácil en producción. Se corre en el servidor:
#   ssh root@206.189.203.228 'cd /var/www/docfacil && git pull --ff-only && bash deploy.sh'
#
# No borra las vistas compiladas (nada de optimize:clear, view:clear ni
# view:cache, que también borra antes de compilar): el sitio sigue atendiendo
# mientras se sube, y la página que llegaba en ese instante tronaba con "File
# does not exist at path storage/framework/views" (1-oct-2026). Blade vuelve
# a compilar sola la vista cuyo archivo cambió. Ver DeploySinCarreraTest.
set -euo pipefail
cd /var/www/docfacil

git pull --ff-only
php artisan migrate --force

# Configuración, rutas y eventos no se guardan en caché en este servidor:
# limpiarlos solo asegura que nadie dejó uno viejo.
php artisan config:clear
php artisan route:clear
php artisan event:clear

# Filament guarda sus paneles en caché; esto lo sobrescribe sin borrarlo.
php artisan filament:cache-components

systemctl restart docfacil-queue

echo "DEPLOY_OK $(git log --oneline -1)"
