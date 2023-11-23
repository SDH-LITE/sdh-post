<?php
$comments_number = get_comments_number ($post_ID);

$comments = get_comments(array(
    'post_id' => $post_ID,
    'status' => 'approve', // Только подтвержденные комментарии
    'order' => 'ASC', // Или 'DESC' в зависимости от ваших предпочтений
    'parent' => 0, // Только корневые комментарии
));


if ($comments_number > 0) {
?>

<section>
    <div class="container ">
        <div class="row d-flex justify-content-center">
            <div class="col-md-12 col-lg-10">
                <div class="card text-dark">
                    <?= do_action('sdh_comments_form_hook', $post_id); ?>
                    <div class="card-body p-4 com">
                        <h4 class="mb-0"><?=__('Recent comments', 'sdh-post')?></h4>
                        <p class="fw-light mb-4 pb-2"><?=__('Latest Comments section by users', 'sdh-post')?></p>

                        <?php
                        foreach ($comments as $comment) {
                            $avatar_url = get_avatar_url($comment->comment_author_email, array('size' => 42));
                            $author_url = get_author_posts_url($comment->user_id);

                            $human_readable_date = human_time_diff (strtotime ($comment->comment_date), current_time('timestamp')) . ' '. esc_html__('ago', 'sdh-post');
                        ?>

                        <div id="sdh_comment_<?=$comment->comment_ID?>" class="card-body">
                            <div class="d-flex flex-start">
                                <img class="rounded-circle shadow-1-strong me-3"
                                     src="<?=$avatar_url?>" alt="avatar" width="60"
                                     height="60" />
                                <div>
                                    <h6 class="fw-bold mb-1"><a target="_blank" href="<?=$author_url?>"><?php echo esc_html($comment->comment_author); ?></a></h6>
                                    <div class="d-flex align-items-center mb-3">
                                        <p class="mb-0">
                                            <?php echo esc_html ($human_readable_date); ?>
                                            <span class="badge bg-success"><?=__('Approved', 'sdh-post')?></span>
                                        </p>
                                        <?php
                                        $current_likes = count(get_comment_meta($comment->comment_ID, 'sdh_likes', true));


                                        if (SDH_get_role_level() >= 3 OR get_current_user_id() == $comment->user_id) { ?>
                                        <a id="e_<?=$comment->comment_ID?>" class="link-muted edit"><i class="fas fa-pencil-alt ms-2"></i></a>
                                        <a id="<?=$comment->comment_ID?>" class="text-danger remove"><i class="fa fa-trash ms-2"></i></a>
                                        <?php } ?>
                                        <a id="like_<?=$comment->comment_ID?>" class="link-danger like-comments"><i class="fas fa-heart ms-2"></i> <cn id="l_<?=$comment->comment_ID?>"><?=$current_likes?></cn></a>
                                    </div>
                                    <p id="text_comment_<?=$comment->comment_ID?>" class="mb-0">
                                        <?php echo esc_html($comment->comment_content); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr id="h_<?=$comment->comment_ID?>" class="my-0" style="height: 1px;" />
                        <?php } ?>


                </div>
            </div>
        </div>
    </div>
</section>
<?php } else {
    if (is_user_logged_in()) {
    ?>
    <section>
        <div class="container ">
            <div class="row d-flex justify-content-center">
                <div class="col-md-12 col-lg-10">
                    <div class="card text-dark">
                        <?php
                        do_action('sdh_comments_form_hook', $post_id);
                        ?>
                        <div class="card-body p-4 com">
                            <h4 class="mb-0"><?=__('Recent comments', 'sdh-post')?></h4>
                            <p class="fw-light mb-4 pb-2"><?=__('Latest Comments section by users', 'sdh-post')?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php } } ?>
