// Video 2: el odontograma se vuelve presupuesto, se manda por WhatsApp y el paciente lo acepta.
// Uso: node v2-presupuesto.cjs <audioDir> <outDir>
const fs = require('fs');
const path = require('path');
const { chromium, BASE, duraciones, sesion, contextoGrabando, ayudantes, cerrar } = require('./comun.cjs');

const [audioDir, outDir] = process.argv.slice(2);
const ODONTOGRAMA = 4; // Carlos Eduardo Hernández (datos de prueba)

// Vista neutral del mensaje que se abriría en WhatsApp (no se sale a internet).
function vistaDelMensaje(texto) {
  const esc = (t) => t.replace(/&/g, '&amp;').replace(/</g, '&lt;');
  const html = esc(texto)
    .replace(/\*([^*]+)\*/g, '<b>$1</b>')
    .replace(/(https?:\/\/\S+)/g, (u) => '<span style="color:#0f766e;text-decoration:underline">' + (u.includes('aceptar') ? 'Aceptar el plan' : 'Ver el presupuesto') + '</span>')
    .replace(/\n/g, '<br>');
  return '<!doctype html><meta charset="utf-8"><body style="margin:0;height:100vh;background:linear-gradient(#f1f5f9,#e2e8f0);font-family:Inter,system-ui,sans-serif;display:flex;flex-direction:column;align-items:center;justify-content:center">'
    + '<div style="font-size:15px;font-weight:700;letter-spacing:.08em;color:#64748b;margin-bottom:18px">MENSAJE LISTO PARA ENVIAR</div>'
    + '<div style="width:640px;background:#fff;border-radius:22px;box-shadow:0 20px 50px rgba(15,23,42,.18);padding:30px 34px;font-size:24px;line-height:1.5;color:#0f172a">' + html + '</div>'
    + '<div style="margin-top:22px;font-size:17px;color:#64748b">Se abre en el WhatsApp de su consultorio, sin costo por mensaje</div></body>';
}

(async () => {
  fs.mkdirSync(outDir, { recursive: true });
  const durs = duraciones(audioDir);
  const browser = await chromium.launch();
  const estado = await sesion(browser);
  const ctx = await contextoGrabando(browser, outDir, estado);
  let mensaje = null;
  await ctx.route('https://wa.me/**', async (route) => {
    mensaje = new URL(route.request().url()).searchParams.get('text');
    await route.fulfill({ contentType: 'text/html; charset=utf-8', body: vistaDelMensaje(mensaje) });
  });
  const page = await ctx.newPage();
  const inicioVideo = Date.now();
  const errores = [];
  page.on('pageerror', e => errores.push(e.message));

  await page.goto(BASE + '/doctor/odontogramas/' + ODONTOGRAMA + '/edit');
  await page.waitForLoadState('networkidle');
  await page.evaluate(() => document.fonts.ready);
  // El resumen arriba, luego las herramientas y la boca completa.
  await page.evaluate(() => {
    const resumen = [...document.querySelectorAll('div')].find(e => e.textContent.trim().startsWith('Por tratar') && e.children.length >= 1);
    const caja = resumen.closest('div[style*="border-radius:16px"]') || resumen;
    scrollTo({ top: caja.getBoundingClientRect().top + scrollY - 70 });
  });
  await page.mouse.move(430, 500);
  await page.waitForTimeout(600);

  const marks = [];
  const { W, cap, mover, clic, bajar, listo, seg, visible, acercar } = ayudantes(page, durs, inicioVideo, marks);
  const herramienta = (nombre) => clic(page.locator('button', { hasText: new RegExp('^\\s*' + nombre + '\\s*$') }), 12);
  const cara = (c) => clic(page.locator(`[data-cara="${c}"]`), 12);

  await seg(1, 'Del odontograma al presupuesto', async () => {
    await mover(page.locator('text=ARCADA SUPERIOR').first(), 30); await W(700);
    await mover(page.locator('[data-cara="46-oclusal"]').first(), 30);
  });

  await seg(2, 'Caries por cara y extracciones', async () => {
    await herramienta('Caries');
    await cara('16-distal'); await W(150);
    await cara('16-oclusal'); await W(150);
    await cara('46-oclusal'); await W(150);
    await herramienta('Extracción');
    await cara('38-oclusal'); await W(200);
  });

  await seg(3, 'Lo que falta tratar, a la vista', async () => {
    await mover(page.locator('text=Por tratar').first(), 16); await W(300);
    await mover(page.locator('text=extracción indicada').first(), 12);
  });

  await seg(4, 'El presupuesto, con sus precios', async () => {
    await page.evaluate(() => scrollTo({ top: 0, behavior: 'smooth' })); await W(400);
    await clic(page.locator('button', { hasText: 'Armar presupuesto' }));
    await page.waitForURL(/presupuestos\/\d+\/edit/, { timeout: 10000 });
    await listo(); await W(400);
    await cap('El presupuesto, con sus precios');
    const lineas = page.locator('text=Servicios y costos').first();
    await visible(lineas);
    await page.evaluate(() => [...document.querySelectorAll('h3,span,div')].find(e => e.textContent.trim() === 'Servicios y costos')?.scrollIntoView({ block: 'start', behavior: 'smooth' }));
    await W(700);
    await mover(page.locator('.choices__list--single', { hasText: 'Extracción de tercer molar' }).first(), 16); await W(300);
    await page.evaluate(() => scrollBy({ top: 420, behavior: 'smooth' })); await W(600);
    await mover(page.locator('text=$3,700.00').first(), 16);
  });

  await seg(5, 'Mensaje listo, con liga para aceptar', async () => {
    await page.evaluate(() => scrollTo({ top: 0, behavior: 'smooth' })); await W(300);
    await clic(page.locator('button', { hasText: 'Enviar por WhatsApp' }));
    await page.waitForURL(/wa\.me/, { timeout: 10000 });
    await cap('Mensaje listo, con liga para aceptar');
    await page.mouse.move(430, 560, { steps: 20 });
  });

  await seg(6, 'El paciente acepta desde su celular', async () => {
    const ver = mensaje.match(/https?:\/\/\S+\/p\/[a-f0-9]{64}(?=\s|$)/)[0];
    await page.goto(ver);
    await listo();
    await cap('El paciente acepta desde su celular');
    const aceptar = page.locator('a', { hasText: 'Aceptar plan' });
    await bajar(500, 500);
    await clic(aceptar, 16);
    await page.waitForURL(/\/aceptar/, { timeout: 10000 });
    await listo(); await cap('El paciente acepta desde su celular');
  });

  await seg(7, null, async () => {
    await page.goto(BASE + '/doctor/presupuestos');
    await listo();
    await cap('Aceptado, sin perseguir a nadie');
    const aceptado = page.locator('.fi-badge', { hasText: 'Aceptado' }).first();
    await visible(aceptado);
    await page.evaluate(() => {
      const b = [...document.querySelectorAll('.fi-badge')].find(e => e.textContent.includes('Aceptado'));
      const celdas = [...b.closest('tr').querySelectorAll('td')];
      b.closest('td').id = '__estado';
      celdas[celdas.indexOf(b.closest('td')) - 1].id = '__total';
      // El renglón desde el nombre del paciente, para que se lea completo.
      const paciente = celdas.find(td => td.textContent.includes('Carlos'));
      if (paciente) paciente.id = '__paciente';
    });
    // El aviso de "listo para enviar" es de antes; aquí ya se aceptó.
    await page.addStyleTag({ content: '.fi-no-notification{display:none !important}' });
    await acercar(['#__paciente', '#__estado'], 1.8); await W(1100);
    await mover(aceptado, 20);
  });

  const video = await cerrar(ctx, page, outDir, marks);
  await browser.close();
  console.log(video);
  if (errores.length) { console.error('Errores de JavaScript en la página:\n' + errores.join('\n')); process.exit(2); }
})().catch(e => { console.error(e); process.exit(1); });
