// Video 5: la pantalla de la sala de espera.
// Todo es lo que hay: la pantalla (/clinica/{slug}/sala, la liga sale de la
// página Check-in QR), el "Llegó" de recepción en las citas de hoy del
// escritorio y el "Iniciar consulta". Entre una pantalla y otra se ve la
// anterior congelada, para no enseñar cómo cargan.
// Antes de grabar, la base del demo necesita hoy tres citas por venir y nadie
// en consulta.
// Uso: node v5-sala.cjs <audioDir> <outDir>
const fs = require('fs');
const path = require('path');
const { chromium, BASE, duraciones, sesion, contextoGrabando, ayudantes, cerrar } = require('./comun.cjs');

const [audioDir, outDir] = process.argv.slice(2).map(p => path.resolve(p));
const RESPIRO = 700;

(async () => {
  fs.mkdirSync(outDir, { recursive: true });
  const durs = duraciones(audioDir);
  const browser = await chromium.launch();
  const estado = await sesion(browser);
  const ctx = await contextoGrabando(browser, outDir, estado, { width: 720, height: 900 });
  await ctx.addInitScript(() => addEventListener('DOMContentLoaded', () => {
    const st = document.createElement('style');
    st.textContent = '#__cap{top:16px !important;bottom:auto !important;font-size:24px !important;padding:12px 20px !important;max-width:680px !important;background:rgba(15,118,110,.96) !important}'
      + ' #__cortina{position:fixed;inset:0;z-index:99998;pointer-events:none}';
    document.head.appendChild(st);
    const foto = sessionStorage.getItem('__congelado');
    if (foto) {
      const c = document.createElement('div'); c.id = '__cortina';
      c.style.cssText = 'background:center/cover no-repeat url(' + foto + ')';
      document.body.appendChild(c);
    }
  }));
  const page = await ctx.newPage();
  const inicioVideo = Date.now();
  const errores = [];
  page.on('pageerror', e => errores.push(e.message));

  const marks = [];
  const { W, mover, clic, listo, seg, visible, acercar } = ayudantes(page, durs, inicioVideo, marks);
  const congelar = async () => {
    const foto = 'data:image/jpeg;base64,' + (await page.screenshot({ type: 'jpeg', quality: 88 })).toString('base64');
    await page.evaluate((f) => {
      sessionStorage.setItem('__congelado', f);
      let c = document.getElementById('__cortina');
      if (!c) { c = document.createElement('div'); c.id = '__cortina'; document.body.appendChild(c); }
      c.style.transition = 'none'; c.style.opacity = '1';
      c.style.background = `center/cover no-repeat url(${f})`;
    }, foto);
  };
  const descongelar = async () => {
    await page.evaluate(() => {
      sessionStorage.removeItem('__congelado');
      const c = document.getElementById('__cortina');
      if (c) { c.style.transition = 'opacity .35s'; c.style.opacity = '0'; setTimeout(() => c.remove(), 400); }
    });
    await W(400);
  };

  // La liga de la pantalla, de donde la saca el doctor: la página del QR.
  await page.goto(BASE + '/doctor/check-in-qr');
  await listo();
  const pantalla = await page.locator('a', { hasText: 'Abrir la pantalla' }).first().getAttribute('href');
  const fila = (nombre) => page.locator('.fi-wi-widget tr', { hasText: nombre }).first();
  const alEscritorio = async () => {
    await page.goto(BASE + '/doctor');
    await listo();
    const tabla = page.locator('.fi-wi-widget', { hasText: 'Citas de hoy' }).first();
    await tabla.scrollIntoViewIfNeeded();
    await visible(fila('Mónica'));
    await fila('Mónica').evaluate(e => e.scrollIntoView({ block: 'center' }));
    await W(300);
  };
  const aLaPantalla = async () => {
    await page.goto(pantalla);
    await listo();
    // En una tele la letra sale grande; en el video vertical se agranda el
    // contenido para que se vea como allá (el cursor no se escala).
    await page.addStyleTag({ content: 'body > header, body > main, body > footer { zoom: 1.5 }' });
    await page.evaluate(() => document.fonts.ready);
    await W(300);
  };

  await aLaPantalla();
  await page.mouse.move(360, 820);
  await W(800);

  await seg(1, '¿Le preguntan a cada rato cuánto les falta?', async () => {
    await W(RESPIRO);
  });

  await seg(2, 'En una tele de la sala: quién está en consulta y quién sigue', async () => {
    await mover(page.locator('h2', { hasText: 'En consulta' }), 20); await W(900);
    await mover(page.locator('h2', { hasText: 'Siguen' }), 20); await W(500);
    await mover(page.locator('.siguen .nombre').first(), 14);
    await W(RESPIRO);
    await congelar();
    await alEscritorio();
  });

  await seg(3, 'Recepción le da "Llegó"', async () => {
    await descongelar();
    await clic(fila('Mónica').getByRole('button', { name: 'Llegó' }), 18);
    await listo(); await W(900);
    await congelar();
    await aLaPantalla();
    await descongelar();
    await mover(page.locator('.llego').first(), 18);
    await W(RESPIRO);
    await congelar();
    await alEscritorio();
  });

  await seg(4, 'Usted le da "Iniciar consulta"', async () => {
    await descongelar();
    await clic(fila('Mónica').getByRole('link', { name: 'Iniciar consulta' }), 18);
    await listo(); await W(1200);
    await congelar();
    await aLaPantalla();
    await descongelar();
    await mover(page.locator('.en-consulta .nombre').first(), 18);
    await W(RESPIRO);
  });

  await seg(5, 'Solo el nombre y la inicial, nunca a qué viene', async () => {
    await mover(page.locator('.siguen .nombre').first(), 16); await W(500);
    await mover(page.locator('.en-consulta .nombre').first(), 16);
    await W(RESPIRO + 500);
  });

  const video = await cerrar(ctx, page, outDir, marks);
  await browser.close();
  console.log(video);
  if (errores.length) { console.error('Errores de JavaScript en la página:\n' + errores.join('\n')); process.exit(2); }
})().catch(e => { console.error(e); process.exit(1); });
