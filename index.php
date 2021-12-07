<?php

require __DIR__.'/vendor/autoload.php';

require __DIR__.'/lib/src/loader.php';

require __DIR__.'/app/loader.php';

$bot = new \app\Bot(getenv('TOKEN'));

$bot->onText('\/start',\app\HandleStart::class);

$bot->webhook();
