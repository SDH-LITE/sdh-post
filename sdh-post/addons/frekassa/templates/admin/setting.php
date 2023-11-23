<div class="wrap">
    <h2><?php echo get_admin_page_title() ?></h2>

    <form method="post" action="options.php">
        <?php settings_fields('freekassa_options_group'); ?>
        <?php do_settings_sections('freekassa-settings'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Merchant ID (ИД Магазина)</th>
                <td><input type="text" autocomplete="off" name="freekassa_merchant_id" value="<?php echo esc_attr(get_option('freekassa_merchant_id')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Secret Key (Секретное слово 1)</th>
                <td><input type="password" autocomplete="off" name="freekassa_secret_key" value="<?php echo esc_attr(get_option('freekassa_secret_key')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Secret Key2 (Секретное слово 2)</th>
                <td><input type="password" autocomplete="off" name="freekassa_secret_key2" value="<?php echo esc_attr(get_option('freekassa_secret_key2')); ?>" /></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>

    <span>
        <ol>
            <li>
                <p>
                    <strong>Регистрация в Free-Kassa:</strong>
                </p>
                <ul>
                    <li>Перейдите на <a href="https://www.free-kassa.ru/" target="_new">официальный сайт Free-Kassa</a> и зарегистрируйтесь.</li>
                    <li>Получите идентификатор магазина и секретный ключ из раздела "Мои магазины" на сайте Free-Kassa.</li>
                </ul>
            </li>
        </ol>
    </span>
</div>