<?php

require 'vendor/autoload.php';

require 'lib/src/loader.php';

require 'app/loader.php';

$bot = new \app\Bot(getenv('TOKEN'));

$bot->onText('\/start',\app\HandleStart::class);