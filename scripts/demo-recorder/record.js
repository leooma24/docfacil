// DocFácil demo screencast recorder
// Navega el demo en prod, grabando un .webm de 1280x800.
// Cada paso tiene un comentario con el tiempo objetivo (acumulado en segundos)
// que coincide con scripts/demo-recorder/script.md
//
// Uso:
//   cd scripts/demo-recorder
//   npm install
//   npx playwright install chromium
//   npm run record
//
// Salida: ./output/demo-<timestamp>.webm

const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE = 'https://docfacil.tu-app.co';
const OUT_DIR = path.join(__dirname, 'output');
const VIEWPORT = { width: 1920, height: 1080 };

const sleep = (ms) => new Promise(r => setTimeout(r, ms));

async function step(label, seconds, fn) {
  console.log(`[${label}] ${seconds}s`);
  try { await fn(); } catch (e) { console.warn(`  warn: ${e.message}`); }
  await sleep(seconds * 1000);
}

(async () => {
  if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

  const browser = await chromium.launch({
    headless: false,
    args: ['--start-maximized', '--window-size=1920,1080'],
  });
  const context = await browser.newContext({
    viewport: VIEWPORT,
    recordVideo: { dir: OUT_DIR, size: VIEWPORT },
    locale: 'es-MX',
  });
  const page = await context.newPage();

  // 0:00 — Login con credenciales prellenadas (5s)
  await step('login-screen', 5, async () => {
    await page.goto(`${BASE}/demo`, { waitUntil: 'networkidle' });
  });

  // 0:05 — Click "Iniciar sesión" → dashboard (8s)
  await step('enter-dashboard', 8, async () => {
    // Filament login button: type=submit
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL(/\/doctor(\/?$|\/\?)/, { timeout: 10000 }).catch(() => {});
    await page.waitForLoadState('networkidle').catch(() => {});
  });

  // 0:13 — Agenda / citas (10s)
  await step('citas-list', 10, async () => {
    await page.goto(`${BASE}/doctor/appointments`, { waitUntil: 'networkidle' });
  });

  // 0:23 — Calendario visual (10s)
  await step('calendar', 10, async () => {
    await page.goto(`${BASE}/doctor/calendario`, { waitUntil: 'networkidle' }).catch(() => {});
  });

  // 0:33 — Lista de pacientes (8s)
  await step('patients-list', 8, async () => {
    await page.goto(`${BASE}/doctor/patients`, { waitUntil: 'networkidle' });
  });

  // 0:41 — Abrir primer paciente (12s)
  await step('patient-profile', 12, async () => {
    const firstRow = page.locator('table tbody tr').first();
    await firstRow.locator('a').first().click({ timeout: 5000 });
    await page.waitForLoadState('networkidle').catch(() => {});
  });

  // 0:53 — Recetas (10s)
  await step('prescriptions', 10, async () => {
    await page.goto(`${BASE}/doctor/prescriptions`, { waitUntil: 'networkidle' });
  });

  // 1:03 — Abrir una receta (10s)
  await step('prescription-detail', 10, async () => {
    const firstRow = page.locator('table tbody tr').first();
    await firstRow.locator('a').first().click({ timeout: 5000 });
    await page.waitForLoadState('networkidle').catch(() => {});
  });

  // 1:13 — Cobros / pagos (10s)
  await step('payments', 10, async () => {
    await page.goto(`${BASE}/doctor/payments`, { waitUntil: 'networkidle' });
  });

  // 1:23 — Expediente clínico (8s)
  await step('medical-records', 8, async () => {
    await page.goto(`${BASE}/doctor/medical-records`, { waitUntil: 'networkidle' });
  });

  // 1:31 — Odontograma (8s)
  await step('odontograms', 8, async () => {
    await page.goto(`${BASE}/doctor/odontograms`, { waitUntil: 'networkidle' });
  });

  // 1:39 — Cierre en dashboard con widgets/ingresos (10s)
  await step('dashboard-close', 10, async () => {
    await page.goto(`${BASE}/doctor`, { waitUntil: 'networkidle' });
  });

  await context.close();
  await browser.close();

  // Renombrar último video al nombre con timestamp
  const files = fs.readdirSync(OUT_DIR).filter(f => f.endsWith('.webm'));
  if (files.length) {
    const latest = files.sort().pop();
    const stamp = new Date().toISOString().replace(/[:.]/g, '-');
    const finalName = `demo-${stamp}.webm`;
    fs.renameSync(path.join(OUT_DIR, latest), path.join(OUT_DIR, finalName));
    console.log(`\n✓ Video listo: scripts/demo-recorder/output/${finalName}`);
  }
})();
