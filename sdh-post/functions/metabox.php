<?php
// Добавление метабокса
function digital_product_meta_box() {
    add_meta_box(
        'digital-product-meta-box',
        'Цифровой товар',
        'display_digital_product_meta_box',
        'post', // Замените 'post' на тип вашего контента
        'normal',
        'high'
    );
}

// Отображение метабокса
function display_digital_product_meta_box($post) {
    wp_nonce_field('digital_product_nonce', 'digital_product_nonce');
    $product_price = get_post_meta($post->ID, '_product_price', true);
    $old_product_price = get_post_meta($post->ID, '_old_product_price', true);
    $product_currency = get_post_meta($post->ID, '_product_currency', true);
    $product_link = get_post_meta($post->ID, '_product_link', true);
    $product_file_url = get_post_meta($post->ID, '_product_file_url', true);

    $current_language = get_locale();
    $currency_options = ($current_language === 'ru_RU') ? array('USD' => 'USD', 'RUB' => 'RUB') : array('USD' => 'USD', 'RUB' => 'RUB');
    ?>
    <div id="digital_product_fields" class="postbox">
        <div>
            <div>
                <label for="old_product_price"><?=esc_html__('Old product price', 'sdh-post')?>:</label>
                <input type="number" id="old_product_price" name="old_product_price" value="<?php echo esc_attr($old_product_price); ?>" class="regular-text" />
            </div>
            <br>
            <div>
                <label for="product_price"><?=esc_html__('Product price', 'sdh-post')?>:</label>
                <input type="number" id="product_price" name="product_price" value="<?php echo esc_attr($product_price); ?>" class="regular-text" />


                <select id="product_currency" name="product_currency">
                    <?php
                    foreach ($currency_options as $currency_code => $currency_name) {
                        ?>
                        <option value="<?php echo esc_attr($currency_code); ?>" <?php selected($product_currency, $currency_code); ?>><?php echo esc_html($currency_name); ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
            <br>
            <div>
                <label for="product_file"><?=esc_html__('Product File', 'sdh-post')?>:</label>
                <input type="text" id="product_file_url" name="product_file_url" value="<?php echo esc_url($product_file_url); ?>" readonly class="regular-text" />
                <button id="upload_product_file" class="button">Выбрать файл</button>
            </div>
            <br> <br>
            <p class="description"><?=esc_html__('Maximum file size:', 'sdh-post')?> <?php echo size_format(wp_max_upload_size()); ?></p>
        </div>
    </div>
    <script>
        jQuery(document).ready(function($) {
            $('#is_digital_product').change(function() {
                if (this.checked) {
                    $('#digital_product_fields').show();
                } else {
                    $('#digital_product_fields').hide();
                }
            });

            var frame;
            $('#upload_product_file').on('click', function(e) {
                e.preventDefault();

                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: 'Выбрать файл товара',
                    button: {
                        text: 'Выбрать'
                    },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#product_file_url').val(attachment.url);
                });

                frame.open();
            });
        });
    </script>
    <?php
}


function save_digital_product_meta($post_id) {
    if (isset($_POST['product_price'])) {
        update_post_meta($post_id, '_product_price', sanitize_text_field($_POST['product_price']));
    }

    if (isset($_POST['old_product_price'])) {
        update_post_meta($post_id, '_old_product_price', sanitize_text_field($_POST['old_product_price']));
    }

    if (isset($_POST['product_currency'])) {
        update_post_meta($post_id, '_product_currency', sanitize_text_field($_POST['product_currency']));
    }

    if (isset($_POST['product_link'])) {
        update_post_meta($post_id, '_product_link', esc_url($_POST['product_link']));
    }

    if (isset($_POST['product_file_url'])) {
        update_post_meta($post_id, '_product_file_url', esc_url($_POST['product_file_url']));
    }
}


// Добавление метабокса на страницу создания/редактирования поста
add_action('add_meta_boxes', 'digital_product_meta_box');
// Сохранение значений метаполей при сохранении поста
add_action('save_post', 'save_digital_product_meta');