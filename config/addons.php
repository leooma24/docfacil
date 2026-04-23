<?php

/**
 * Catalogo de add-ons — source of truth para el marketplace.
 *
 * Cada entrada mapea un slug unico a su metadata + feature_flag.
 * Cuando una clinica tiene un ClinicAddon con ese slug y isActive()=true,
 * Clinic::hasFeature(feature_flag) devuelve true aunque no este en
 * featuresForPlan('basico'/'profesional'/'clinica').
 *
 * Precios en MXN mensual. Stripe Price IDs se seteran por env vars
 * cuando se implemente la integracion completa de billing multi-item.
 */
return [
    'recall_automation' => [
        'slug' => 'recall_automation',
        'name' => 'Recall automático',
        'feature_flag' => 'recall_automation',
        'short_description' => 'Pacientes que hace meses no regresan aparecen listados con click-to-WhatsApp.',
        'long_description' => 'Configura servicios con periodo de recall (ej. limpieza cada 6 meses). DocFácil calcula qué pacientes ya te tocan y los lista cada semana con un botón para abrir WhatsApp con el mensaje armado.',
        'monthly_price' => 49.00,
        'annual_price' => 490.00, // 2 meses gratis en anual
        'icon' => '🦷',
        'revenue_hypothesis' => 'Recupera $10-30k/mes en consultas de seguimiento perdidas.',
        'stripe_price_id_monthly' => env('STRIPE_PRICE_ADDON_RECALL_MONTHLY'),
        'stripe_price_id_annual' => env('STRIPE_PRICE_ADDON_RECALL_ANNUAL'),
        'beta_trial_days' => 30,
        'available' => true,
    ],

    'treatment_plans' => [
        'slug' => 'treatment_plans',
        'name' => 'Plan de tratamiento / Presupuestos',
        'feature_flag' => 'treatment_plans',
        'short_description' => 'Arma presupuestos multi-cita, genera PDF bonito y el paciente acepta en línea.',
        'long_description' => 'Ideal para ortodoncia, rehabilitación, implantes. Armas el plan completo con tus servicios + precios + descuento, el paciente recibe un PDF con tu marca por WhatsApp y acepta dándole clic a un link. Registra IP y timestamp como respaldo legal.',
        'monthly_price' => 129.00,
        'annual_price' => 1290.00,
        'icon' => '📋',
        'revenue_hypothesis' => 'Sube 20% tu tasa de aceptación de tratamientos grandes ($20-80k).',
        'stripe_price_id_monthly' => env('STRIPE_PRICE_ADDON_TREATMENT_PLANS_MONTHLY'),
        'stripe_price_id_annual' => env('STRIPE_PRICE_ADDON_TREATMENT_PLANS_ANNUAL'),
        'beta_trial_days' => 30,
        'available' => true,
    ],
];
