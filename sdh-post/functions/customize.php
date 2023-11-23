<?php
#Вешаем фильтр
add_filter('template_include', 'customize_sdh_post_content');

/**
 * Функция кастомизации поста с рубрикой SDH /
 * The function of customizing a post with the SDH heading
 *
 * @return string template
 */
function customize_sdh_post_content ($content) {
    // Проверяем, что мы на странице одного поста и пост принадлежит рубрике "sdh"
    if (is_single() AND in_category('sdh')) {
        add_action('wp_enqueue_scripts', 'load_scripts');
        $custom_template = SDH_PATH . 'templates/single.php';
        return $custom_template;
    }

    return $content;
}

/**
 * Функция для подключения скриптов нужных для страницы поста /
 * Function for connecting scripts needed for the post page
 */
function load_scripts () {
    wp_enqueue_script('jquery');
    wp_enqueue_style('sdh-single', SDH_URL . 'src/css/single.css');
    wp_enqueue_script('sdh-single', SDH_URL . 'src/js/single.js');

    wp_enqueue_script( 'sdh-single' );
    wp_localize_script('sdh-single', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

    wp_enqueue_style('fancybox', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css');
    wp_enqueue_script('fancybox-js', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js');
    //
    // https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js
}


#Вешаем фильтр
add_filter('category_template', 'customize_sdh_category_content');

function customize_sdh_category_content ($content) {
    $current_category = get_queried_object();

    // Проверяем, является ли текущая страница категорией и имеет ли она URL-адрес /category/sdh/
    if ($current_category->slug === 'sdh') {
        $custom_template = SDH_PATH . 'templates/category.php';
        // Добавляем ваше измененное содержимое к оригинальному контенту
        return $custom_template;
    }

    // Возвращаем обновленный контент
    return $content;
}
