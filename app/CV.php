<?php


namespace app;


class CV
{
    static function generate($user_id): string
    {
        $user_info = (new User($user_id))->get_info();

        $template = str_replace(['%name', '%date', '%phone', '%current_date'], [$user_info['full_name'], $user_info['date_of_birth'], $user_info['phone'], date('Y:m:d')], file_get_contents('./cv.html'));
        try {
            $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/tmp', 'format' => 'A6']);
            $mpdf->WriteHTML($template);
            if (!is_dir('./cv/')) mkdir('cv');
            $path = './cv/' . $user_info['full_name'] . ' - ' . $user_id . '-' . (time()) . '.pdf';
            if (file_exists($path)) unlink($path);
            $mpdf->Output($path);
            return $path;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}

