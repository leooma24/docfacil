#!/bin/bash
# Genera la voz de un guion con Kokoro y la deja lista para montar:
# sin silencios en las orillas, volumen parejo y durations.txt.
# Uso: voz.sh <carpetaDelVideo> [velocidad, 1.0 por omisión]
set -euo pipefail
DIR="$1"; A="$DIR/audio"; mkdir -p "$A"
(cd ~/AI/voz && ./kokoro-venv/bin/python kokoro_lote.py "$DIR/guion.txt" "$A" ef_dora "${2:-1.0}" >/dev/null)
: > "$A/durations.txt"
for f in "$A"/seg*_raw.wav; do
  k=$(basename "${f%_raw.wav}")
  /opt/homebrew/bin/ffmpeg -y -loglevel error -i "$f" -af "silenceremove=start_periods=1:start_threshold=-45dB,areverse,silenceremove=start_periods=1:start_threshold=-45dB,areverse,loudnorm=I=-16:TP=-1.5:LRA=11,apad=pad_dur=0.15" -ar 48000 "$A/$k.wav"
  echo "$k $(/opt/homebrew/bin/ffprobe -v error -show_entries format=duration -of csv=p=0 "$A/$k.wav")" >> "$A/durations.txt"
done
cat "$A/durations.txt"
