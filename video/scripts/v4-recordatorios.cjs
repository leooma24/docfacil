// Video 4: los recordatorios de mañana, sin escribirlos.
// Lo que se ve es lo que hay: la lista "Mañana, sin recordatorio", el botón
// de WhatsApp de cada cita (que abre el chat con el mensaje ya escrito y deja
// la cita recordada), la página donde el paciente confirma y la cita confirmada.
// Las citas de prueba de mañana se preparan en la base antes de grabar.
// Uso: node v4-recordatorios.cjs <audioDir> <outDir>
const fs = require('fs');
const path = require('path');
const { chromium, BASE, duraciones, sesion, contextoGrabando, ayudantes, cerrar } = require('./comun.cjs');

const [audioDir, outDir] = process.argv.slice(2).map(p => path.resolve(p));
const RESPIRO = 700;
// En el video la liga se ve como le llega al paciente, con el dominio real.
const DOMINIO = 'docfacil.tu-app.co';

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
    // La página del paciente (/c/…) está hecha para el celular: en el video
    // se agranda desde que carga, para que se vea como en su teléfono.
    if (location.pathname.startsWith('/c/')) {
      const contenido = [...document.body.children].find(e => !['__cur', '__cap', '__cortina'].includes(e.id) && e.tagName !== 'SCRIPT');
      if (contenido) { contenido.style.transformOrigin = 'top center'; contenido.style.transform = 'scale(1.45)'; }
    }
    const foto = sessionStorage.getItem('__congelado');
    if (foto) {
      const c = document.createElement('div'); c.id = '__cortina';
      c.style.cssText = 'background:center/cover no-repeat url(' + foto + ')';
      document.body.appendChild(c);
    }
  }));
  // WhatsApp no se abre de verdad: la ruta de DocFácil sí corre (deja la cita
  // recordada) y de su respuesta se toma la liga que arma; la pestaña nueva
  // se queda en blanco en vez de ir a wa.me.
  let ligaWhatsapp = '';
  await ctx.route('**/recordar', async (r) => {
    const resp = await r.fetch({ maxRedirects: 0 });
    ligaWhatsapp = resp.headers()['location'] || '';
    await r.fulfill({ status: 200, contentType: 'text/html', body: '<!doctype html><title>WhatsApp</title>' });
  });
  const page = await ctx.newPage();
  const inicioVideo = Date.now();
  const errores = [];
  page.on('pageerror', e => errores.push(e.message));

  const marks = [];
  let otraPestaña = null;
  const { W, mover, clic, listo, seg, visible } = ayudantes(page, durs, inicioVideo, marks);
  const congelar = async () => {
    const foto = 'data:image/jpeg;base64,' + (await page.screenshot({ type: 'jpeg', quality: 88 })).toString('base64');
    await page.evaluate((f) => {
      sessionStorage.setItem('__congelado', f);
      let c = document.getElementById('__cortina');
      if (!c) { c = document.createElement('div'); c.id = '__cortina'; document.body.appendChild(c); }
      c.style.transition = 'none'; c.style.opacity = '1'; c.innerHTML = '';
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
  const fila = (nombre) => page.locator('tr', { hasText: nombre }).first();

  await page.goto(BASE + '/doctor/citas?tableFilters[sin_recordatorio][isActive]=true');
  await listo();
  await page.evaluate(() => document.fonts.ready);
  await page.locator('table').first().evaluate(e => e.scrollIntoView({ block: 'start' }));
  await page.evaluate(() => scrollBy(0, -150));
  await page.mouse.move(360, 760);
  await W(1000);

  await seg(1, '¿Todavía escribe uno por uno los recordatorios?', async () => {
    await mover(fila('Miguel Ángel').locator('td').nth(2), 22);
    await W(RESPIRO);
  });

  await seg(2, 'Los de mañana que faltan, en una lista', async () => {
    for (const n of ['Mónica', 'Daniela', 'Carlos Eduardo']) { await mover(fila(n).locator('td').nth(2), 12); await W(250); }
    await mover(fila('Miguel Ángel').locator('td').nth(2), 12);
    await W(300);
  });

  await seg(3, 'Se abre su WhatsApp con el mensaje listo: usted da enviar', async () => {
    // El botón de WhatsApp está a la vista en cada cita: un clic.
    const pestaña = ctx.waitForEvent('page');
    await clic(fila('Miguel Ángel').locator('a[href*="/recordar"]'), 16);
    const otra = await pestaña;
    for (let i = 0; i < 50 && !ligaWhatsapp; i++) await W(100);
    // Su video no es el de la grabación: se borra al cerrarla.
    otraPestaña = otra;
    await otra.close().catch(() => {});
    await page.bringToFront();
    if (!ligaWhatsapp.includes('wa.me')) throw new Error('no salió la liga de WhatsApp: ' + ligaWhatsapp);
    const mensaje = decodeURIComponent(ligaWhatsapp.split('text=')[1].replace(/\+/g, ' '));
    fs.writeFileSync(path.join(outDir, 'mensaje.txt'), mensaje);
    // No sale solo: se abre el WhatsApp del doctor con el mensaje ya escrito
    // en la caja de texto, y él le da enviar. Eso es lo que se enseña.
    await page.evaluate(([texto, dominio]) => {
      const c = document.createElement('div'); c.id = '__cortina';
      c.style.cssText = 'opacity:0;transition:opacity .3s;background:rgba(15,23,42,.55);display:flex;align-items:center;justify-content:center;pointer-events:auto';
      const visto = texto.replace(/https?:\/\/[^/\s]+/, 'https://' + dominio)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/\n/g, '<br>');
      c.innerHTML = '<div style="width:640px;background:#efeae2;border-radius:22px;overflow:hidden;box-shadow:0 24px 60px rgba(0,0,0,.35);font-family:Inter,system-ui,sans-serif">'
        + '<div style="background:#f8fafc;padding:16px 22px;font:700 20px/1.3 Inter,system-ui;color:#334155;border-bottom:1px solid #e2e8f0">Su WhatsApp · Miguel Ángel Pérez</div>'
        + '<div id="__chat" style="min-height:150px;padding:18px 22px;display:flex;flex-direction:column;justify-content:flex-end"></div>'
        + '<div style="display:flex;gap:12px;align-items:flex-end;padding:14px 16px;background:#f0f2f5">'
        + '<div id="__caja" style="flex:1;background:#fff;border-radius:18px;padding:14px 16px;font:400 20px/1.45 Inter,system-ui;color:#111827">' + visto + '</div>'
        + '<div id="__enviar" style="flex:none;width:58px;height:58px;border-radius:50%;background:#0f8a4d;display:flex;align-items:center;justify-content:center;transition:transform .12s">'
        + '<svg viewBox="0 0 24 24" width="28" height="28" fill="#fff"><path d="M3 20.5 21 12 3 3.5l.01 6.6L15 12 3.01 13.9z"/></svg></div></div></div>';
      document.body.appendChild(c);
      const enviar = c.querySelector('#__enviar');
      enviar.addEventListener('mousedown', () => { enviar.style.transform = 'scale(.88)'; });
      enviar.addEventListener('mouseup', () => {
        enviar.style.transform = '';
        const caja = c.querySelector('#__caja');
        c.querySelector('#__chat').innerHTML = '<div style="margin-left:auto;max-width:520px;background:#d9fdd3;border-radius:16px 4px 16px 16px;padding:14px 16px;font:400 20px/1.45 Inter,system-ui;color:#111827;box-shadow:0 1px 1px rgba(0,0,0,.12)">' + caja.innerHTML + '</div>';
        caja.innerHTML = '<span style="color:#94a3b8">Escribe un mensaje</span>';
      });
      requestAnimationFrame(() => { c.style.opacity = '1'; });
    }, [mensaje, DOMINIO]);
    await W(900);
    await clic(page.locator('#__enviar'), 18);
    await W(RESPIRO + 500);
    // Detrás, ya congelado: abre la página que ve el paciente.
    await congelar();
    await page.goto(mensaje.match(/https?:\/\/\S+/)[0]);
    await listo();
    await W(300);
  });

  await seg(4, 'Su paciente confirma con un toque', async () => {
    await descongelar();
    const boton = page.locator('a,button', { hasText: 'Confirmar mi cita' });
    await mover(boton, 20); await W(400);
    await Promise.all([page.waitForNavigation(), clic(boton, 4)]);
    await listo();
    await W(RESPIRO + 300);
    await congelar();
    await page.goto(BASE + '/doctor/citas');
    await listo();
    await page.locator('table').first().evaluate(e => e.scrollIntoView({ block: 'start' }));
    await page.evaluate(() => scrollBy(0, -150));
    await W(300);
  });

  await seg(5, 'En su agenda ya aparece confirmada', async () => {
    await descongelar();
    const estado = fila('Miguel Ángel').getByText('Confirmada', { exact: true });
    if (!(await estado.count())) throw new Error('la cita no quedó confirmada');
    await mover(estado, 20);
    await W(RESPIRO + 700);
  });

  const video = await cerrar(ctx, page, outDir, marks);
  // montar.sh toma el único .webm de la carpeta: el de la pestaña de WhatsApp sobra.
  // (Si no alcanzó a grabar nada, no hay archivo que borrar.)
  if (otraPestaña) await otraPestaña.video().path().then(f => fs.rmSync(f, { force: true })).catch(() => {});
  await browser.close();
  console.log(video);
  if (errores.length) { console.error('Errores de JavaScript en la página:\n' + errores.join('\n')); process.exit(2); }
})().catch(e => { console.error(e); process.exit(1); });
