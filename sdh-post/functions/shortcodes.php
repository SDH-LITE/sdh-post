<?php

add_shortcode( 'sdh-posts', 'sdh_posts_shortcode' );

function sdh_posts_shortcode ($atts) { // функция шорткода вывода постов с меткой sdh
    wp_enqueue_script('sdh-async', SDH_URL . 'src/js/load_post.js');
    wp_enqueue_style('sdh-style-blog', SDH_URL . 'src/css/blog.css');

    wp_enqueue_script( 'sdh-async' );
    wp_localize_script('sdh-async', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

    $atts = shortcode_atts(array(
        'category' => 'sdh', // Значение по умолчанию для атрибута "category"
    ), $atts);

    $args = array(
        'category_name' => $atts['category'],
        'posts_per_page' => 5, // -1 чтобы вывести все посты, установите желаемое количество
    );

    $query = new WP_Query($args);

    // Проверка наличия постов
    if ($query->have_posts()) {
        $posts_output = array(); // Создаем массив для хранения информации о постах

        while ($query->have_posts()) {
            $query->the_post();
            $post_author_id = get_the_author_meta('ID');

            $data_send = [
                'post_author_id' => get_the_author_meta('ID'),
                'post_author_avatar_url' => get_avatar_url($post_author_id, array('size' => 22)),
                'post_title' => '«'.get_the_title().'»',
                'post_link' => get_permalink(),
                'post_date' => get_the_date(),
                'post_author' => get_the_author(),
                'post_image' => get_the_post_thumbnail_url(get_the_ID(), 'large'),
                'post_excerpt' => get_the_excerpt(),
                'comments_count' => get_comments_number(),
                'author_url' => get_author_posts_url($post_author_id),
                'count_like' => get_like_count(get_the_ID()),
                'post_tags' => get_the_tags(),
            ];

            $posts_output[] = bodyWrapping ('templates/blog-post.php', $data_send);
            $data_send = []; // обнуляем
        }

        $output = '<div class="row justify-content-center">';
        $output .= implode('', $posts_output); // Объединяем информацию о постах в одну строку
        $output .= '</div>';

        return $output;
    }

    return ''; // Возвращаем пустую строку, если нет постов.
}
