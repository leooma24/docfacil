// Piezas comunes para grabar los videos de DocFácil con Playwright.
// Formato vertical 4:5 (864x1080, se escala a 1080x1350 al montar).
const fs = require('fs');
const path = require('path');
const { chromium } = require('/Users/omarlerma/Sites/agents/coder/ignis-phltesttpl/node_modules/playwright-core');

const BASE = process.env.DOCFACIL_URL || 'http://127.0.0.1:8841';
const VIEW = { width: 864, height: 1080 };

function duraciones(audioDir) {
  return Object.fromEntries(fs.readFileSync(path.join(audioDir, 'durations.txt'), 'utf8').trim().split('\n')
    .map(l => { const [k, v] = l.split(' '); return [k, parseFloat(v)]; }));
}

// Inicia sesión fuera de la grabación y regresa el estado para reutilizarlo.
async function sesion(browser) {
  const ctx = await browser.newContext({ viewport: VIEW, locale: 'es-MX' });
  const p = await ctx.newPage();
  await p.goto(BASE + '/doctor/login');
  await p.fill('input[type=email]', 'demo@docfacil.com');
  await p.fill('input[type=password]', 'demo2026');
  await p.click('button[type=submit]');
  await p.waitForLoadState('networkidle');
  const estado = await ctx.storageState();
  await ctx.close();
  return estado;
}

// Contexto que graba, con cursor visible y sin el botón flotante de ayuda.
// `vista` cambia el tamaño de la pantalla que se graba: más angosta, todo sale
// más grande en el celular (el video se escala igual a 1080x1350 al montar).
async function contextoGrabando(browser, outDir, estado, vista = VIEW) {
  const ctx = await browser.newContext({
    viewport: vista, locale: 'es-MX', colorScheme: 'light', storageState: estado,
    recordVideo: { dir: outDir, size: vista },
  });
  await ctx.addInitScript(() => {
    const montar = () => {
      if (document.getElementById('__cur')) return;
      const st = document.createElement('style');
      st.textContent = '#__cur{position:fixed;left:0;top:0;width:26px;height:26px;z-index:100000;pointer-events:none;transition:transform .12s;filter:drop-shadow(0 2px 3px rgba(0,0,0,.35))} #__cur.down{transform:scale(.8)}'
        + ' .fi-topbar{position:static !important}';
      document.head.appendChild(st);
      const c = document.createElement('div'); c.id = '__cur';
      c.innerHTML = '<svg viewBox="0 0 22 22" width="26" height="26"><path d="M3 2 L3 18 L7.5 14 L10.5 20.5 L13.2 19.3 L10.3 13 L16.5 13 Z" fill="#0f766e" stroke="#fff" stroke-width="1.4" stroke-linejoin="round"/></svg>';
      c.style.transform = 'translate(-60px,-60px)';
      document.body.appendChild(c);
      addEventListener('mousemove', e => { c.style.left = (e.clientX - 3) + 'px'; c.style.top = (e.clientY - 2) + 'px'; c.style.transform = ''; }, true);
      addEventListener('mousedown', () => c.classList.add('down'), true);
      addEventListener('mouseup', () => c.classList.remove('down'), true);
      // El botón "¿Te ayudo?" tapa la pantalla en un video vertical.
      const ocultarAyuda = () => document.querySelectorAll('a,button,div').forEach(el => {
        if (el.children.length < 4 && /¿Te ayudo\?/.test(el.textContent || '') && getComputedStyle(el).position === 'fixed') el.style.display = 'none';
      });
      ocultarAyuda(); new MutationObserver(ocultarAyuda).observe(document.body, { childList: true, subtree: true });
    };
    addEventListener('DOMContentLoaded', montar);
  });
  return ctx;
}

function ayudantes(page, durs, t0, marks) {
  const W = (ms) => page.waitForTimeout(ms);
  const cap = (t) => page.evaluate((t) => {
    let c = document.getElementById('__cap');
    if (!c) {
      c = document.createElement('div'); c.id = '__cap';
      c.style.cssText = 'position:fixed;left:50%;bottom:34px;transform:translateX(-50%);width:max-content;max-width:800px;background:rgba(15,23,42,.92);color:#fff;font:600 21px/1.35 Inter,system-ui,sans-serif;padding:13px 20px;border-radius:14px;z-index:99999;text-align:center;box-shadow:0 10px 28px rgba(0,0,0,.25);pointer-events:none';
      document.body.appendChild(c);
    }
    c.style.display = t ? 'block' : 'none';
    c.textContent = t || '';
  }, t);
  const visible = async (loc) => { await loc.first().waitFor({ state: 'visible', timeout: 8000 }); return loc.first(); };
  const centro = async (loc) => {
    const el = await visible(loc);
    await el.scrollIntoViewIfNeeded();
    const b = await el.boundingBox();
    return { x: b.x + b.width / 2, y: b.y + b.height / 2 };
  };
  const mover = async (loc, steps = 18) => { const p = await centro(loc); await page.mouse.move(p.x, p.y, { steps }); return p; };
  const clic = async (loc, steps = 16) => { const p = await mover(loc, steps); await W(140); await page.mouse.click(p.x, p.y); };
  const escribir = async (loc, texto, delay = 32) => { await clic(loc, 12); await page.keyboard.type(texto, { delay }); };
  const bajar = async (y, wait = 500) => { await page.evaluate((y) => scrollBy({ top: y, behavior: 'smooth' }), y); await W(wait); };
  const listo = () => page.waitForLoadState('networkidle').catch(() => {});

  async function seg(i, caption, acciones) {
    const key = 'seg' + String(i).padStart(2, '0');
    const inicio = Date.now();
    marks.push({ key, at: (inicio - t0) / 1000 });
    await cap(caption);
    if (acciones) await acciones();
    const gastado = Date.now() - inicio;
    const necesita = durs[key] * 1000 + 450 - gastado;
    console.log(`${key}: acciones ${gastado} ms de ${Math.round(durs[key] * 1000 + 450)} ms`);
    if (necesita > 0) await W(necesita);
  }

  // Acercamiento de cámara: escala la página para que los elementos (selectores
  // CSS) queden dentro de la pantalla, de orilla a orilla.
  const acercar = (selectores, max = 1.9) => page.evaluate(([sel, max]) => {
    const cajas = sel.map(q => (typeof q === 'string' ? document.querySelector(q) : null)).filter(Boolean).map(e => e.getBoundingClientRect());
    const m = document.querySelector('.fi-main') || document.body;
    const r = m.getBoundingClientRect();
    const L = Math.min(...cajas.map(c => c.left)) - 14, R = Math.max(...cajas.map(c => c.right)) + 14;
    const T = Math.min(...cajas.map(c => c.top)) - 60, B = Math.max(...cajas.map(c => c.bottom)) + 60;
    const s = Math.min(max, (innerWidth - 20) / (R - L), (innerHeight - 160) / (B - T));
    if (s < 1.15) return; // no cabe más grande: no se aleja ni se mueve
    // Lo que se muestra queda centrado a lo ancho y un poco arriba de la mitad.
    const cx = (L + R) / 2, cy = (T + B) / 2;
    const x0 = (s * cx - innerWidth / 2) / (s - 1), y0 = (s * cy - innerHeight * 0.42) / (s - 1);
    m.style.transition = 'transform 1s ease';
    m.style.transformOrigin = `${x0 - r.left}px ${y0 - r.top}px`;
    m.style.transform = `scale(${s})`;
  }, [selectores, max]);

  return { W, cap, mover, clic, escribir, bajar, listo, seg, visible, acercar };
}

async function cerrar(ctx, page, outDir, marks) {
  await page.waitForTimeout(500);
  const vid = page.video();
  await ctx.close();
  fs.writeFileSync(path.join(outDir, 'marks.json'), JSON.stringify(marks, null, 1));
  return vid.path();
}

module.exports = { chromium, BASE, VIEW, duraciones, sesion, contextoGrabando, ayudantes, cerrar };
