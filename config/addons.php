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
        'name' => 'Recall: a quién ya le toca volver',
        'feature_flag' => 'recall_automation',
        'short_description' => 'Pacientes que hace meses no regresan aparecen listados con click-to-WhatsApp.',
        'long_description' => 'Configura servicios con periodo de recall (ej. limpieza cada 6 meses). DocFácil calcula a qué pacientes ya les toca volver y los pone en su escritorio. Un clic abre su WhatsApp con el mensaje armado y usted da enviar.',
        'monthly_price' => 49.00,
        'annual_price' => 490.00, // 2 meses gratis en anual
        'icon' => 'arrow-path',
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
        'long_description' => 'Ideal para ortodoncia, rehabilitación, implantes. Armas el plan completo con tus servicios + precios + descuento, el paciente recibe un PDF con tu marca por WhatsApp y acepta dándole clic a un link. Registra la IP, la fecha y la hora en que el paciente acepta.',
        'monthly_price' => 129.00,
        'annual_price' => 1290.00,
        'icon' => 'clipboard-document-list',
        'stripe_price_id_monthly' => env('STRIPE_PRICE_ADDON_TREATMENT_PLANS_MONTHLY'),
        'stripe_price_id_annual' => env('STRIPE_PRICE_ADDON_TREATMENT_PLANS_ANNUAL'),
        'beta_trial_days' => 30,
        // Ya viene en todos los planes de pago: la tienda no lo vende.
        'available' => false,
    ],

    'google_reviews' => [
        'slug' => 'google_reviews',
        'name' => 'Reseñas en Google: a quién pedírsela',
        'feature_flag' => 'google_reviews',
        'short_description' => 'Lista pacientes a pedirles reseña. 1 clic y abre WhatsApp con el link a Google Maps listo.',
        'long_description' => 'Después de cada cita completada, DocFácil le muestra a qué pacientes pedirles reseña en Google. Un clic abre su WhatsApp con el mensaje y su link de Google, y usted da enviar. Más reseñas ayudan a que pacientes nuevos lo encuentren sin pagar anuncios.',
        'monthly_price' => 49.00,
        'annual_price' => 490.00,
        'icon' => 'star',
        'stripe_price_id_monthly' => env('STRIPE_PRICE_ADDON_GOOGLE_REVIEWS_MONTHLY'),
        'stripe_price_id_annual' => env('STRIPE_PRICE_ADDON_GOOGLE_REVIEWS_ANNUAL'),
        'beta_trial_days' => 30,
        'available' => true,
    ],
];
