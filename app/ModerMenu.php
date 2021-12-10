<?php


namespace app;


use CURLFile;

class ModerMenu extends \lib\CallbackHandler
{
    public function process($callback, $state = null)
    {
        $data = $callback['data'];
        $user_id = $callback['from']['id'];
        $message_id = $callback['message']['message_id'];
        $operation = explode(' ', $data)[1];

        switch ($operation) {
            case 'reviews':
                return $this->reviews_list($user_id, $message_id);
                break;
            case (bool)(preg_match('#choose_cv#', $operation)):
                $this->api->delete_message($user_id, $message_id);
                $cv_id = (int)(str_replace('choose_cv.', '', $operation));
                $keyboard = [[], []];
                $review = DB::get_review($cv_id);
                for ($i = 0; $i < 6; $i++) {
                    $keyboard[0][] = $i;
                    $keyboard[1][] = "moder_menu mark_cv.{$cv_id}.{$i}";
                }
                $keyboard = $this->api->callback_keyboard($keyboard[0], $keyboard[1], 3);
                $response = "Имя: {$review['full_name']} \nID: {$review['user_id']}\nВыберите оценку:";
                $this->api->send_document($user_id, new CURLFile($review['path']), $response, $keyboard);
                break;
            case (bool)(preg_match('#mark_cv#', $operation)):
                $data = explode('.', $operation); //data[1] - review_id ,data[2] - mark
                DB::mark_review($data[1], $data[2]);
                $cv_owner = DB::get_review($data[1])['user_id'];
                $this->api->send_message($cv_owner, "Ваше резюме было оценено. Ваша оценка: " . $data[2]);
                $this->api->answer_callback_query($callback['id'], "Резюме оценено");
                return $this->reviews_list($user_id, $message_id);
        }
        $this->api->answer_callback_query($callback['id'], "");

    }

    function reviews_list($user_id, $message_id)
    {
        $this->api->delete_message($user_id, $message_id);
        $reviews = DB::get_reviews($user_id);
        if (!$reviews) return $this->api->send_message($user_id, "Неоценённых резюме не осталось", $this->api->moder_menu());
        $data = [[], []];
        foreach ($reviews as $review) {
            $data[0][] = $review['user_id'] . "({$review['id']})";
            $data[1][] = 'moder_menu choose_cv.' . $review['id'];
        }
        $data[0][] = "Главное меню";
        $data[1][] = 'moder_menu back';
        $keyboard = $this->api->callback_keyboard($data[0], $data[1]);
        $this->api->send_message($user_id, "Выберите резюме, которое хотите оценить", $keyboard);
    }
}