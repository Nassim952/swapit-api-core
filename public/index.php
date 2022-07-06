<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($_SERVER['HTTPS'] = 'on', $context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
