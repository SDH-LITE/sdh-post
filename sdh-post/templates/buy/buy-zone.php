<?php
$haveFile = sdh_is_file_name_exists('sdh_order_' . $post_id . '_by_' . get_current_user_id());
?>
<div class="purchase-block">
    <div class="buy-btn-zone">
        <span class="product-old-price">
            <?=$old_product_prices . $product_currency?>
        </span>
        <span class="price">
            <?=$product_prices . $product_currency?>
        </span>
        <button id="buyOrderBtn" type="button" data-toggle="modal" data-target="#buyOrder" class="buy-button">
            <i class="fas fa-shopping-cart"></i>
            <?=($haveFile == '') ? esc_html__('Buy', 'sdh-post') : esc_html__('Download', 'sdh-post')?>
        </button>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="buyOrder" tabindex="-1" role="dialog" aria-labelledby="buyOrderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 style="color: black;" class="modal-title" id="exampleModalLabel"><?php the_title()?></h5>
                <button id="closeModal" type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if ($haveFile != '') { ?>
                <div id="buyZone" style="border-radius: 7px;" class="card">
                      <div class="card-header text-center">
                          <?=esc_html__('The product is purchased', 'sdh-post')?>
                      </div>
                      <div class="card-body text-center">
                          <a target="_blank" href="<?=$haveFile?>">
                              <button type="button" class="btn btn-outline-primary">
                                  <?=esc_html__('Your download link', 'sdh-post')?>
                              </button>
                          </a>
                      </div>
                </div>
                  <?php } elseif (is_user_logged_in()) { ?>
                    <!-- Код для авторизованного пользователя -->
                    <?php if ($product_prices <= 0) { ?>
                    <div id="buyZone" style="border-radius: 7px;" class="card">
                        <div class="card-header text-center">
                            <?=esc_html__('Product registration', 'sdh-post')?>
                        </div>
                        <div class="card-body text-center">
                            <blockquote class="blockquote mb-0">
                                <p><?=esc_html__('After payment you will receive a download link', 'sdh-post')?></p>
                                <footer class="blockquote-footer"><?=esc_html__('Price', 'sdh-post')?>: <cite title="Source Title"><?=$product_prices . $product_currency?></cite></footer>
                            </blockquote>
                        </div>
                    </div>
                    <?php } else { ?>
                        <div id="buyZone" style="border-radius: 7px;" class="card">
                            <div class="card-header text-center">
                                <?=esc_html__('Product registration', 'sdh-post')?>
                            </div>
                            <div class="card-body text-center">
                                <blockquote class="blockquote mb-0">
                                    <p><?=esc_html__('After payment you will receive a download link', 'sdh-post')?></p>
                                    <footer class="blockquote-footer"><?=esc_html__('Price', 'sdh-post')?>: <cite title="Source Title"><?=$product_prices . $product_currency?></cite></footer>
                                </blockquote>
                            </div>
                            <select id="PayMents" class="form-select" aria-label="Пример выбора по умолчанию">
                                <option selected>Выберите способ оплаты</option>
                                <?php
                                $payments = sdh_get_payment ();

                                foreach ($payments as $payment) {
                                    echo '<option value="'.$payment.'">'.$payment.'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    <?php } ?>

                <?php } else { ?>
                    <p> Авторизируйтесь для покупки </p>
                <?php } ?>


            </div>
            <div class="modal-footer">
                <button id="closeModal" type="button" class="btn btn-secondary" data-dismiss="modal"><?=esc_html__('Close', 'sdh-post')?></button>
                <?php if ($haveFile == '') { ?>
                <button id="buyConfirm" type="button" class="btn btn-primary"><?=esc_html__('Buy', 'sdh-post')?></button>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
