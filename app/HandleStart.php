<?php


namespace app;


class HandleStart extends \lib\TextHandler
{

    public function process($message, $state = null)
    {
        $user = new User($message['from']['id']);
        $role = $user->get_role();

        switch ($role){
            case 1:
                $this->api->send_message(
                    $user->id,
                    "Admin",
                    $this->api->callback_keyboard(['Moderators','Feedbacks'],['admin menu moderators','admin menu feedbacks'],2)
                );
        }

        $user->set_state();
    }

}