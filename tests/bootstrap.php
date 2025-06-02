<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';


if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
