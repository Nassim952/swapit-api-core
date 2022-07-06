<?php

use App\Kernel;

$_SERVER['HTTP_X_FORWARDED_PROTO'] = $_SERVER['HTTP_CUSTOM_FORWARDED_PROTO'];

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
