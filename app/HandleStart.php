<?php


namespace app;


use lib\TextHandler;

class HandleStart extends TextHandler
{

    public function process($message, $state = null)
    {
        $user = new User($message['from']['id']);
        $role = $user->get_role();

        switch ($role) {
            case 1:
                $this->api->send_message(
                    $user->id,
                    "Admin",
                    $this->api->admin_menu()
                );
                break;
            case 2:
                $this->api->send_message(
                    $user->id,
                    "Moder menu",
                    $this->api->moder_menu()
                );
                break;
            default:
                $user->save_or_ignore();
                $this->api->send_message(
                    $user->id,
                    "User menu",
                    $this->api->user_menu()
                );
                break;

        }

        $user->set_state();
        die();
    }

}