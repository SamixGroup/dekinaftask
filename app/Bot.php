<?php


namespace app;


class Bot extends \lib\Api
{

    function ask_name(User $user)
    {
        $name = $user->get_name();
        $kb = $name ? $this->keyboard([$name]) : null;
        $this->send_message($user->id, 'Введите ваше ФИО.', $kb);
    }

    function ask_date(User $user)
    {
        $date = $user->get_birth_date();
        $kb = $date ? $this->keyboard([$date]) : null;
        $this->send_message($user->id, 'Введите дату вашего рождения.', $kb);
    }

    function ask_phone(User $user)
    {
        $phone = $user->get_phone();
        $kb = $phone ? $this->keyboard([$phone]) : null;
        $this->send_message($user->id, 'Введите ваш телефонный номер.', $kb);
    }

    function user_menu()
    {
        return $this->callback_keyboard(['Заполнить резюме', 'Редактировать резюме', 'Отправить резюме', 'Получить резюме', 'Настройки'], ['user_menu fill_cv', 'user_menu edit_cv', 'user_menu send_cv', 'user_menu get_cv', 'user_menu settings'], 2);
    }

    function admin_menu()
    {
        return $this->callback_keyboard(['Модераторы', 'Статистика'], ['admin_menu moderators', 'admin_menu reviews'], 2);
    }

    function moder_menu()
    {
        return $this->callback_keyboard(['Текущие резюме'], ['moder_menu reviews']);
    }

}