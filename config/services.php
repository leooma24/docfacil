<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'notifications' => [
        // Destinatarios para avisos internos (nuevos leads landing, etc.).
        // CSV en NOTIFY_EMAILS. Cuando el dominio tenga buzón real, agregar admin@docfacil.com.
        'emails' => env('NOTIFY_EMAILS', 'leooma24@gmail.com'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
        // Meta App Secret — usado para validar firma X-Hub-Signature-256
        // de los webhooks entrantes. Si no esta seteado el webhook rechaza todo.
        'app_secret' => env('WHATSAPP_APP_SECRET'),
    ],

    'mercadopago' => [
        'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
        // IDs de los 6 precios creados en Stripe (3 planes x 2 ciclos).
        // Las keys usan el slug interno del plan (basico, profesional, clinica).
        'prices' => [
            'basico_monthly'      => env('STRIPE_PRICE_BASICO_MONTHLY'),
            'basico_annual'       => env('STRIPE_PRICE_BASICO_ANNUAL'),
            'profesional_monthly' => env('STRIPE_PRICE_PRO_MONTHLY'),
            'profesional_annual'  => env('STRIPE_PRICE_PRO_ANNUAL'),
            'clinica_monthly'     => env('STRIPE_PRICE_CLINICA_MONTHLY'),
            'clinica_annual'      => env('STRIPE_PRICE_CLINICA_ANNUAL'),
        ],
    ],

    // Datos bancarios para pagos por SPEI (transferencia manual con aprobación)
    'spei' => [
        'enabled'   => env('SPEI_ENABLED', true),
        'banco'     => env('SPEI_BANCO', 'BanBajío'),
        'titular'   => env('SPEI_TITULAR', 'Omar Alonso Lerma Orduño'),
        'clabe'     => env('SPEI_CLABE', '030743900001300398'),
        // Correos que reciben notificación de nuevo SPEI pendiente de aprobación
        'admin_emails' => env('SPEI_ADMIN_EMAILS', 'leooma24@gmail.com'),
    ],

    'ai' => [
        'enabled' => env('AI_ENABLED', false),
        'max_daily_cost_usd' => env('AI_MAX_DAILY_COST_USD', 5),
        'provider' => env('AI_PROVIDER', 'deepseek'),
        'chatbot_enabled' => env('CHATBOT_ENABLED', true),
        'chatbot_max_daily_cost_usd' => env('CHATBOT_MAX_DAILY_COST_USD', 2),
        'anthropic' => [
            'key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001'),
        ],
        'deepseek' => [
            'key' => env('DEEPSEEK_API_KEY'),
            'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
            'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1'),
        ],
        'openai' => [
            'key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        ],
    ],

];
