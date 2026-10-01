// Video 1: de la cita a la receta en un minuto, y el odontograma se actualiza solo.
// Uso: node v1-consulta.cjs <audioDir> <outDir>
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');
const { chromium, BASE, duraciones, sesion, contextoGrabando, ayudantes, cerrar } = require('./comun.cjs');

const [audioDir, outDir] = process.argv.slice(2);
const PACIENTE = 3; // Ana Sofía Martínez Soto (datos de prueba)

(async () => {
  fs.mkdirSync(outDir, { recursive: true });
  const durs = duraciones(audioDir);
  const browser = await chromium.launch();
  const estado = await sesion(browser);
  const ctx = await contextoGrabando(browser, outDir, estado);
  const page = await ctx.newPage();
  // Las marcas van desde que empieza el video, no desde que cargó la página.
  const inicioVideo = Date.now();
  const errores = [];
  page.on('pageerror', e => errores.push(e.message));

  await page.goto(BASE + '/doctor');
  await page.waitForLoadState('networkidle');
  await page.evaluate(() => document.fonts.ready);
  await page.mouse.move(430, 300);
  await page.waitForTimeout(600);

  const t0 = inicioVideo;
  const marks = [];
  const { W, cap, mover, clic, escribir, bajar, listo, seg, visible } = ayudantes(page, durs, t0, marks);
  const siguiente = () => clic(page.locator('button.nav-btn', { hasText: 'Siguiente' }));

  await seg(1, 'Una consulta completa, sin papeles', async () => {
    await mover(page.locator('text=Buenas').first(), 30); await W(900);
    await bajar(330, 700);
    await mover(page.locator('text=Siguiente paciente').first(), 25); await W(600);
  });

  await seg(2, 'La cita de hoy: resina', async () => {
    await mover(page.locator('text=Resina (obturación)').first(), 18); await W(500);
    await clic(page.locator('a,button', { hasText: 'Iniciar consulta' }));
    await listo(); await W(700);
    await siguiente(); await W(400);
  });

  await seg(3, 'Motivo y diagnóstico', async () => {
    await escribir(page.locator('textarea[placeholder="¿Por qué viene el paciente?"]'), 'Dolor al masticar del lado izquierdo', 22);
    await escribir(page.locator('textarea[wire\\:model="diagnosis"]'), 'Caries oclusal y mesial en el 36', 22);
    await W(300);
    await siguiente(); await W(400);
  });

  await seg(4, 'Receta con dosis, frecuencia y días', async () => {
    await clic(page.locator('button', { hasText: 'Agregar medicamento' })); await W(500);
    await escribir(page.locator('[wire\\:model\\.blur="medications.0.medication"]'), 'Ibuprofeno', 30);
    await escribir(page.locator('[wire\\:model="medications.0.presentacion"]'), 'Tabletas 400 mg', 22);
    await escribir(page.locator('[wire\\:model="medications.0.dosage"]'), '1 tableta', 22);
    await escribir(page.locator('[wire\\:model="medications.0.frequency"]'), 'Cada 8 horas', 22);
    await escribir(page.locator('[wire\\:model="medications.0.duration"]'), '3 días', 22);
    await W(300);
    await siguiente(); await W(500);
  });

  await seg(5, 'El diente 36 ya viene del odontograma', async () => {
    const diente = page.locator('input[wire\\:model="procedures.0.tooth_number"]');
    await visible(diente);
    if ((await diente.inputValue()) !== '36') throw new Error('la consulta no trajo el 36: ' + await diente.inputValue());
    await mover(page.locator('text=Del odontograma').first(), 20); await W(700);
    await mover(diente, 18); await W(900);
    await mover(page.locator('text=$600.00').first(), 16); await W(500);
  });

  await seg(6, 'Receta con su cédula, lista para imprimir', async () => {
    await siguiente(); await W(500);
    await clic(page.locator('button', { hasText: 'Completar consulta' }));
    await listo(); await W(500);
    const liga = page.locator('a', { hasText: 'Imprimir receta' });
    await mover(liga, 16); await W(400);
    // El PDF no se ve en Chromium sin ventana: se convierte a imagen y se muestra.
    const url = await liga.first().getAttribute('href');
    const pdf = await page.request.get(url);
    const pdfPath = path.join(outDir, 'receta.pdf');
    fs.writeFileSync(pdfPath, await pdf.body());
    execFileSync('sips', ['-s', 'format', 'png', '-Z', '1600', pdfPath, '--out', path.join(outDir, 'receta.png')], { stdio: 'ignore' });
    // Lo que importa de la receta está arriba: se muestra esa parte, grande.
    execFileSync('/opt/homebrew/bin/ffmpeg', ['-y', '-loglevel', 'error', '-i', path.join(outDir, 'receta.png'), '-vf', 'crop=iw:ih*0.42:0:0', path.join(outDir, 'receta-arriba.png')]);
    fs.writeFileSync(path.join(outDir, 'receta.html'),
      '<!doctype html><meta charset="utf-8"><body style="margin:0;background:linear-gradient(#e2e8f0,#cbd5e1);display:flex;justify-content:center;align-items:center;height:100vh;overflow:hidden">'
      + '<img src="receta-arriba.png" style="width:820px;box-shadow:0 18px 48px rgba(15,23,42,.28);border-radius:10px;margin-bottom:120px"></body>');
    await page.goto('file://' + path.join(outDir, 'receta.html'));
    await cap('Receta con su cédula, lista para imprimir');
    await W(300);
  });

  // El letrero sale ya en el perfil, no encima de la receta.
  await seg(7, null, async () => {
    await page.goto(BASE + '/doctor/perfil-paciente?patient=' + PACIENTE);
    await listo();
    await cap('El odontograma se actualizó solo');
    await clic(page.locator('button', { hasText: 'Odontograma' })); await listo(); await W(400);
    const cambio = page.locator('text=36: Caries → Obturación');
    await visible(cambio);
    await cambio.first().scrollIntoViewIfNeeded();
    await bajar(260, 400);
    // Acercamiento de cámara al 36 y al aviso del cambio.
    await page.evaluate(() => {
      // Acercamiento que deja dentro, de orilla a orilla, el aviso del cambio
      // y el diente 36 (con el ancho de la pantalla manda la escala).
      const d = document.querySelector('[data-cara="36-oclusal"]').closest('svg').getBoundingClientRect();
      const c = [...document.querySelectorAll('span')].find(e => e.textContent.trim().startsWith('36: Caries')).getBoundingClientRect();
      const m = document.querySelector('.fi-main') || document.body;
      const r = m.getBoundingClientRect();
      const L = Math.min(c.left, d.left) - 14, R = Math.max(c.right, d.right) + 14;
      const T = Math.min(c.top, d.top) - 60, B = Math.max(c.bottom, d.bottom) + 60;
      const s = Math.min(1.9, (innerWidth - 20) / (R - L), (innerHeight - 160) / (B - T));
      const x0 = (s * L - 10) / (s - 1), y0 = (s * T - 90) / (s - 1);
      m.style.transition = 'transform 1s ease';
      m.style.transformOrigin = `${x0 - r.left}px ${y0 - r.top}px`;
      m.style.transform = `scale(${s})`;
    });
    await W(1100);
    await mover(page.locator('[data-cara="36-oclusal"]').first(), 20); await W(800);
    await mover(cambio, 16); await W(500);
  });

  const video = await cerrar(ctx, page, outDir, marks);
  await browser.close();
  console.log(video);
  if (errores.length) { console.error('Errores de JavaScript en la página:\n' + errores.join('\n')); process.exit(2); }
})().catch(e => { console.error(e); process.exit(1); });
