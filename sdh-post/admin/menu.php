<?php
function sdh_plugin_menu () {
    // Добавляем страницу в меню
    add_menu_page(
        'SDH Plugin',   // Заголовок страницы
        'SDH Plugin',   // Текст в меню
        'manage_options', // Роль пользователя, которая может видеть эту страницу
        'sdh-plugin-page', // Уникальный идентификатор страницы
        'sdh_plugin_page_content', // Функция, которая будет вызвана для отображения содержимого страницы
        'dashicons-admin-plugins', // Иконка в меню (может быть заменена на URL изображения)
    );

    /*
    // Добавляем подменю
    add_submenu_page(
        'sdh-plugin-page', // Родительская страница (уникальный идентификатор)
        'Дополнения SDH-Post',    // Заголовок подменю
        'Аддоны',    // Текст в меню подменю
        'manage_options',  // Роль пользователя
        'sdh-addons-page', // Уникальный идентификатор подменю
        'sdh_submenu_addons_content' // Функция для отображения содержимого подменю
    );*/

    // Добавляем подменю
    /*
    add_submenu_page(
        'sdh-plugin-page', // Родительская страница (уникальный идентификатор)
        'Настройки SDH-Post',    // Заголовок подменю
        'Настройки',    // Текст в меню подменю
        'manage_options',  // Роль пользователя
        'sdh-settings-page', // Уникальный идентификатор подменю
        'sdh_submenu_settings_content' // Функция для отображения содержимого подменю
    );*/
}

function sdh_plugin_page_content () {
    sdh_submenu_addons_content ();

    /*
    enqueue_menu ();
    wp_enqueue_script ('sdh-materialize', SDH_URL . 'src/js/admin-menu.js');
    include SDH_PATH . 'templates/admin/main.php';
    */
}

function sdh_submenu_addons_content () {
    enqueue_menu ();
    wp_enqueue_script ('sdh-materialize', SDH_URL . 'src/js/addons.js');
    include SDH_PATH . 'templates/admin/addons.php';
}

function sdh_submenu_settings_content () {
    enqueue_menu ();
    include SDH_PATH . 'templates/admin/settings.php';
}

function enqueue_menu () {
    wp_enqueue_script ('materialize', SDH_URL . 'assets/materialize.min.js');
    wp_enqueue_style  ('materialize', SDH_URL . 'assets/materialize.min.css');
}

// Добавляем хук для добавления страницы в меню
add_action('admin_menu', 'sdh_plugin_menu');