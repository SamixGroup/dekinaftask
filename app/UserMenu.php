<?php


namespace app;

use lib\CallbackHandler;

class UserMenu extends CallbackHandler
{
    function process($callback, $state = null)
    {
        $data = $callback['data'];
        $user = new User($callback['from']['id']);
        $message_id = $callback['message']['message_id'];
        $operation = explode(' ', $data)[1];

        switch ($operation) {
            case 'fill_cv':
                $this->api->delete_message($user->id, $message_id);
                $this->api->ask_name($user);
                User::set_state_byId($user->id, 'user fill_cv name');
                break;
            case 'edit_cv':
                $this->api->delete_message($user->id, $message_id);
                $this->api->ask_name($user);
                User::set_state_byId($user->id, 'user edit_cv name');
                break;
            case 'change_cv':
                $this->api->delete_message($user->id, $message_id);
                $path = CV::generate($user->id);
                $user->set('path', $path);
                $this->api->send_message($user->id, "Резюме успешно обновлено", $this->api->user_menu());
                break;
            case 'get_cv':
                $filled = $user->get('filled');
                if (!$filled) return $this->api->answer_callback_query($callback['id'], "Вы ещё не заполнили резюме");
                $this->api->delete_message($user->id, $message_id);
                $this->api->answer_callback_query($callback['id'], "Отправляем...");
                $this->api->send_document($user->id, new \CURLFile($user->get('path')), 'Ваше резюме', $this->api->user_menu());
                break;
            case 'settings':
                $this->api->delete_message($user->id, $message_id);
                $this->api->send_message($user->id, "Что вы хотите изменить?", $this->api->callback_keyboard(['ФИО', "Дата рождения", "Номер телефона", 'Назад'], ['user_menu set_name', 'user_menu set_date_of_birth', 'user_menu set_phone', 'user_menu back'], 2));
                break;
            case (bool)(preg_match('#set_#', $operation)):
                $this->api->delete_message($user->id, $message_id);
                $step = str_replace('set_', '', $operation);
                switch ($step) {
                    case 'name':
                        $this->api->ask_name($user);
                        break;
                    case 'date_of_birth':
                        $this->api->ask_date($user);
                        break;
                    case 'phone':
                        $this->api->ask_phone($user);
                        break;
                }
                $user->set_state('user set' . $step);
                break;
            case (bool)preg_match('#send_cv#', $operation):
                $this->api->delete_message($user->id, $message_id);
                if ($operation != 'send_cv') {
                    $moder_id = (int)str_replace('send_cv.', '', $operation);
                    $cv = $user->get('path');
                    DB::add_review($user->id, $moder_id);
                    $this->api->send_message($user->id, "Главное меню", $this->api->user_menu());
                    $this->api->send_document($moder_id, new \CURLFile($cv), "Новое резюме на оценку\nID пользователя: {$user->id}", $this->api->callback_keyboard(['Открыть неоценённые резюме'], ['moder_menu reviews']));
                    return $this->api->answer_callback_query($callback['id'], "Ваше резюме успешно отправлено модератору");
                }
                $moderators = DB::get_moders();
                $data = [[], []];
                foreach ($moderators as $moder) {
                    $data[0][] = $moder['name'];
                    $data[1][] = 'user_menu send_cv.' . $moder['user_id'];
                }
                $data[0][] = "Главное меню";
                $data[1][] = 'user_menu back';
                $keyboard = $this->api->callback_keyboard($data[0], $data[1]);
                $this->api->send_message($user->id, "Выберите модератора, которому хотите отправить резюме", $keyboard);
                break;
            case 'back':
                $this->api->delete_message($user->id, $message_id);
                User::set_state_byId($user->id);
                $this->api->send_message(
                    $user->id,
                    "User",
                    $this->api->user_menu()
                );
                break;
        }
    }
}