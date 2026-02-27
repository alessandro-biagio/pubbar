<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    | Config centralizzata per servizi esterni (mail, notifiche, pagamenti).
    | Le chiavi reali stanno sempre in .env
    */

    // Email transazionali (Postmark)
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    // Email API alternativa (Resend)
    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    // AWS SES (invio mail via Amazon)
    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Notifiche Slack (opzionale)
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // PayPal Checkout (usato nel flusso pagamento)
    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret'    => env('PAYPAL_SECRET'),
        'mode'      => env('PAYPAL_MODE', 'sandbox'), // sandbox | live
        'currency'  => env('PAYPAL_CURRENCY', 'EUR'),
    ],

];
