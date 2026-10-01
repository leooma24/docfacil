// Video 3: planes de pago de ortodoncia, y la mensualidad se cobra en la consulta.
// Uso: node v3-ortodoncia.cjs <audioDir> <outDir>
const fs = require('fs');
const { chromium, BASE, duraciones, sesion, contextoGrabando, ayudantes, cerrar } = require('./comun.cjs');

const [audioDir, outDir] = process.argv.slice(2);
const VALENTINA = 15, PLAN_DIEGO = 1, CITA_DIEGO = 140; // datos de prueba

(async () => {
  fs.mkdirSync(outDir, { recursive: true });
  const durs = duraciones(audioDir);
  const browser = await chromium.launch();
  const estado = await sesion(browser);
  const ctx = await contextoGrabando(browser, outDir, estado);
  const page = await ctx.newPage();
  const inicioVideo = Date.now();
  const errores = [];
  page.on('pageerror', e => errores.push(e.message));

  await page.goto(BASE + '/doctor/planes-de-pago');
  await page.waitForLoadState('networkidle');
  await page.evaluate(() => document.fonts.ready);
  await page.mouse.move(430, 400);
  await page.waitForTimeout(600);

  const marks = [];
  const { W, cap, mover, clic, listo, seg, visible, acercar } = ayudantes(page, durs, inicioVideo, marks);
  const campo = (nombre) => page.locator(`input[id$=".${nombre}"]`).first();

  await seg(1, '¿Quién le debe la mensualidad?', async () => {
    await mover(page.locator('text=Planes de pago').first(), 25); await W(500);
    await page.evaluate(() => {
      const filas = [...document.querySelectorAll('tr')].filter(tr => tr.querySelector('.fi-badge'));
      filas[0].id = '__f1'; filas[filas.length - 1].id = '__f2';
    });
    await acercar(['#__f1', '#__f2'], 1.6); await W(900);
  });

  await seg(2, 'Al corriente o con pagos vencidos', async () => {
    await mover(page.locator('.fi-badge', { hasText: 'vencida' }).first(), 20); await W(700);
    await mover(page.locator('.fi-badge', { hasText: 'Al corriente' }).first(), 20);
  });

  await seg(3, null, async () => {
    await page.goto(BASE + '/doctor/planes-de-pago/create?patient=' + VALENTINA);
    await listo();
    await cap('Total, enganche y mensualidades');
    await page.locator('text=Cómo se paga').first().scrollIntoViewIfNeeded();
    await page.evaluate(() => scrollBy({ top: -90 }));
    // Los campos traen un valor de inicio (0, 20): se reemplaza, no se agrega.
    const poner = async (nombre, valor) => { await clic(campo(nombre), 12); await page.keyboard.press('Meta+A'); await page.keyboard.type(valor, { delay: 55 }); };
    await poner('total', '24000');
    await poner('down_payment', '4000');
    await poner('installments_count', '20');
    await page.keyboard.press('Tab');
    await page.locator('text=20 mensualidades de $1,000.00').first().waitFor({ timeout: 6000 });
    await mover(page.locator('text=20 mensualidades de $1,000.00').first(), 16);
  });

  await seg(4, 'Cada pago con su fecha', async () => {
    await clic(page.locator('button', { hasText: /^\s*Crear\s*$/ }));
    await page.waitForURL(/planes-de-pago\/\d+$/, { timeout: 10000 });
    await listo(); await cap('Cada pago con su fecha');
    await page.locator('text=Mensualidades').last().scrollIntoViewIfNeeded();
    await page.evaluate(() => scrollBy({ top: -60, behavior: 'smooth' })); await W(500);
    await mover(page.locator('text=Enganche').last(), 16);
  });

  await seg(5, null, async () => {
    await page.goto(BASE + '/doctor/consulta?appointment=' + CITA_DIEGO);
    await listo();
    await cap('Se cobra en la consulta, ahí mismo');
    for (let i = 0; i < 3; i++) { await clic(page.locator('button.nav-btn', { hasText: 'Siguiente' }), 10); await W(250); }
    const tarjeta = page.locator('text=Mensualidades por cobrar').first();
    await visible(tarjeta); await tarjeta.scrollIntoViewIfNeeded();
    await mover(page.locator('text=mensualidad 2 de 20').first(), 16); await W(400);
    await clic(page.locator('button', { hasText: 'Efectivo' }).first());
    await page.locator('text=Mensualidad cobrada').first().waitFor({ timeout: 6000 });
  });

  await seg(6, null, async () => {
    await page.goto(BASE + '/doctor/planes-de-pago/' + PLAN_DIEGO);
    await listo();
    await cap('Queda en su corte del día');
    await page.evaluate(() => {
      const fila = [...document.querySelectorAll('tr')].find(tr => tr.textContent.includes('01/09/2026'));
      fila.id = '__sep';
    });
    await page.locator('#__sep').scrollIntoViewIfNeeded();
    await acercar(['#__sep'], 1.5); await W(900);
    await mover(page.locator('#__sep .fi-badge').first(), 16);
  });

  const video = await cerrar(ctx, page, outDir, marks);
  await browser.close();
  console.log(video);
  if (errores.length) { console.error('Errores de JavaScript en la página:\n' + errores.join('\n')); process.exit(2); }
})().catch(e => { console.error(e); process.exit(1); });
