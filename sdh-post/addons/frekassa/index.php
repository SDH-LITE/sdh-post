<?php

class FK {
    public function __construct() {
        $this->includes(); // Подключаем все нужные файлы с функциями и классами
        $this->define_constants ();
    }

    public function includes () {
        require_once 'fk-functions.php';
        require_once 'admin/menu.php';
    }

    private function define_constants () {
        $this->define( 'FK_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/'  );
        $this->define( 'FK_PATH', __DIR__ . '/' );
    }

    private function define ($name, $value) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }
}

$GLOBALS['fk'] = new FK();