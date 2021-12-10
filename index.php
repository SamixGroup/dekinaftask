<?php

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/lib/src/loader.php';

require __DIR__ . '/app/loader.php';

use app\AddOrEditModer;
use app\AdminMenu;
use app\CommandHandler;
use app\FillOrEditCv;
use app\HandleStart;
use app\Bot;
use app\DB;
use app\ModerMenu;
use app\UserMenu;


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

DB::setCreditionals($_ENV['DB_USER'], $_ENV['DB_PASS']);
DB::connect();
$bot = new Bot($_ENV['TOKEN']);
$bot->onText('\/start', HandleStart::class);
$bot->onText('\/+', CommandHandler::class);
$bot->onCallback('admin_menu+', AdminMenu::class);
$bot->onCallback('user_menu+', UserMenu::class);
$bot->onCallback('moder_menu+', ModerMenu::class);
$bot->onText('', AddOrEditModer::class, 'admin+');
$bot->onText('', FillOrEditCv::class, 'user+');

$bot->webhook();
