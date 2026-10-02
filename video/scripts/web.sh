#!/usr/bin/env bash
# Saca la versión para la página web de los 3 videos: 720x900, liviana y con
# +faststart (empieza a reproducir antes de bajarse completa), y su póster
# (la tarjeta de inicio, que ya trae el gancho escrito).
# Uso: video/scripts/web.sh   (después de montar.sh)
set -euo pipefail
cd "$(dirname "$0")/.."
destino=../public/videos
mkdir -p "$destino"
for par in "v1-consulta:docfacil-de-la-cita-a-la-receta" "v2-presupuesto:docfacil-odontograma-a-presupuesto" "v3-ortodoncia:docfacil-mensualidades-de-brackets"; do
  dir=${par%%:*}; mp4=${par#*:}
  ffmpeg -y -loglevel error -i "$dir/$mp4.mp4" -vf "scale=720:900:flags=lanczos" \
    -c:v libx264 -preset slow -crf 28 -pix_fmt yuv420p -c:a aac -b:a 96k -movflags +faststart "$destino/$dir.mp4"
  ffmpeg -y -loglevel error -i "$dir/inicio.png" -vf "scale=720:900:flags=lanczos" -q:v 5 "$destino/$dir-poster.jpg"
  echo "$destino/$dir.mp4 $(du -h "$destino/$dir.mp4" | cut -f1) · póster $(du -h "$destino/$dir-poster.jpg" | cut -f1)"
done
