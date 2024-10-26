<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 跨源资源共享 (CORS) 配置
    |--------------------------------------------------------------------------
    |
    | 在这里您可以配置跨域资源共享的设置
    | 或“CORS”。这决定了可以执行哪些跨域操作
    | 在网络浏览器中。您可以根据需要自由调整这些设置。
    |
    | 要了解更多信息：https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
