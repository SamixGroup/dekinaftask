<?php


namespace app;


class FillOrEditCv extends \lib\TextHandler
{
    public function process($message, $state = null)
    {
        $action = explode(' ', $state)[1];
        $user = new User($message['from']['id']);
        $text = $message['text'];

        switch ($action) {
            case 'fill_cv':
                $step = explode(' ', $state)[2];
                switch ($step) {
                    case 'name':
                        if (strlen($text) < 15) return $this->api->send_message($user->id, "Слишком короткое имя! Введите заново");
                        $user->set_name($text);
                        $this->api->ask_date($user);
                        User::set_state_byId($user->id, 'user fill_cv birth_date');
                        break;
                    case 'birth_date':
                        if (strlen($text) < 5) return $this->api->send_message($user->id, "Кажется вы ввели неправильную дату! Введите заново");
                        $user->set_date($text);
                        $this->api->ask_phone($user);
                        User::set_state_byId($user->id, 'user fill_cv phone');
                        break;
                    case 'phone':
                        if (!is_numeric(str_replace('+', '', $text))) return $this->api->send_message($user->id, "Кажется вы ввели неправильый номер телефона! Введите заново");
                        $user->set_phone($text);
                        $cv = CV::generate($user->id);
                        $user->set('path', $cv);
                        $user->set('filled', 1);
                        $this->api->send_document($user->id, new \CURLFile($cv), "Главное меню", $this->api->user_menu());
                        User::set_state_byId($user->id);
                        break;
                }
                break;
            case 'edit_cv':
                $step = explode(' ', $state)[2];
                switch ($step) {
                    case 'name':
                        if (strlen($text) < 15) return $this->api->send_message($user->id, "Слишком короткое имя! Введите заново");
                        $user->set_name($text);
                        $this->api->ask_date($user);
                        User::set_state_byId($user->id, 'user edit_cv birth_date');
                        break;
                    case 'birth_date':
                        if (strlen($text) < 5) return $this->api->send_message($user->id, "Кажется вы ввели неправильную дату! Введите заново");
                        $user->set_date($text);
                        $this->api->ask_phone($user);
                        User::set_state_byId($user->id, 'user edit_cv phone');
                        break;
                    case 'phone':
                        if (!is_numeric(str_replace('+', '', $text))) return $this->api->send_message($user->id, "Кажется вы ввели неправильый номер телефона! Введите заново");
                        $user->set_phone($text);
                        $cv = CV::generate($user->id);
                        $old_cv = $user->get('path');
                        $this->api->send_document($user->id, new \CURLFile($old_cv), "Старое резюме");
                        $this->api->send_document($user->id, new \CURLFile($cv), "Новое резюме. Заменить данные резюме?", $this->api->callback_keyboard(['Заменить', 'Отменить'], ['user_menu change_cv', 'user_menu back'], 2));
                        User::set_state_byId($user->id);
                        break;
                }
                break;
            case 'set':
                $step = explode(' ', $state)[2];
                switch ($step) {
                    case 'name':
                        if (strlen($text) < 15) return $this->api->send_message($user->id, "Слишком короткое имя! Введите заново");
                        $user->set_name($text);
                        $user->set('path', CV::generate($user->id));
                        User::set_state_byId($user->id);
                        break;
                    case 'date_of_birth':
                        if (strlen($text) < 5) return $this->api->send_message($user->id, "Кажется вы ввели неправильную дату! Введите заново");
                        $user->set_date($text);
                        $user->set('path', CV::generate($user->id));
                        User::set_state_byId($user->id);
                        break;
                    case 'phone':
                        if (!is_numeric(str_replace('+', '', $text))) return $this->api->send_message($user->id, "Кажется вы ввели неправильый номер телефона! Введите заново");
                        $user->set_phone($text);
                        $cv = CV::generate($user->id);
                        $user->set('path', $cv);
                        $user->set('path', CV::generate($user->id));
                        User::set_state_byId($user->id);
                        break;
                }
                $this->api->send_message($user->id, "Изменения усмпешно сохранены,вы можете получить новую версию резюме нажав на кнопку <Получить резюме>", $this->api->user_menu());
                break;

        }

    }
}