#!/usr/bin/env python3
"""Quita los destellos blancos de cambio de página: cada tramo de cuadros
completamente blancos se reemplaza por el último cuadro bueno, sin mover el
tiempo (la voz sigue cuadrada). Uso: sin-destellos.py entrada.mp4 salida.mp4"""
import re, subprocess, sys, shutil
FF = '/opt/homebrew/bin/ffmpeg'
src, out = sys.argv[1], sys.argv[2]
log = subprocess.run([FF, '-hide_banner', '-i', src, '-vf', 'signalstats,metadata=print:key=lavfi.signalstats.YMIN', '-an', '-f', 'null', '-'],
                     capture_output=True, text=True).stderr
ymins = [float(v) for v in re.findall(r'lavfi\.signalstats\.YMIN=(\d+(?:\.\d+)?)', log)]
# En video el blanco vale 235 (rango limitado); una página con texto baja de 100.
blancos = [i for i, y in enumerate(ymins) if y >= 220]
tramos, ini, prev = [], None, None
for i in blancos:
    if ini is None: ini = prev = i
    elif i == prev + 1: prev = i
    else: tramos.append((ini, prev)); ini = prev = i
if ini is not None: tramos.append((ini, prev))
tramos = [(a, b) for a, b in tramos if a > 0]
if not tramos:
    shutil.copy(src, out); print('sin destellos'); sys.exit(0)
# freezeframes toma el cuadro de reemplazo de una segunda entrada: se encadena
# un split + freezeframes por cada tramo.
partes, actual = [], '0:v'
for k, (a, b) in enumerate(tramos):
    partes.append(f'[{actual}]split[m{k}][r{k}];[m{k}][r{k}]freezeframes=first={a}:last={b}:replace={a-1}[v{k}]')
    actual = f'v{k}'
subprocess.run([FF, '-y', '-loglevel', 'error', '-i', src, '-filter_complex', ';'.join(partes), '-map', f'[{actual}]', '-map', '0:a',
                '-c:v', 'libx264', '-preset', 'slow', '-crf', '21', '-pix_fmt', 'yuv420p', '-c:a', 'copy', out], check=True)
print('destellos quitados:', ', '.join(f'{a}-{b}' for a, b in tramos))
