<?php

function fk_get_payment_url ($amount, $order_id, $other = [], $currency = 'RUB') {
    $merchant_id = get_option('freekassa_merchant_id');
    $secret_word = get_option('freekassa_secret_key');

    $sign = md5($merchant_id . ':' . $amount . ':' . $secret_word . ':' . $currency . ':' . $order_id);

    $url = 'https://pay.freekassa.ru/?';
    $url .= 'm=' . $merchant_id;
    $url .= '&oa=' . $amount;
    $url .= '&o=' . $order_id;
    $url .= '&s=' . $sign;
    $url .= '&currency=' . $currency;
    $url .= '&i=1, 4, 6, 7, 8, 9, 10, 12, 13';
    $url .= '&lang=ru';
    $url .= '&us_id=' . get_current_user_id();
    $url .= '&us_postId=' . $other['post_id'];

    return esc_url ($url);
}

add_shortcode( 'fk-handle', 'handle_fk' );

function handle_fk () {
    $secret_word = get_option('freekassa_secret_key2');
    $sign = md5($_REQUEST['MERCHANT_ID'].':'.$_REQUEST['AMOUNT'].':'.$secret_word.':'.$_REQUEST['MERCHANT_ORDER_ID']);

    if ($sign == $_REQUEST['SIGN']) {
        $post_id = $_REQUEST['us_postId'];

        $product_file_urls = get_post_meta($post_id, '_product_file_url', true);
        $product_prices = get_post_meta($post_id, '_product_price', true);

        $user_id = $_REQUEST['us_id'];
        $AMOUNT = $_REQUEST['AMOUNT'];

        if ($AMOUNT >= $product_prices) {
            $haveFile = sdh_is_file_name_exists('sdh_order_' . $post_id . '_by_' . $user_id);

            if (!$haveFile) {
                $new_name = 'sdh_order_' . $post_id . '_by_' . $user_id . "_" . time();
                $temp_file_url = sdh_download_file($product_file_urls, false, $new_name);
            } else {
                $temp_file_url = $haveFile;
            }

            $response = array(
                'html' => bodyWrapping ('templates/buy/buy-confirm.php', ['link_download' => $temp_file_url]),
            );

            SDH_callback_write_to_file ('PaymentsCheck', $response, $user_id);
        }


        wp_die ("YES");
        die ("YES");
    }
}

do_action ('SDH_add_payment', 'Freekassa', 'fk_get_payment_url');