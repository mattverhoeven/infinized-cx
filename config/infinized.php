<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Infinized platform URL
    |--------------------------------------------------------------------------
    |
    | Base URL for the Infinized product (no trailing slash). API calls use
    | {url}/api/v1.
    |
    */

    'url' => rtrim(env('INFINIZED_URL', 'https://app.infinized.com'), '/'),

    /*
    |--------------------------------------------------------------------------
    | Platform operator token
    |--------------------------------------------------------------------------
    |
    | Server-side only (ifo_…). Used to provision partner tenants and mint org
    | API keys. Never expose to the browser.
    |
    */

    'operator_token' => env('INFINIZED_OPERATOR_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Partner organization slug
    |--------------------------------------------------------------------------
    |
    | When CX auto-provisions an Infinized org, it is created as a child tenant
    | of this partner organization (partner_organization_id).
    |
    */

    'partner_organization_slug' => env('INFINIZED_PARTNER_ORG_SLUG'),

    /*
    |--------------------------------------------------------------------------
    | Webhook signing secret
    |--------------------------------------------------------------------------
    |
    | Verifies outbound webhooks from Infinized to CX (whsec_… from Infinized
    | webhook endpoint registration).
    |
    */

    'webhook_secret' => env('INFINIZED_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Default API key scopes for provisioned workspaces
    |--------------------------------------------------------------------------
    */

    'default_api_key_abilities' => [
        'read:organization',
        'write:organization',
        'read:agents',
        'write:agents',
        'read:knowledge',
        'write:knowledge',
        'read:conversations',
        'write:conversations',
        'read:integrations',
        'write:integrations',
        'read:webhooks',
        'write:webhooks',
        'read:teams',
        'write:teams',
        'read:activity',
        'read:runs',
        'read:approvals',
        'write:approvals',
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP client
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env('INFINIZED_HTTP_TIMEOUT', 30),
    'connect_timeout' => (int) env('INFINIZED_HTTP_CONNECT_TIMEOUT', 10),

];
