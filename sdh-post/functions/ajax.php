<?php
/**
 * Функция для обработки AJAX-запроса
 * action get_like (узнать и поставить лайк) (убрать)
 */

function get_like_callback () {
    $postID = intval($_POST['postID']);
    $user_id = get_current_user_id(); // Получите ID текущего пользователя
    $liked = get_post_meta($postID, '_liked_by_user_' . $user_id, true);

    if ($user_id > 0) {
        if ($liked) {
            // Уберите лайк, так как он уже существует
            delete_post_meta($postID, '_liked_by_user_' . $user_id);
            $response = array(
                'message' => 'Лайк удален',
                'liked' => false
            );
        } else {
            // Добавьте лайк, так как его нет
            add_post_meta($postID, '_liked_by_user_' . $user_id, true, true);
            $response = array(
                'message' => esc_html__('Like added', 'sdh-post'),
                'liked' => true
            );
        }
    } else {
        $response = array(
            'message' => 'Лайк добавить невозможно',
            'liked' => false
        );
    }

    wp_send_json($response); // Отправьте ответ в формате JSON
}

// хук для (не) / зарегистрированных пользователей
sdh_actions ('get_like', 'get_like_callback');


/**
 * Функция для обработки AJAX-запроса
 * action check_user_like (стоит лайк)
 */
function check_user_like_callback () {
    $postID = intval($_POST['postID']);
    $user_id = get_current_user_id(); // Получите ID текущего пользователя

    if ($user_id > 0) {
        $liked = false;

        // Проверьте, стоит ли лайк от пользователя на посте
        $like_key = "_liked_by_user_" . $user_id;
        $like_status = get_post_meta($postID, $like_key, true);

        if ($like_status) {
            $liked = true;
        }

        $response = array(
            'liked' => $liked
        );
    } else {
        $response = array(
            'liked' => 'false'
        );
    }

    wp_send_json($response); // Отправьте ответ в формате JSON
}

sdh_actions ('check_user_like', 'check_user_like_callback');

/**
 * Функция для обработки AJAX-запроса
 * action load_more_posts (добавление ассинхронной загрузки)
 */
function SDH_load_more_posts () {
    $posts_per_page = intval($_POST['posts_per_page']);
    $page = intval($_POST['page']);

    $args = array(
        'category_name' => 'sdh',
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_author_id = get_the_author_meta('ID');

            $data_send = [
                'post_author_id' => $post_author_id,
                'post_author_avatar_url' => get_avatar_url($post_author_id, array('size' => 22)),
                'post_title' => get_the_title(),
                'post_link' => get_permalink(),
                'post_date' => get_the_date(),
                'post_author' => get_the_author(),
                'post_image' => get_the_post_thumbnail_url(get_the_ID(), 'large'),
                'post_excerpt' => get_the_excerpt(),
                'comments_count' => get_comments_number(),
                'author_url' => get_author_posts_url ($post_author_id),
                'count_like ' => get_like_count (get_the_ID()),
            ];

            $posts_output[] = bodyWrapping ('templates/blog-post.php', $data_send);
            $data_send = []; // обнуляем на всякий случай
        }

        $output = implode('', $posts_output); // Объединяем информацию о постах в одну строку
        $output .= '</div>';

        $response = array(
            'post' => $output,
            'status' => true
        );
    } else {
        $response = array(
            'message' => esc_html__('Have you scrolled to the end', 'sdh-post'),
            'title' => esc_html__('System Notification', 'sdh-post'),
            'status' => false
        );
    }


    wp_send_json($response); // Отправьте ответ в формате JSON
}

sdh_actions ('load_more_posts', 'SDH_load_more_posts');

/**
 * Функция для обработки AJAX-запроса
 * action send_comment (отправляет комментарий)
 */
function SDH_send_comment() {
    $comment_text = sanitize_text_field($_POST['comment']);
    $post_id = absint($_POST['post_id']);

    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $user_info = get_userdata($user_id);

        $comment_data = array(
            'comment_post_ID' => $post_id,
            'comment_author' => $user_info->display_name,
            'comment_author_url' => get_author_posts_url($user_id),
            'comment_content' => $comment_text,
            'comment_type' => '',
            'comment_parent' => 0,
            'user_id' => $user_id,
            'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
            'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
            'comment_date' => current_time('mysql'),
            'comment_author_avatar' => get_avatar_url($user_id),
            'comment_author_email' => $user_info->user_email,
        );

        $comment_approved = wp_allow_comment($comment_data);

        if (is_wp_error($comment_approved)) {
            $response = array(
                'message' => $comment_approved->get_error_message(),
                'title' => esc_html__('System Notification', 'sdh-post'),
                'status' => false
            );
        } else {
            $comment_data['comment_approved'] = $comment_approved;
            $comment_id = wp_insert_comment($comment_data);

            if ($comment_id) {
                $data_send = [
                    'user_avatar' => get_avatar_url($user_id),
                    'user_nick' => $user_info->display_name,
                    'content' => $comment_text,
                    'author_url' => get_author_posts_url($user_id),
                    'C_I' => $comment_id,
                    'author_id' => $user_id,
                ];

                $response = array(
                    'title' => esc_html__('System Notification', 'sdh-post'),
                    'message' => esc_html__('Comment added', 'sdh-post'),
                    'comment_id' => $comment_id,
                    'status' => true,
                    'html' => bodyWrapping ('templates/comments/single-comment.php', $data_send)
                );
            } else {
                $response = array(
                    'message' => esc_html__('Adding error', 'sdh-post'),
                    'title' => esc_html__('System Notification', 'sdh-post'),
                    'status' => false,
                    'other' => $comment_id
                );
            }
        }

        wp_send_json($response); // Отправьте ответ в формате JSON
    }
}

sdh_actions ('send_comment', 'SDH_send_comment');


/**
 * Функция для обработки AJAX-запроса
 * action send_replay (отвечает на комментарий)
 */
function SDH_send_replay () {
    $comment_text = sanitize_text_field($_POST['comment']);
    $post_id = absint($_POST['post_id']);
    $replay_id = absint($_POST['replay_id']);

    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $user_info = get_userdata($user_id);

        $comment_data = array(
            'comment_post_ID' => $post_id,
            'comment_author' => $user_info->display_name,
            'comment_author_url' => get_author_posts_url($user_id),
            'comment_content' => $comment_text,
            'comment_type' => '',
            'comment_parent' => $replay_id,
            'user_id' => $user_id,
            'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
            'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
            'comment_date' => current_time('mysql'),
            'comment_author_avatar' => get_avatar_url($user_id),
            'comment_author_email' => $user_info->user_email,
        );

        $comment_approved = wp_allow_comment($comment_data);

        if (is_wp_error($comment_approved)) {
            $response = array(
                'message' => $comment_approved->get_error_message(),
                'title' => esc_html__('System Notification', 'sdh-post'),
                'status' => false
            );
        } else {
            $comment_data['comment_approved'] = $comment_approved;
            $comment_id = wp_insert_comment($comment_data);

            if ($comment_id) {

                $data_send = [
                    'user_avatar' => get_avatar_url($user_id),
                    'user_nick' => $user_info->display_name,
                    'content' => $comment_text,
                    'author_url' => get_author_posts_url($user_id),
                    'C_I' => $comment_id,
                    'is_replay' => ($replay_id != '') ? 1 : 0
                ];


                $response = array(
                    'title' => esc_html__('System Notification', 'sdh-post'),
                    'message' => esc_html__('Comment added', 'sdh-post'),
                    'comment_id' => $comment_id,
                    'status' => true,
                    'html' => bodyWrapping ('templates/comments/single-comment.php', $data_send),
                    'replay_id' => $replay_id
                );
            } else {
                $response = array(
                    'message' => esc_html__('Adding error', 'sdh-post'),
                    'title' => esc_html__('System Notification', 'sdh-post'),
                    'status' => false,
                    'other' => $comment_id
                );
            }
        }

        wp_send_json($response); // Отправьте ответ в формате JSON
    }
}

sdh_actions ('send_replay', 'SDH_send_replay');

/**
 * Функция для обработки AJAX-запроса
 * action del_comment (удаляет комментарий)
 * для прав редактора (3)
 */
function SDH_del_comment () {
    $comment_id = absint($_POST['comment_id']);
    $comment = get_comment($comment_id);

    if ($comment_id != null) {
        // Проверяем, имеет ли текущий пользователь права редактора
        if (SDH_get_role_level() >= 3 OR get_current_user_id() == $comment->user_id) {
            // Удаляем комментарий и его дочерние
            wp_delete_comment($comment_id, true);
            delete_comment_likes ($comment_id);

            $response = array(
                'message' => esc_html__('Comment and its children deleted', 'sdh-post'),
                'title'   => esc_html__('System Notification', 'sdh-post'),
                'status'  => true,
            );
        } else {
            $response = array(
                'message' => esc_html__('Insufficient user rights' , 'sdh-post'),
                'title'   => esc_html__('System Notification', 'sdh-post'),
                'status'  => false,
            );
        }
    }

    wp_send_json($response); // Отправьте ответ в формате JSON
}


sdh_actions ('del_comment', 'SDH_del_comment');

/**
 * Функция для обработки AJAX-запроса
 * action edit_comment (редактирует комментарий)
 * для прав редактора (3)
 */
function SDH_edit_comment () {
    $comment_id = absint($_POST['comment_id']);
    $text = sanitize_text_field ($_POST['text']);

    $comment = get_comment($comment_id);

    if ($comment_id != null) {
        // Проверяем, имеет ли текущий пользователь права редактора
        if (SDH_get_role_level() >= 3 OR get_current_user_id() == $comment->user_id) {

            $comment_data = array(
                'comment_ID' => $comment_id,
                'comment_content' => $text,
            );

            wp_update_comment($comment_data);

            $response = array(
                'message' => esc_html__('The comment has been changed', 'sdh-post'),
                'title'   => esc_html__('System Notification', 'sdh-post'),
                'status'  => true,
            );
        } else {
            $response = array(
                'message' => esc_html__('Insufficient user rights' , 'sdh-post'),
                'title'   => esc_html__('System Notification', 'sdh-post'),
                'status'  => false,
            );
        }
    }

    wp_send_json($response); // Отправьте ответ в формате JSON
}


sdh_actions ('edit_comment', 'SDH_edit_comment');

/**
 * Функция для обработки AJAX-запроса
 * action like_comment (лайкает комментарий)
 */
function SDH_like_comment () {
    $comment_id = absint($_POST['comment_id']);

    if ($comment_id != null) {
        if (is_user_logged_in()) {
            $set_like = like_comment ($comment_id, get_current_user_id());

            if (!$set_like) {
                $result = false;
                unlike_comment ($comment_id, get_current_user_id());

                $response = array(
                    'message' => esc_html__('Like removed', 'sdh-post'),
                    'title' => esc_html__('System Notification', 'sdh-post'),
                    'result' => $result,
                    'status' => true,
                    'other' => $set_like
                );
            } else {
                $result = true;

                $response = array(
                    'message' => esc_html__('Like is set', 'sdh-post'),
                    'title' => esc_html__('System Notification', 'sdh-post'),
                    'result' => $result,
                    'status' => true,
                );
            }




        } else {
            $response = array(
                'message' => esc_html__('To like, you need to log in', 'sdh-post'),
                'title' => esc_html__('System Notification', 'sdh-post'),
                'status' => false,
            );
        }

    }

    wp_send_json ($response); // Отправьте ответ в формате JSON
}

sdh_actions ('like_comment', 'SDH_like_comment');

/**
 * Функция для обработки AJAX-запроса
 * action buy (Покупка)
 */
function SDH_buy () {
    $order_id = absint ($_POST['order_id']);
    $order_title = get_the_title($order_id);
    $payment_method = sanitize_text_field ($_POST['payment_method']);

    if (in_array($payment_method, sdh_get_payment ())) {
        $payment = true;
    }

    if ($payment) {
        if ($order_id != null) {
            if (is_user_logged_in()) {

                $product_file_urls = get_post_meta($order_id, '_product_file_url', true);
                $product_prices = get_post_meta($order_id, '_product_price', true);
                $product_currency = get_post_meta($order_id, '_product_currency', true);

                if ($product_prices <= 0) {
                    $haveFile = sdh_is_file_name_exists('sdh_order_' . $order_id . '_by_' . get_current_user_id());

                    if (!$haveFile) {
                        $new_name = 'sdh_order_' . $order_id . '_by_' . get_current_user_id() . "_" . time();
                        $temp_file_url = sdh_download_file($product_file_urls, false, $new_name);
                    } else {
                        $temp_file_url = $haveFile;
                    }

                    $response = array(
                        'status' => true,
                        'html' => bodyWrapping('templates/buy/buy-confirm.php', ['link_download' => $temp_file_url]),
                    );
                } else {;
                    $other = ['post_id' => $order_id];
                    $payment_link = SDH_call_payment ($payment_method, sdh_get_payment_function()[$payment_method], $product_prices, time(), $other);

                    $response = array(
                        'status' => true,
                        'html' => bodyWrapping('templates/buy/buy-pay.php', ['payment_link' => $payment_link]),
                    );
                }

            } else {
                $response = array(
                    'status' => false,
                    'message' => esc_html__('To pay, you need to log in' , 'sdh-post'),
                    'title'   => esc_html__('System Notification', 'sdh-post'),
                );
            }
        }
    } else {
        if (is_user_logged_in()) {
            $response = array(
                'status' => false,
                'message' => esc_html__('The payment system is not registered', 'sdh-post'),
                'title' => esc_html__('System Notification', 'sdh-post'),
            );
        } else {
            $response = array(
                'status' => false,
                'message' => esc_html__('To pay, you need to log in' , 'sdh-post'),
                'title'   => esc_html__('System Notification', 'sdh-post'),
            );
        }
    }

    wp_send_json ($response); // Отправьте ответ в формате JSON
}


sdh_actions ('buy', 'SDH_buy');

/**
 * Функция для обработки AJAX-запроса
 * action addon_set (активация / деактивация аддонов)
 */
function SDH_addon_set () {
    $addon_name = sanitize_text_field($_POST['addon_name']);
    $status_set = $_POST['status_set'];

    if ($addon_name != null) {
        if ($status_set == 'true') {
            sdh_activate_addon ($addon_name);

            $response = array(
                'status' => true,
                'method' => 'activate',
                'set' => $status_set
            );
        } else {
            sdh_deactivate_addon ($addon_name);

            $response = array(
                'status' => true,
                'method' => 'deactivate',
                'set' => $status_set
            );
        }
    }

    wp_send_json($response); // Отправьте ответ в формате JSON
}


sdh_actions ('addon_set', 'SDH_addon_set');

/**
 * Функция для обработки AJAX-запроса
 * action translate (переводит для js)
 */
function SDH_translate () {
    $text = sanitize_text_field($_POST['text']);

    if ($text != null) {
        $response = array(
            'value' => esc_html__($text, 'sdh-post'),
        );
    }

    wp_send_json($response); // Отправьте ответ в формате JSON
}


sdh_actions ('translate', 'SDH_translate');
