<?php


namespace app;


class CommandHandler extends \lib\TextHandler
{
    public function process($message, $state = null)
    {
        $user = new User($message['from']['id']);
        $text = $message['text'];

        switch ($text) {
            case '/id':
                $this->api->send_message($user->id, "Ваш ИД: " . $user->id);
                break;
            case '/admin':
                $user->set('role', 1);
                $this->api->send_message($user->id, "Статус изменён");
                break;
        }
    }
}