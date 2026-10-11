{{-- Los formularios del panel, más claros (11-oct-2026): secciones con su
     ícono, etiquetas que se leen, ayudas discretas y los botones de guardar
     siempre a la vista, en una barra fija abajo. --}}
<style>
    /* Encabezado de cada sección */
    .fi-section-header-heading { font-size: 1rem !important; font-weight: 700 !important; color: #0f172a !important; letter-spacing: -0.01em; }
    .fi-section-header-description { font-size: 0.85rem !important; color: #64748b !important; line-height: 1.45 !important; }
    .fi-section-header-icon { color: #0d9488 !important; }

    /* Secciones con el título a un lado: el título arriba de su descripción y
       una línea que separa una sección de la otra. */
    .fi-fo-component-ctn > .fi-aside { padding-top: 0.5rem; }
    .fi-fo-component-ctn > .fi-aside + .fi-aside { border-top: 1px solid #e2e8f0; padding-top: 1.75rem; }

    /* En las secciones con título a un lado, la tarjeta es solo la de los
       campos: el título queda afuera, sobre el fondo. */
    .fi-page form.fi-form .fi-section.fi-aside { border: none !important; box-shadow: none !important; background: transparent !important; overflow: visible !important; }
    .fi-page form.fi-form > .fi-section.fi-aside:first-of-type::before { display: none; }

    /* Etiquetas y ayudas */
    .fi-fo-field-wrp-label > span { font-weight: 600 !important; color: #1e293b !important; }
    .fi-fo-field-wrp-helper-text { font-size: 0.8rem !important; color: #64748b !important; line-height: 1.4 !important; }

    /* Los campos */
    .fi-input-wrp { border-radius: 8px !important; }
    .fi-input-wrp:focus-within { box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15) !important; }

    /* Los botones de guardar, fijos abajo y a la vista */
    .fi-page .fi-form > .fi-form-actions,
    .fi-page .fi-form-actions:not(.fi-modal .fi-form-actions) {
        position: sticky; bottom: 0; z-index: 20;
        margin-top: 1.25rem; padding: 0.75rem 1rem;
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(8px);
        border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 -6px 18px rgba(15, 23, 42, 0.06);
    }
    .fi-page .fi-form-actions .fi-btn { min-height: 2.6rem; padding-left: 1.1rem; padding-right: 1.1rem; font-weight: 700; }

    /* En el celular: los botones en un renglón; «Guardar y agregar otro»
       queda para la computadora. */
    @media (max-width: 640px) {
        .fi-page .fi-form-actions [wire\:click="createAnother"] { display: none; }
        .fi-page .fi-form > .fi-form-actions, .fi-page .fi-form-actions:not(.fi-modal .fi-form-actions) { margin-left: -0.25rem; margin-right: -0.25rem; padding: 0.6rem 0.75rem; }
    }
</style>
