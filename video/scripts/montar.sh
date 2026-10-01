#!/bin/bash
# Monta un video de DocFácil: tarjeta de inicio + grabación con voz + tarjeta de cierre
# (la última frase del guion se oye sobre el cierre). Sale vertical 1080x1350 para WhatsApp.
# Uso: montar.sh <carpetaDelVideo> <carpetaDeGrabacion> <salida.mp4>
set -euo pipefail
FF=/opt/homebrew/bin/ffmpeg; FP=/opt/homebrew/bin/ffprobe
DIR="$1"; REC="$2"; OUT="$3"; B="$REC/montaje"; mkdir -p "$B"
AUDIO="$DIR/audio"; WEBM=$(ls "$REC"/*.webm | head -1)
ULTIMA=$(tail -1 "$AUDIO/durations.txt" | cut -d' ' -f1)
# El cuerpo empieza 0.4 s antes de la primera frase, después de que cargó la página.
TRIM=$(python3 -c "import json;print(max(0,round(json.load(open('$REC/marks.json'))[0]['at']-0.4,3)))")
inputs=(); filt=""; i=0
while read -r key at; do
  ms=$(python3 -c "print(max(0,int(round(($at-$TRIM)*1000))))")
  inputs+=(-i "$AUDIO/$key.wav"); filt+="[$i:a]aresample=48000,aformat=channel_layouts=stereo,adelay=${ms}|${ms}[a$i];"; i=$((i+1))
done < <(python3 -c "import json;[print(m['key'],m['at']) for m in json.load(open('$REC/marks.json'))]")
mix=""; for ((k=0;k<i;k++)); do mix+="[a$k]"; done
$FF -y -loglevel error "${inputs[@]}" -filter_complex "${filt}${mix}amix=inputs=$i:normalize=0:duration=longest[out]" -map "[out]" -c:a pcm_s16le "$B/narracion.wav"
# Grabación 864x1080 → 1080x1350.
$FF -y -loglevel error -ss "$TRIM" -i "$WEBM" -i "$B/narracion.wav" -map 0:v -map 1:a \
  -vf "scale=1080:1350:flags=lanczos,setsar=1,fps=30" -c:v libx264 -preset slow -crf 21 -pix_fmt yuv420p \
  -c:a aac -b:a 160k -ar 48000 -ac 2 "$B/cuerpo.mp4"
tarjeta() { # imagen duración salida [audio]
  if [ -n "${4:-}" ]; then
    $FF -y -loglevel error -loop 1 -t "$2" -i "$1" -i "$4" -filter_complex "[1:a]aresample=48000,aformat=channel_layouts=stereo,adelay=300|300,apad[a]" -map 0:v -map "[a]" \
      -vf "scale=1080:1350,setsar=1,format=yuv420p,fps=30,fade=t=in:st=0:d=0.3" -c:v libx264 -preset slow -crf 21 -c:a aac -b:a 160k -ar 48000 -ac 2 -t "$2" "$3"
  else
    $FF -y -loglevel error -loop 1 -t "$2" -i "$1" -f lavfi -t "$2" -i anullsrc=r=48000:cl=stereo \
      -vf "scale=1080:1350,setsar=1,format=yuv420p,fps=30,fade=t=out:st=$(python3 -c "print($2-0.3)"):d=0.3" -c:v libx264 -preset slow -crf 21 -c:a aac -b:a 160k -ar 48000 -ac 2 -shortest "$3"
  fi
}
tarjeta "$DIR/inicio.png" 1.6 "$B/inicio.mp4"
DUR_ULTIMA=$(grep "^$ULTIMA " "$AUDIO/durations.txt" | cut -d' ' -f2)
tarjeta "$DIR/cierre.png" "$(python3 -c "print(round($DUR_ULTIMA+1.6,2))")" "$B/cierre.mp4" "$AUDIO/$ULTIMA.wav"
$FF -y -loglevel error -i "$B/inicio.mp4" -i "$B/cuerpo.mp4" -i "$B/cierre.mp4" \
  -filter_complex "[0:v][0:a][1:v][1:a][2:v][2:a]concat=n=3:v=1:a=1[v][a]" -map "[v]" -map "[a]" \
  -c:v libx264 -preset slow -crf 22 -pix_fmt yuv420p -r 30 -c:a aac -b:a 160k -ar 48000 -movflags +faststart "$OUT"
echo "$OUT $(du -h "$OUT" | cut -f1) $($FP -v error -show_entries format=duration -of csv=p=0 "$OUT")s"
