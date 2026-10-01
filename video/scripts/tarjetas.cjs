// Tarjetas de inicio y cierre de un video (1080x1350).
// Uso: node tarjetas.cjs <carpetaDelVideo> "<titulo del inicio>"
const path = require('path');
const { chromium } = require('/Users/omarlerma/Sites/agents/coder/ignis-phltesttpl/node_modules/playwright-core');
const [dir, titulo] = process.argv.slice(2);
const html = 'file://' + path.join(__dirname, '..', 'marca', 'tarjeta.html');
(async () => {
  const b = await chromium.launch();
  const p = await (await b.newContext({ viewport: { width: 1080, height: 1350 } })).newPage();
  const foto = async (q, archivo) => {
    await p.goto(html + '?' + new URLSearchParams(q));
    await p.waitForLoadState('networkidle'); await p.evaluate(() => document.fonts.ready); await p.waitForTimeout(200);
    await p.screenshot({ path: path.join(dir, archivo) });
  };
  await foto({ t: titulo }, 'inicio.png');
  await foto({ t: 'Pruébelo 15 días gratis', s: 'Sin tarjeta. Yo le ayudo a cargar su agenda.', b: 'docfacil.tu-app.co',
    f: '<b>Omar Lerma</b> · WhatsApp 668 249 3398' }, 'cierre.png');
  await b.close();
})();
