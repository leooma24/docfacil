// Arma public/css/panel-doctor.css desde resources/css/panel-doctor.css:
// solo las clases de Tailwind que usan las pantallas del panel del doctor.
// Uso: npm run css:panel
import fs from 'node:fs';
import path from 'node:path';
import { compile, optimize } from '@tailwindcss/node';
import { Scanner } from '@tailwindcss/oxide';

const entrada = path.resolve('resources/css/panel-doctor.css');
const salida = path.resolve('public/css/panel-doctor.css');

const compilador = await compile(fs.readFileSync(entrada, 'utf8'), {
    base: path.dirname(entrada),
    onDependency: () => {},
});

// Las clases que ya trae el CSS de Filament.
const deFilament = new Set();
for (const archivo of ['public/css/filament/filament/app.css', 'public/css/filament/forms/forms.css', 'public/css/filament/support/support.css']) {
    const texto = fs.readFileSync(path.resolve(archivo), 'utf8');
    for (const [, clase] of texto.matchAll(/\.((?:\\.|[A-Za-z0-9_-])+)/g)) {
        deFilament.add(clase.replace(/\\(.)/g, '$1'));
    }
}

// Solo las carpetas de @source: nada de buscar en todo el proyecto. Las
// clases sencillas que Filament ya trae se quedan con la de Filament; las
// variantes (con ":") se vuelven a escribir para que vayan después de las bases.
const clases = new Scanner({ sources: compilador.sources }).scan()
    .filter((clase) => clase.includes(':') || !deFilament.has(clase));
// Los colores de Tailwind v4 vienen en oklch(), que no entienden los iPhone
// con iOS anterior a 15.4: ahí los botones volverían a salir sin fondo. Se
// pasan a hex, que entiende cualquier navegador.
const css = optimize(compilador.build(clases), { minify: true }).code
    .replace(/oklch\(([\d.]+)%?\s+([\d.]+)\s+([\d.]+)(?:\s*\/\s*([\d.]+%?))?\)/g, (todo, l, c, h, a) => aHex(parseFloat(l) / (l.includes('.') && parseFloat(l) <= 1 && !todo.includes('%') ? 1 : 100), parseFloat(c), parseFloat(h), a));

function aHex(L, C, H, alfa) {
    const h = (H * Math.PI) / 180;
    const a = C * Math.cos(h), b = C * Math.sin(h);
    const l_ = L + 0.3963377774 * a + 0.2158037573 * b;
    const m_ = L - 0.1055613458 * a - 0.0638541728 * b;
    const s_ = L - 0.0894841775 * a - 1.291485548 * b;
    const [l3, m3, s3] = [l_ ** 3, m_ ** 3, s_ ** 3];
    const lineal = [
        4.0767416621 * l3 - 3.3077115913 * m3 + 0.2309699292 * s3,
        -1.2684380046 * l3 + 2.6097574011 * m3 - 0.3413193965 * s3,
        -0.0041960863 * l3 - 0.7034186147 * m3 + 1.707614701 * s3,
    ];
    const canal = (v) => {
        const g = v <= 0.0031308 ? 12.92 * v : 1.055 * Math.pow(v, 1 / 2.4) - 0.055;
        return Math.round(Math.min(1, Math.max(0, g)) * 255).toString(16).padStart(2, '0');
    };
    let hex = '#' + lineal.map(canal).join('');
    if (alfa !== undefined) {
        const x = alfa.endsWith('%') ? parseFloat(alfa) / 100 : parseFloat(alfa);
        hex += Math.round(x * 255).toString(16).padStart(2, '0');
    }
    return hex;
}

fs.mkdirSync(path.dirname(salida), { recursive: true });
fs.writeFileSync(salida, '/* Generado por scripts/css-del-panel.mjs (npm run css:panel). No se edita a mano. */\n' + css);
console.log(`${path.relative(process.cwd(), salida)}: ${(css.length / 1024).toFixed(1)} KB, ${clases.length} candidatos`);
