<?php


namespace app;


class AdminMenu extends \lib\CallbackHandler
{
    function process($callback, $state = null)
    {
        $data = $callback['data'];
        $user_id = $callback['from']['id'];
        $message_id = $callback['message']['message_id'];
        $operation = explode(' ', $data)[1];

        switch ($operation) {
            case 'moderators':
                $this->api->edit_message_text(
                    $user_id,
                    $message_id,
                    'Moderators',
                    $this->api->callback_keyboard(
                        ['Добавить', "Удалить", "Редактировать", 'Главное меню'],
                        ['admin_menu add_moderator', 'admin_menu delete_moderator', 'admin_menu edit_moderator', 'admin_menu back'],
                        2
                    )
                );
                break;
            case 'add_moderator':
                $this->api->delete_message($user_id, $message_id);
                $this->api->send_message($user_id, 'Введите имя модератора:', $this->api->callback_keyboard(['Отменить'], ['admin_menu back']));
                User::set_state_byId($user_id, 'admin+add_moder+name');
                break;
            case (bool)preg_match('#delete_moderator#', $operation):
                if ($operation != 'delete_moderator') {
                    $moder_id = (int)str_replace('delete_moderator.', '', $operation);
                    DB::delete_moder($moder_id);
                    $this->api->answer_callback_query($callback['id'], "Модератор успешно удалён");
                }
                $this->list_moders_delete($callback);
                break;

            case (bool)preg_match('#edit_moderator#', $operation):
                if ($operation != 'edit_moderator') {
                    $this->api->delete_message($user_id, $message_id);
                    $moder_id = (int)str_replace('edit_moderator.', '', $operation);
                    $this->api->send_message(
                        $user_id,
                        "Введите новое имя модератора",
                        $this->api->callback_keyboard(['Отменить'], ['admin_menu back'])
                    );
                    User::set_state_byId($user_id, 'admin+edit_moder+id|' . $moder_id);

                }
                $this->list_moders_edit($callback);
                break;

            case 'reviews':
                $this->list_moders_review($callback);
                break;

            case (bool)(preg_match("#show_reviews#", $operation)):
                $moder_id = str_replace('show_reviews.', '', $operation);
                $reviews_count = count(DB::get_reviews($moder_id, 1));
                $moder = new User($moder_id);
                $response = "Статистика по модератору\nИмя: {$moder->get()['name']}\nКоличество оценённых работ: {$reviews_count}\nИД: {$moder_id}";
                $this->api->edit_message_text($user_id, $message_id, $response, $this->api->callback_keyboard(['К списку модераторов'], ['admin_menu reviews']));
                break;
            case 'back':
                $this->api->delete_message($user_id, $message_id);
                User::set_state_byId($user_id);
                $this->api->send_message(
                    $user_id,
                    "Admin",
                    $this->api->admin_menu()
                );
                break;
        }
        $this->api->answer_callback_query($callback['id']);
    }


    function list_moders_delete($callback)
    {
        $user_id = $callback['from']['id'];
        $message_id = $callback['message']['message_id'];
        $moderators = DB::get_moders();
        if (empty($moderators)) {
            $this->api->delete_message($user_id, $message_id);
            $this->api->send_message($user_id, "Нет модераторов для удаления", $this->api->admin_menu());
            return $this->api->answer_callback_query($callback['id'], "Модераторов для удаления нет!");
        }
        $data = [[], []];
        foreach ($moderators as $moder) {
            $data[0][] = $moder['name'];
            $data[1][] = 'admin_menu delete_moderator.' . $moder['id'];
        }
        $data[0][] = "Главное меню";
        $data[1][] = 'admin_menu back';
        $keyboard = $this->api->callback_keyboard($data[0], $data[1]);
        $this->api->edit_message_text($user_id, $message_id, "Выберите модератора для удаления", $keyboard);
    }

    function list_moders_edit($callback)
    {
        $user_id = $callback['from']['id'];
        $message_id = $callback['message']['message_id'];
        $moderators = DB::get_moders();
        if (empty($moderators)) {
            $this->api->delete_message($user_id, $message_id);
            $this->api->send_message($user_id, "Нет модераторов для изменения", $this->api->admin_menu());
            return $this->api->answer_callback_query($callback['id'], "Модераторов для изменения нет!");
        }
        $data = [[], []];
        foreach ($moderators as $moder) {
            $data[0][] = $moder['name'];
            $data[1][] = 'admin_menu edit_moderator.' . $moder['user_id'];
        }
        $data[0][] = "Главное меню";
        $data[1][] = 'admin_menu back';
        $keyboard = $this->api->callback_keyboard($data[0], $data[1]);
        $this->api->edit_message_text($user_id, $message_id, "Выберите модератора для изменения", $keyboard);
    }

    function list_moders_review($callback)
    {
        $user_id = $callback['from']['id'];
        $message_id = $callback['message']['message_id'];
        $moderators = DB::get_moders();
        if (empty($moderators)) {
            $this->api->delete_message($user_id, $message_id);
            $this->api->send_message($user_id, "Нет модераторов", $this->api->admin_menu());
            return $this->api->answer_callback_query($callback['id'], "Список модераторов пуст");
        }
        $data = [[], []];
        foreach ($moderators as $moder) {
            $data[0][] = $moder['name'];
            $data[1][] = 'admin_menu show_reviews.' . $moder['user_id'];
        }
        $data[0][] = "Главное меню";
        $data[1][] = 'admin_menu back';
        $keyboard = $this->api->callback_keyboard($data[0], $data[1]);
        $this->api->edit_message_text($user_id, $message_id, "Выберите модератора для просмотра статистики", $keyboard);
    }

}