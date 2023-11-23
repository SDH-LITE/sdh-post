<?php

/**
 * Функция для получения лайков с поста /
 * Function for getting likes from a post
 *
 * @param $post_id int
 * @return int like_count
 */
function get_like_count ($post_id) {
    $like_count = 0;
    $post_meta = get_post_meta($post_id);

    foreach ($post_meta as $key => $value) {
        if (strpos($key, '_liked_by_user_') === 0) {
            $like_count++;
        }
    }

    return $like_count;
}


/**
 * Функция для перевода именных ролей в числовые /
 * Function for converting nominal roles to numeric ones
 *
 * @return int level access
 */
function SDH_get_role_level () {
    $current_user = wp_get_current_user();
    $user_roles = $current_user->roles;

    $role_levels = array(
        'subscriber' => 0,
        'contributor' => 1,
        'author' => 2,
        'editor' => 3,
        'administrator' => 4,
    );

    $user_level = 0; // Уровень по умолчанию

    foreach ($user_roles as $role) {
        if (isset($role_levels[$role])) {
            $user_level = max($user_level, $role_levels[$role]);
        }
    }

    return $user_level;
}


/**
 * Функция для обертывания html кода
 *
 * @param $path string путь до html файла
 * @param $data array переменные, которые надо передать ['link' => 123, ...]
 *
 * @return string HTML код
 */
function bodyWrapping ($path, $data = []) {
    extract($data); // Извлекаем переменные из массива

    ob_start();
    include SDH_PATH . $path;
    return ob_get_clean();
}

/**
 * Функция для добавления действий к AJAX
 *
 * @param string $action имя действия
 * @param string $func_name имя функции
 */
function sdh_actions (string $action = '', string $func_name = '') {
    // хук для зарегистрированных пользователей
    add_action("wp_ajax_$action", $func_name);
    // хук для незарегистрированных пользователей
    add_action("wp_ajax_nopriv_$action", $func_name);
}

/**
 * Функция для загрузки файлов в дирикторию sdh-upload
 *
 * @param string $file_url url file который копируем
 * @param bool $type тип ответа (true - полный путь, false - путь по url)
 * @param string $new_file_name новое имя файла
 *
 * @return string путь до файла
 */
function sdh_download_file ($file_url, $type = true, $new_file_name = '') {
    // Если новое имя файла не передано, получаем имя из URL
    $file_name = $new_file_name ? sanitize_file_name($new_file_name) : basename($file_url);

    // Создаем полный путь для сохранения файла
    $file_path = trailingslashit(SDH_UPLOAD_PATH) . $file_name;

    // Получаем содержимое файла по URL
    $response = wp_remote_get($file_url);

    if (is_wp_error($response)) {
        // Обработка ошибки запроса
        return $response->get_error_message();
    }

    // Получаем тело ответа (содержимое файла)
    $file_content = wp_remote_retrieve_body($response);

    // Записываем содержимое файла в локальный файл
    $result = file_put_contents($file_path, $file_content);

    if ($result === false) {
        // Обработка ошибки записи файла
        return 'Error writing file to disk.';
    }

    return ($type) ? $file_path : trailingslashit(SDH_UPLOAD_URL) . $file_name;
}


/**
 * Функция для поиска копий в дириктории sdh-upload
 *
 * @param string $file_name имя файла для поиска sdh_order_205_by_1
 *
 * @return bool false or PACH
 */
function sdh_is_file_name_exists ($file_name) {
    $directory = trailingslashit(SDH_UPLOAD_PATH);
    $files = scandir($directory);

    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $parts = explode('_', $file);
            $base_name = $parts[0] . '_' . $parts[1] . '_' . $parts[2] . '_' . $parts[3] . '_' . $parts[4];

            if ($base_name === $file_name) {
                return SDH_UPLOAD_URL . $file;
            }

        }
    }

    return false;
}


/**
 * Функция для пинга скрипта аддона 'activate'
 * говорит о том, что аддон активирован
 *
 * @param string $url ссылка на 'activate'
 *
 */
function ping_script_async ($url) {
    // Опции запроса
    $args = array(
        'body' => ['activate' => 1],
    );

    // Отправка запроса асинхронно
    wp_remote_post ($url, $args);
}


/**
 * Функция для активации аддона
 *
 * @param string $addon_name имя аддона
 *
 */
function sdh_activate_addon ($addon_name) {
    $active_addons = get_option('sdh_active_addons', array());

    if (!in_array($addon_name, $active_addons)) {
        ping_script_async (SDH_URL . 'addons/' . $addon_name . '/activate.php');

        $active_addons[] = $addon_name;
        update_option('sdh_active_addons', $active_addons);
    }
}

/**
 * Функция для деактивации аддона
 *
 * @param string $addon_name имя аддона
 *
 */
function sdh_deactivate_addon ($addon_name) {
    $active_addons = get_option('sdh_active_addons', array());

    $index = array_search($addon_name, $active_addons);
    if ($index !== false) {
        ping_script_async (SDH_URL . 'addons/' . $addon_name . '/deactivate.php');

        unset($active_addons[$index]);
        update_option('sdh_active_addons', $active_addons);
    }
}


/**
 * Функция для получения активированных аддонов
 */
function sdh_get_active_addons () {
    return get_option ('sdh_active_addons', array());
}

/**
 * Функция для получения активных платежных систем
 */
function sdh_get_payment () {
    return SDH()->get_registered_gateways();
}

/**
 * Функция для получения активированных функций платежных систем
 */
function sdh_get_payment_function () {
    return SDH()->get_registered_gateways_functions();
}

/**
 * Функция для вывоза хука платежной системы
 *
 * @param string $gateway_name имя платежки
 * @param string $function_name имя функции
 * @param mixed ...$params передаваемые значения
 *
 * @return string Ссылка на оплату
 */
function SDH_call_payment ($gateway_name, $function_name, ...$params) {
    if (in_array($gateway_name, sdh_get_payment())) {
        // Получаем имя функции и проверяем, существует ли она
        if (is_callable($function_name)) {
            // Вызываем функцию, передавая параметры
            return $function_name(...$params);
        } else {
            // Обработка ситуации, если функция не найдена
            wp_die('Function not found.');
        }
    } else {
        // Обработка ситуации, если шлюз не зарегистрирован
        wp_die('Gateway not registered.');
    }
}

/**
 * Функция для записи коллбека под юзера
 *
 * @param string $filename Имя файла
 * @param array $data Данные для чтения
 *
 * @return bool Записаны либо нет
 */
function SDH_callback_write_to_file ($filename, $data, $user_id = 0) {
    if ($user_id == 0) {
        $user_id = get_current_user_id();
    }

    $file_path = SDH_PATH . 'files_callback/' . $filename . $user_id . '.json'; // Укажите свой путь к папке с файлами

    $data['result'] = 'ok';

    $data = json_encode($data, JSON_PRETTY_PRINT);

    if (file_put_contents($file_path, $data)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Функция для чтения коллбека под юзера
 *
 * @param string $filename Имя файла
 * @param bool прочитал или нет
 *
 */
function SDH_callback_read_file ($filename, $user_id = 0) {
    $file_path = SDH_PATH . 'files_callback/' . $filename . get_current_user_id() . '.json';

    $data = json_decode (file_get_contents ($file_path), true);

    if ($data['result'] == 'ok') {
        return $data;
    } else {
        return false;
    }
}

/**
 * Функция для удаления коллбека под юзера
 *
 * @param string $filename Имя файла
 * @param bool удалил или нет
 *
 */
function SDH_callback_delete_file ($filename) {
    $file_path = SDH_PATH . 'files_callback/' . $filename . get_current_user_id() . '.json';

    if (file_exists($file_path)) {
        unlink($file_path);
        return true;
    } else {
        return false;
    }
}


/**
 * Функция для лайка комментария
 *
 * @param int $comment_id ИД комменатрия
 * @param int $user_id ИД пользователя
 *
 */
function like_comment ($comment_id, $user_id) {
    // Получение текущих лайков
    $likes = get_comment_meta($comment_id, 'sdh_likes', true);

    // Если мета-поле не существует, создаем его и инициализируем пустым массивом
    if (empty($likes) || !is_array($likes)) {
        $likes = array();
    }

    // Проверяем, поставил ли пользователь уже лайк
    if (!in_array($user_id, $likes)) {
        // Добавляем ID пользователя в массив лайкнувших
        $likes[] = $user_id;

        // Обновляем мета-поле
        update_comment_meta($comment_id, 'sdh_likes', $likes);
        return true;
    } else {
        return false;
    }
}


/**
 * Функция для удаления лайка с комментария
 *
 * @param int $comment_id ИД комменатрия
 * @param int $user_id ИД пользователя
 *
 */
function unlike_comment($comment_id, $user_id) {
    // Получение текущих лайков
    $likes = get_comment_meta($comment_id, 'sdh_likes', true);

    // Если мета-поле существует и является массивом
    if (is_array($likes)) {
        // Удаление ID пользователя из массива лайкнутых
        $likes = array_diff($likes, array($user_id));

        // Обновление мета-поля комментария
        update_comment_meta($comment_id, 'sdh_likes', $likes);
    }
}

/**
 * Функция для удаления всех лайков с комментария
 *
 * @param int $comment_id ИД комменатрия
 *
 */
function delete_comment_likes ($comment_id) {
    // Получаем текущие лайки комментария
    $likes = get_comment_meta($comment_id, 'sdh_likes', true);

    // Проверяем, существуют ли лайки
    if (is_array($likes)) {
        // Удаляем мета-поле лайков
        delete_comment_meta($comment_id, 'sdh_likes');
    }
}


