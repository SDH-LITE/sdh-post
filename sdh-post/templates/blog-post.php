<div class="col-md-8">
    <!-- News Card -->
    <div class="card mb-4 border-0 rounded-my sdh">
        <a href="<?=$author_url?>">
            <div class="c-p" style="padding: 0.75rem 1.25rem;">
                <img src="<?=esc_url($post_author_avatar_url)?>" class="rounded-circle" style="width: 22px; height: 22px;" alt="Аватар автора">
                <strong><span class="ml-2"><?=esc_html($post_author)?></span>
                </strong>
                <time style="float: right;" class="time float-right"><i class="fas fa-clock"></i>
                    <?=human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ' . esc_html__('ago', 'sdh-post')?>
                </time>
            </div>
        </a>

        <div class="card-body c-p ">
            <div class="sdh-div">
                <?php
                if ($post_tags) {
                    echo '<div class="bd-example m-0 border-0">';
                    foreach ($post_tags as $tag) {
                        echo '<a href="' . esc_url(get_tag_link($tag->term_id)) . '">' . '<span class="badge bg-sdh">' . esc_html($tag->name) . '</span>' . '</a> ';
                    }
                    echo '</div>';
                }
                ?>

                <h4 style="color: black; margin-top: 15px;" class="card-title">
                    <a href="<?=esc_url($post_link)?>"><?=esc_html($post_title)?></a>
                </h4>

            </div>
            <p class="card-text text-xl"><?=$post_excerpt?></p>

            <? if ($post_image) { ?>
                <img src="<?=esc_url($post_image)?>" class="card-img-top mx-auto">
            <? } ?>

        </div>
        <div class="card-footer border-0 bg-transparent rounded-my">
            <div style="width: unset;"  class="row">
                <div class="col-md-6 ">
                    <div class="text-muted"><i class="far fa-thumbs-up text-danger"></i>  <?=esc_html__('Likes', 'sdh-post')?>: <?=$count_like?></div>
                    <div class="text-muted"><i class="far fa-comment text-info"></i>  <?=esc_html__('Comments', 'sdh-post')?>: <?=$comments_count?></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Repeat News Cards for other articles -->
</div>