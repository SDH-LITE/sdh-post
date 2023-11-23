<?php

// Регистрация опций
function fk_register_freekassa_options () {
    register_setting ('freekassa_options_group', 'freekassa_merchant_id');
    register_setting ('freekassa_options_group', 'freekassa_secret_key');
    register_setting ('freekassa_options_group', 'freekassa_secret_key2');
}

add_action('admin_init', 'fk_register_freekassa_options');

// Добавление страницы настроек в меню
function fk_add_freekassa_settings_page() {
    add_menu_page(
        'Freekassa Settings',
        'Freekassa',
        'manage_options',
        'freekassa-settings', // Уникальный идентификатор страницы
        'fk_freekassa_settings_page', // Функция для вывода содержимого страницы
    );
}

add_action('admin_menu', 'fk_add_freekassa_settings_page');

// Функция для вывода содержимого страницы настроек
function fk_freekassa_settings_page() {
    require_once FK_PATH . 'templates/admin/setting.php';
}
