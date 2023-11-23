<?php
get_header(); // Включите шапку сайта
$author_id = get_userdata(get_post_field('post_author', get_the_ID()))->ID;


$post_id = get_the_ID(); // Получаем ID текущей записи
$user_id = get_current_user_id(); // Получаем ID текущего пользователя

$like_count = get_like_count($post_id);

$post = get_post();

// Проверяем, является ли текущая страница отдельной записью (постом)
if (is_single() && $post) {
    ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": "<?php echo esc_html($post->post_title); ?>",
        "image": "<?php echo get_the_post_thumbnail_url($post, 'full'); ?>",
        "datePublished": "<?php echo get_the_date('c', $post); ?>",
        "dateModified": "<?php echo get_the_modified_date('c', $post); ?>",
        "author": {
            "@type": "Person",
            "name": "<?php echo get_the_author_meta('display_name', $author_id); ?>"
        },
        "publisher": {
            "@type": "Organization",
            "name": "<?php bloginfo('name'); ?>",
            "logo": {
                "@type": "ImageObject",
                "url": "<?php echo esc_url(get_site_icon_url()); ?>"
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "<?php echo esc_url(get_permalink($post)); ?>"
        }
    }
    </script>
    <?php
}

$product_file_urls = get_post_meta ($post_id, '_product_file_url', true);

if ($product_file_urls != '') { // не делаем лишние запросы, если нет ссылки на скачивание
    $old_product_prices = get_post_meta($post_id, '_old_product_price', true);
    $product_prices = get_post_meta($post_id, '_product_price', true);
    $product_currency = get_post_meta($post_id, '_product_currency', true);

    $product_currency = ($product_currency == 'RUB') ? '₽' : '$';
}
?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if ($product_file_urls != '') { ?>
                <div class="alert alert-info text-center" role="alert">
                    <?=esc_html__('The post represents a digital product', 'sdh-post')?>
                </div>
                <?php } ?>

                <div class="bg-white p-4">
                    <div class="sdh-div">

                        <div style="width: 100%;" class="content-header">
                            <div class="content-header__info">
                                <div class="content-header-author content-header-author--subsite content-header__item">
                                    <a href="<?php echo esc_url(get_author_posts_url($author_id)); ?>">
                                        <div class="content-header-author__avatar">
                                            <img class="andropov_image" style="background-color: transparent;" src="<?php echo esc_url(get_avatar_url($author_id, array('size' => 64))); ?>">
                                        </div>
                                        <div class="content-header-author__name">
                                            <?php echo get_the_author_meta('display_name', $author_id); ?>
                                        </div>
                                    </a>
                                </div>
                                <div class="content-header__item content-header-number">
                                    <span class="lm-hidden">
                                        <time class="time"><?php the_time('d M \в H:i'); ?></time>
                                    </span>
                                </div>
                                <div class="like-button <?= ($user_id <= 0) ? 'hidden' : '' ?>">
                                    <i class="far fa-heart nofon"></i> <!-- Сердце без заливки (по умолчанию) -->
                                    <i class="fas fa-heart" style="display: none; color: #e5545e;"></i> <!-- Сердце с заливкой (при нажатии) -->
                                    <span class="like-count"><?=$like_count?></span>
                                    <span style="display: none" class="postID"><?=$post_id?></span>
                                </div>
                            </div>
                        </div>

                        <?php
                        $post_tags = get_the_tags();
                        if ($post_tags) {
                            echo '<div class="bd-example m-0 border-0">';
                            foreach ($post_tags as $tag) {
                                echo '<a href="' . esc_url(get_tag_link($tag->term_id)) . '">' . '<span class="badge bg-primary">' . esc_html($tag->name) . '</span>' . '</a> ';
                            }
                            echo '</div>';
                        }
                        ?>

                        <h1 class="content-title">«<?php the_title()?>»</h1>

                        <?php
                        $post_image = get_the_post_thumbnail(get_the_ID(), 'large');

                        if (!empty($post_image)) {
                            echo '<div class="post-image">' . $post_image . '</div>';
                        }
                        ?>
                    </div>
                    <br>
                    <div class="sdh-div">
                        <?php
                        the_content();

                        echo ' <hr>';
                        if ($product_file_urls != '') require_once SDH_PATH . 'templates/buy/buy-zone.php';
                        ?>
                    </div>
                </div>
            </div>

            <?php if (comments_open() || get_comments_number()) do_action('sdh_comments_hook', $post_id); ?>

        </div>
    </div>

<?php get_footer(); ?>



