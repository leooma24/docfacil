// La imagen de la promoción de fundador (1080x1350) para adjuntar en WhatsApp.
// Uso: node video/scripts/promo-fundador.cjs
const path = require('path');
const { chromium } = require('/Users/omarlerma/Sites/agents/coder/ignis-phltesttpl/node_modules/playwright-core');
(async () => {
  const b = await chromium.launch();
  const p = await (await b.newContext({ viewport: { width: 1080, height: 1350 } })).newPage();
  await p.goto('file://' + path.join(__dirname, '..', 'marca', 'promo-fundador.html'));
  await p.waitForLoadState('networkidle'); await p.evaluate(() => document.fonts.ready); await p.waitForTimeout(200);
  await p.screenshot({ path: path.join(__dirname, '..', '..', 'public', 'images', 'promo', 'fundador.png') });
  await b.close();
})();
