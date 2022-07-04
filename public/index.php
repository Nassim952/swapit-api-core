<?php

use App\Kernel;

// if((!empty( $_SERVER['HTTP_X_FORWARDED_HOST'])) || (!empty( $_SERVER['HTTP_X_FORWARDED_FOR'])) ) {
//     $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
//     $_SERVER['HTTPS'] = 'on';
// }

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
