<div id="sdh_comment_<?=$C_I?>" class="card-body">
    <div class="d-flex flex-start">
        <img class="rounded-circle shadow-1-strong me-3" src="<?=$user_avatar?>" alt="avatar" width="60" height="60">
        <div>
            <h6 class="fw-bold mb-1"><a target="_blank" href="<?=$author_url?>"><?=$user_nick?> </a></h6>
            <div class="d-flex align-items-center mb-3">
                <p class="mb-0">
                    <?=__('just now', 'sdh-post')?> <span class="badge bg-success"><?=__('Approved', 'sdh-post')?></span>
                </p>
                <?php if (SDH_get_role_level() >= 3 OR get_current_user_id() == $author_id) { ?>
                <a id="e_<?=$C_I?>" class="link-muted edit"><i class="fas fa-pencil-alt ms-2"></i></a>
                <a id="<?=$C_I?>" class="text-danger remove"><i class="fa fa-trash ms-2"></i></a>
                <?php } ?>
                <a id="like_<?=$C_I?>" class="link-danger like-comments"><i class="fas fa-heart ms-2"></i><cn id="l_<?=$C_I?>"> 0</cn></a>
            </div>
            <p id="text_comment_<?=$C_I?>" class="mb-0">
                <?=$content?>
            </p>
        </div>
    </div>
</div>

<hr id="h_<?=$C_I?>" class="my-0" style="height: 1px;" />