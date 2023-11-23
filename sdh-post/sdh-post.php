<?php
/*
  Plugin Name: SDH-Post
  Plugin URI: https://sdh-lite.ru/
  Description: Plugin for blogging, sales, style, management.
  Version: 1.0.0
  Author: ViPlayer
  Author URI: https://sdh-lite.ru/account/?user=1
  Text Domain: sdh-post
  Domain Path: /languages
  Requires PHP: 7.4.0
  License: GPL-2.0+
  License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
  GitHub Plugin URI: https://github.com/SDH-LITE/sdh-post
 */


final class SDH_Post {
    public $version = '1.0.0';
    protected static $_instance = null;
    public $need_update = false;

    private $registered_gateways = array();
    private $registered_gateways_functions = array();

    public static function instance () {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct () {
        $this->haveWP ();          // Проверяем установлен вообще ли ВП
        $this->define_constants(); // Определяем константы.
        $this->includes();         // Подключаем все нужные файлы с функциями и классами
        $this->init_hooks ();      // Вешаем хуки
        $this->addons_include ();  // Подключаем ад-доны
    }

    public function haveWP () {
        if ( !defined( 'ABSPATH' ) ) {
            http_response_code( 404 );
            die();
        }
    }

    public function includes () {
        add_action('plugins_loaded', array( $this, 'load_textdomain' ), 10);

        require_once 'sdh-functions.php';

        require_once 'functions/customize.php';
        require_once 'functions/ajax.php';
        require_once 'functions/comments.php';
        require_once 'functions/metabox.php';
        require_once 'functions/shortcodes.php';
        require_once 'functions/callback-handler.php';

        require_once 'admin/menu.php';

        if (!is_admin()) {
            // Здесь подключим стили
            add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'), 99); // подключаем необходимые файлы с приоритетом 99
            wp_enqueue_style('bootstrap', SDH_URL . 'assets/bootstrap.min.css'); // тут нам приоритет не нужен
        }
        /*
         * Здесь подключите остальные файлы, которые нужны для вашего плагина.
         * Например, файлы с функциями и классами.
         */
    }

    public function SDH_add_payment_gateway ($gateway_name, $function_name) {
        // Проверяем, не добавлен ли уже такой шлюз
        if (!in_array($gateway_name, $this->registered_gateways)) {
            // Ваш код для добавления платежной системы, например:

            // Сохраняем имя добавленного шлюза, чтобы избежать дублирования
            $this->registered_gateways[] = $gateway_name;
            $this->registered_gateways_functions[$gateway_name] = $function_name;
        }
    }


    public function get_registered_gateways () {
        return $this->registered_gateways;
    }

    public function get_registered_gateways_functions () {
        return $this->registered_gateways_functions;
    }

    public function enqueue_styles () {
        wp_enqueue_script ('sdh-toast',    SDH_URL . 'src/js/toast-library.js');
        wp_enqueue_script ('sdh-poll',     SDH_URL . 'src/js/pollServer.js');
        wp_enqueue_script ('bootstrap',    SDH_URL . 'assets/bootstrap.bundle.min.js');
        wp_enqueue_style  ('font-awesome', SDH_URL . 'assets/all.min.css');
        wp_enqueue_style  ('sdh-post',     SDH_URL . 'src/css/sdh-post.css');
    }

    function load_textdomain() {
        load_plugin_textdomain('sdh-post', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    private function define_constants () {
        $upload_dir = $this->upload_dir();

        $this->define( 'SDH_URL', $this->plugin_url() . '/' );
        $this->define( 'SDH_PATH', trailingslashit( $this->plugin_path() ) );
        $this->define( 'SDH_UPLOAD_PATH', $upload_dir['basedir'] . '/sdh-uploads/' );
        $this->define( 'SDH_UPLOAD_URL', $upload_dir['baseurl'] . '/sdh-uploads/' );
    }

    private function define ($name, $value) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    public function upload_dir () {
        if ( defined( 'MULTISITE' ) ) {
            $upload_dir = array(
                'basedir' => WP_CONTENT_DIR . '/uploads',
                'baseurl' => WP_CONTENT_URL . '/uploads'
            );
        } else {
            $upload_dir = wp_upload_dir();
        }

        if ( is_ssl() ) {
            $upload_dir['baseurl'] = str_replace( 'http://', 'https://', $upload_dir['baseurl'] );
        }

        return apply_filters( 'sdh_post_upload_dir', $upload_dir, $this );
    }

    public function plugin_url() {
        return untrailingslashit( plugins_url( '/', __FILE__ ) );
    }

    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }
    

    private function init_hooks () {
        register_activation_hook (__FILE__, array( 'SDH_Post', 'install' ));

        add_action ('SDH_add_payment', array($this, 'SDH_add_payment_gateway'), 10, 2);
        add_filter ( 'display_post_states', array($this,'special_page_mark'), 100, 2 );
    }

    public function install () {
        /********* создаем категорию sdh *********/
        $category_exists = term_exists('sdh', 'category'); // Проверяем существует ли рубрика "sdh" в категориях

        if (0 == $category_exists || null == $category_exists) {
            // Рубрика "sdh" не существует, создаем ее
            wp_insert_term('sdh', 'category');
        }
        /********* !создаем категорию sdh *********/

        /********* создаем папку в uploads - sdh-uploads *********/
        $upload_dir = wp_upload_dir();
        $sdh_uploads_dir = $upload_dir['basedir'] . '/sdh-uploads';

        if ( ! file_exists( $sdh_uploads_dir ) ) {
            wp_mkdir_p( $sdh_uploads_dir );
        }
        /********* !создаем папку в uploads - sdh-uploads *********/

        /********* создание страницы Блог *********/
        $page_title = esc_html__ ('Blog', 'sdh-post');
        $page_content = '[sdh-posts]';

        // Проверка, существует ли страница с таким заголовком
        $page_exists = get_page_by_title ($page_title);

        // Если страница не существует, создаем ее
        if (!$page_exists) {
            // Аргументы для создания страницы
            $page_args = array(
                'post_title' => $page_title,
                'post_content' => $page_content,
                'post_status' => 'publish', // Статус "Опубликовано"
                'post_type' => 'page', // Тип записи "Страница"
            );

            // Вставляем страницу и получаем ее ID
            wp_insert_post ($page_args);
        }
        /********* !создание страницы *********/

        /********* создание тестовой записи *********/
        $post_title = 'Test title';
        $template_path = SDH_URL . 'templates/test_tempale.php';
        $post_content = file_get_contents($template_path);

        $category_slug = 'sdh'; // Замените на слаг вашей рубрики

        // Получаем ID рубрики по ее слагу
        $category = get_category_by_slug($category_slug);

        // Проверка, существует ли рубрика
        if ($category) {
            $post_args = array(
                'post_title'    => $post_title,
                'post_content'  => $post_content,
                'post_status'   => 'publish', // Статус "Опубликовано"
                'post_type'     => 'post', // Тип записи "Запись"
                'post_category' => array($category->term_id), // ID рубрики
            );

            // Вставляем запись и получаем ее ID
            wp_insert_post ($post_args);
        }
        /********* !создание тестовой записи *********/
    }


    public function addons_include () {
        $list = get_option('sdh_active_addons', array());

        foreach ($list as $addon) {
            $index_file = SDH_PATH . "addons/$addon/index.php";

            if (file_exists($index_file)) {
                try {
                    require_once $index_file;
                } catch (Throwable $e) {
                    // Обработка ошибок в блоке catch
                    // Например, можно вести лог или выводить сообщение об ошибке
                    $this->sdh_deactivate_addon($addon);

                    // Выводим сообщение в админку
                    $error_message = sprintf('Аддон "%s" был отключен из-за ошибки в коде: %s', $addon, $e->getMessage());
                    add_settings_error('sdh-addons', 'addon-deactivation-error', $error_message, 'error');
                }
            } else {
                // Обработка ситуации, когда файл не найден
                // Например, можно вести лог или выводить сообщение об ошибке
                $this->sdh_deactivate_addon($addon);

                // Выводим сообщение в админку
                $error_message = sprintf('Аддон "%s" был отключен, так как файл index.php не найден.', $addon);
                add_settings_error('sdh-addons', 'addon-deactivation-error', $error_message, 'error');
            }
        }
    }
    
    public function sdh_deactivate_addon ($addon) {
        $active_addons = get_option('sdh_active_addons', array());

        $index = array_search($addon, $active_addons);
        if ($index !== false) {
            unset($active_addons[$index]);
            update_option('sdh_active_addons', $active_addons);
        }
    }

    public function special_page_mark ($post_states, $post) {
        if ($post->post_type === 'page') {
            if ($post->post_name == 'blog' ) {
                $post_states[] = esc_html__ ('SDH-Post Blog Page', 'sdh-post');
            }
        }

        return $post_states;
    }


}

/**
 * Возвращает класс SDH_Post
 * @return SDH_Post SDH_Post
 */
function SDH () {
    return SDH_Post::instance();
}

$GLOBALS['sdh'] = SDH();


