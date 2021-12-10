<?php


namespace app;


use lib\TextHandler;

class AddOrEditModer extends TextHandler
{
    function process($message, $state = null)
    {

        $action = explode('+', $state)[1];
        $user_id = $message['from']['id'];
        $text = $message['text'];

        if ((new User($user_id))->get_role() != 1) return $this->api->send_message($user_id, "Эта операция только для администраторов");

        switch ($action) {
            case 'add_moder':
                $step = explode('+', $state)[2];
                switch ($step) {
                    case 'name':
                        User::set_state_byId($user_id, 'admin+add_moder+id|' . $text);
                        $this->api->send_message($user_id, "Введите телеграм ID модератора (каждый может узнать свой ИД командой /id)", $this->api->callback_keyboard(['Отменить'], ['admin_menu back']));
                        break;
                    case (bool)preg_match('#id\|#', $step):
                        if (!is_numeric($text)) return $this->api->send_message($user_id, "Идентификатор должен иметь числовое значение");
                        DB::save_moder(str_replace("id|", '', $step), (int)$text);
                        User::set_state_byId($user_id);
                        $this->api->send_message(
                            $user_id,
                            "Admin",
                            $this->api->admin_menu()
                        );
                        break;
                }
                break;
            case 'edit_moder':
                $step = explode('+', $state)[2];
                switch ($step) {
                    case (bool)preg_match('#id\|#', $step):
                        $moder_id = str_replace('id|', '', $step);
                        DB::save_moder($text, (int)($moder_id));
                        User::set_state_byId($user_id);
                        $this->api->send_message(
                            $user_id,
                            "Изменения сохранены",
                            $this->api->admin_menu()
                        );
                        break;
                }
                break;
        }
    }
}

