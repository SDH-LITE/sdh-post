<?php

sdh_actions ('SDH_callback', 'SDH_callback');

/**
 * Функция для обработки AJAX-запроса
 * action SDH_callback
 */
function SDH_callback () {
    $SDHAction = sanitize_text_field ($_POST['SDHAction']);
    $SDHData = $_POST['SDHData'];

    $response = array(
        'status' => 'success',
        'data' => ['info_callback' => 'none']
    );

    if ($SDHAction == 'PaymentsCheck') {
        $file_callback = SDH_callback_read_file ($SDHAction);

        if ($file_callback['result'] == 'ok') {
            $response = array(
                'status' => 'success',
                'data' => $file_callback
            );

            SDH_callback_delete_file ($SDHAction);
        }
    }

    wp_send_json($response); // Отправьте ответ в формате JSON
}