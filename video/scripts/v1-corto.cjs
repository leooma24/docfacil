// Video 1 corto: la receta sin papel, para mandar en frío por WhatsApp.
// Cuatro momentos y nada más, cada uno de cerca para que se lea en el celular:
// la receta, el cobro con el diente, la receta impresa y el odontograma.
// La navegación entre pantallas queda fuera de cuadro (antes de la primera
// marca se recorta) o detrás de una cortina.
// Uso: node v1-corto.cjs <audioDir> <outDir>
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');
const { chromium, BASE, duraciones, sesion, contextoGrabando, ayudantes, cerrar } = require('./comun.cjs');

const [audioDir, outDir] = process.argv.slice(2).map(p => path.resolve(p));
const PACIENTE = 3; // Ana Sofía Martínez Soto (datos de prueba)
const RESPIRO = 700; // pausa al final de cada momento: que se alcance a ver

(async () => {
  fs.mkdirSync(outDir, { recursive: true });
  const durs = duraciones(audioDir);
  const browser = await chromium.launch();
  const estado = await sesion(browser);
  const ctx = await contextoGrabando(browser, outDir, estado, { width: 680, height: 850 });
  // Letreros grandes y arriba: abajo los tapa la barra de WhatsApp.
  await ctx.addInitScript(() => addEventListener('DOMContentLoaded', () => {
    const st = document.createElement('style');
    st.textContent = '#__cap{top:16px !important;bottom:auto !important;font-size:23px !important;padding:12px 20px !important;max-width:640px !important;background:rgba(15,118,110,.96) !important}'
      + ' #__cortina{position:fixed;inset:0;z-index:99998;background:linear-gradient(#e2e8f0,#cbd5e1);opacity:0;transition:opacity .25s;pointer-events:none}';
    document.head.appendChild(st);
    // Mientras se cambia de pantalla se ve la anterior, congelada: una pausa
    // natural en vez de ver cómo carga. Sobrevive a la navegación.
    const foto = sessionStorage.getItem('__congelado');
    if (foto) {
      const c = document.createElement('div'); c.id = '__cortina';
      c.style.cssText = 'opacity:1;background:center/cover no-repeat url(' + foto + ')';
      document.body.appendChild(c);
    }
  }));
  const page = await ctx.newPage();
  const inicioVideo = Date.now();
  const errores = [];
  page.on('pageerror', e => errores.push(e.message));

  const marks = [];
  const { W, cap, mover, clic, escribir, listo, seg, visible, acercar } = ayudantes(page, durs, inicioVideo, marks);
  const siguiente = () => clic(page.locator('button.nav-btn', { hasText: 'Siguiente' }), 8);
  const sinAcercar = () => page.evaluate(() => { const m = document.querySelector('.fi-main') || document.body; m.style.transition = 'transform .5s ease'; m.style.transform = ''; });
  const ponerCortina = (fondo) => page.evaluate((fondo) => {
    let c = document.getElementById('__cortina');
    if (!c) { c = document.createElement('div'); c.id = '__cortina'; document.body.appendChild(c); }
    c.style.transition = 'none'; c.style.background = fondo; c.style.opacity = '1';
  }, fondo);
  const congelar = async () => {
    const foto = 'data:image/jpeg;base64,' + (await page.screenshot({ type: 'jpeg', quality: 88 })).toString('base64');
    await page.evaluate((f) => sessionStorage.setItem('__congelado', f), foto);
    await ponerCortina(`center/cover no-repeat url(${foto})`);
  };
  const descongelar = async () => {
    await page.evaluate(() => {
      sessionStorage.removeItem('__congelado');
      const c = document.getElementById('__cortina');
      if (c) { c.style.transition = 'opacity .35s'; c.style.opacity = '0'; setTimeout(() => c.remove(), 400); }
    });
    await W(400);
  };

  // Fuera de cuadro: de la cita a la pantalla de la receta.
  // La cita de resina de Ana Sofía (en la base de prueba, la 139; el script
  // de grabación la pasa a hoy antes de grabar).
  await page.goto(BASE + '/doctor/consulta?appointment=' + (process.env.CITA || 139));
  await listo(); await W(500);
  await siguiente(); await W(300);
  await escribir(page.locator('textarea[placeholder="¿Por qué viene el paciente?"]'), 'Dolor al masticar del lado izquierdo', 0);
  await escribir(page.locator('textarea[wire\\:model="diagnosis"]'), 'Caries oclusal y mesial en el 36', 0);
  await siguiente(); await W(400);
  await clic(page.locator('button', { hasText: 'Agregar medicamento' }), 4); await W(500);
  await page.locator('[wire\\:model\\.blur="medications.0.medication"]').first().evaluate(e => e.scrollIntoView({ block: 'start' }));
  await page.evaluate(() => scrollBy(0, -170));
  await page.mouse.move(300, 600);
  await W(1200);

  await seg(1, '¿Todavía hace la receta a mano?', async () => {
    await mover(page.locator('[wire\\:model\\.blur="medications.0.medication"]'), 20);
    await W(RESPIRO);
  });

  await seg(2, 'Receta con dosis, cada cuánto y días', async () => {
    await escribir(page.locator('[wire\\:model\\.blur="medications.0.medication"]'), 'Ibuprofeno', 45);
    await escribir(page.locator('[wire\\:model="medications.0.presentacion"]'), 'Tabletas 400 mg', 28);
    await escribir(page.locator('[wire\\:model="medications.0.dosage"]'), '1 tableta', 28);
    await escribir(page.locator('[wire\\:model="medications.0.frequency"]'), 'Cada 8 horas', 28);
    await escribir(page.locator('[wire\\:model="medications.0.duration"]'), '3 días', 28);
    await W(RESPIRO);
    // Detrás de la receta congelada: pasa al cobro.
    await congelar();
    await siguiente(); await W(300);
    const diente = page.locator('input[wire\\:model="procedures.0.tooth_number"]');
    await visible(diente);
    if ((await diente.inputValue()) !== '36') throw new Error('la consulta no trajo el 36: ' + await diente.inputValue());
    await diente.first().evaluate(e => e.scrollIntoView({ block: 'center' }));
    await W(200);
  });

  await seg(3, 'El cobro ya trae el diente 36', async () => {
    await descongelar();
    const diente = page.locator('input[wire\\:model="procedures.0.tooth_number"]');
    await mover(diente, 18); await W(500);
    await mover(page.locator('text=$600.00').first(), 16);
    await W(RESPIRO);
    // Detrás del cobro congelado: guarda la consulta, saca la receta y deja
    // listo el odontograma, para que lo que sigue se vea sin esperas.
    await congelar();
    await siguiente(); await W(300);
    await clic(page.locator('button', { hasText: 'Completar consulta' }), 4);
    await listo(); await W(300);
    const url = await page.locator('a', { hasText: 'Imprimir receta' }).first().getAttribute('href');
    const pdf = await page.request.get(url);
    const pdfPath = path.join(outDir, 'receta.pdf');
    fs.writeFileSync(pdfPath, await pdf.body());
    execFileSync('sips', ['-s', 'format', 'png', '-Z', '1600', pdfPath, '--out', path.join(outDir, 'receta.png')], { stdio: 'ignore' });
    execFileSync('/opt/homebrew/bin/ffmpeg', ['-y', '-loglevel', 'error', '-i', path.join(outDir, 'receta.png'), '-f', 'lavfi', '-i', 'color=white:s=4000x4000', '-filter_complex', '[1][0]scale2ref[f][r];[f][r]overlay=format=auto,crop=iw:ih*0.42:0:0', '-frames:v', '1', '-q:v', '3', path.join(outDir, 'receta-arriba.jpg')]);
    await page.goto(BASE + '/doctor/perfil-paciente?patient=' + PACIENTE);
    await listo();
    await clic(page.locator('button', { hasText: 'Odontograma' }), 4); await listo(); await W(300);
    const cambio = page.locator('text=36: Caries → Obturación');
    await visible(cambio);
    await page.evaluate(() => [...document.querySelectorAll('span')].find(e => e.textContent.trim().startsWith('36: Caries')).id = '__cambio');
    await page.locator('[data-cara="36-oclusal"]').first().evaluate(e => e.scrollIntoView({ block: 'center' }));
    await acercar(['#__cambio', '[data-cara="36-oclusal"]'], 1.6);
    await W(1100);
  });

  await seg(4, 'Con su cédula, lista para imprimir', async () => {
    // La receta se enseña encima del odontograma, que ya espera abajo.
    const receta = 'data:image/jpeg;base64,' + fs.readFileSync(path.join(outDir, 'receta-arriba.jpg')).toString('base64');
    await page.evaluate((src) => {
      sessionStorage.removeItem('__congelado');
      const c = document.getElementById('__cortina');
      c.style.transition = 'none';
      c.style.background = 'linear-gradient(#e2e8f0,#cbd5e1)';
      c.style.display = 'flex'; c.style.alignItems = 'center'; c.style.justifyContent = 'center';
      c.innerHTML = `<img src="${src}" style="width:96vw;box-shadow:0 18px 48px rgba(15,23,42,.28);border-radius:10px;margin-top:40px">`;
    }, receta);
    await page.mouse.move(600, 800, { steps: 10 });
    await W(RESPIRO + 400);
  });

  await seg(5, 'El odontograma se actualiza solo', async () => {
    await descongelar();
    await mover(page.locator('[data-cara="36-oclusal"]').first(), 20); await W(600);
    await mover(page.locator('#__cambio'), 16);
    await W(RESPIRO + 500);
  });

  const video = await cerrar(ctx, page, outDir, marks);
  await browser.close();
  console.log(video);
  if (errores.length) { console.error('Errores de JavaScript en la página:\n' + errores.join('\n')); process.exit(2); }
})().catch(e => { console.error(e); process.exit(1); });
