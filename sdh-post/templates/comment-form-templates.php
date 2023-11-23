<?php
$avatar_url = get_avatar_url(get_current_user_id(), array('size' => 42));

if (is_user_logged_in()) : ?>
<div class="card-footer py-3 border-0">
    <div class="d-flex flex-start w-100">
        <img class="rounded-circle shadow-1-strong me-3" src="<?=$avatar_url?>" alt="avatar" width="40" height="40">
        <div class="form-outline w-100">
            <input id="post_id" value="<?=get_the_ID()?>" hidden>
            <input id="replay_id_val" value="0" hidden>
            <textarea class="form-control" id="comment-text" rows="4" placeholder="<?=esc_html__('Add your comment', 'sdh-post')?>"></textarea>
        </div>
    </div>
    <div class="float-end mt-2 pt-1">
        <button id="submit-comment" type="button" class="btn btn-primary btn-sm"><i class="fa fa-pencil fa-fw"></i> <?=esc_html__('Add', 'sdh-post')?></button>
    </div>
</div>
<?php else : ?>
<br>
    <p style="text-align: center"><?= esc_html__('Please log in to leave a comment', 'sdh-post') ?></p>
<?php endif; ?>