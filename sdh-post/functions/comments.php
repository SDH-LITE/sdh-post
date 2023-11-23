<?php
/**
 * Функция шаблона комментариев /
 * Comment Template function
 *
 * @var $post_ID integer post id
 */
function SDH_comments_template ($post_ID) {
    include SDH_PATH . 'templates/comments-templates.php';
}

/**
 * Функция шаблона формы комментариев /
 * Function of the Comment Form template
 */
function SDH_comment_form_template () {
    include SDH_PATH . 'templates/comment-form-templates.php';
}

remove_all_filters('comment_flood_filter');
add_filter('comment_flood_filter', 'custom_throttle_comment_flood');

function custom_throttle_comment_flood() {
    return false;
}

add_action('sdh_comments_hook', 'SDH_comments_template');
add_action('sdh_comments_form_hook', 'SDH_comment_form_template');