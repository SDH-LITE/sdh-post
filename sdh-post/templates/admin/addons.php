<?php
if (isset($_POST['install-addon-submit'])) {
    // Проверяем, загружен ли файл
    if (isset($_FILES['addonzip']) && $_FILES['addonzip']['error'] === UPLOAD_ERR_OK) {
        $zip_file = $_FILES['addonzip']['tmp_name']; // Временный файл ZIP-архива

        // Папка для разархивированных файлов внутри вашего плагина
        $extracted_folder = SDH_PATH . 'addons/';

        // Создаем папку, если ее нет
        if (!file_exists($extracted_folder)) {
            mkdir($extracted_folder, 0755, true);
        }

        // Разархивируем ZIP-архив
        $zip = new ZipArchive;
        if ($zip->open($zip_file) === true) {
            $zip->extractTo($extracted_folder);
            $zip->close();
        }

        // Ваш код для обработки разархивированных файлов, если необходимо
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['addon'])) {
    $addon_to_delete = sanitize_text_field($_GET['addon']);

    ping_script_async (SDH_URL . 'addons/' . $addon_to_delete . '/remove.php');

    // Проверяем, существует ли папка аддона
    $addon_folder_path = SDH_PATH . 'addons/' . $addon_to_delete;
    if (file_exists($addon_folder_path)) {
        // Удаляем папку аддона
        $deleted = sdh_recursive_remove_directory($addon_folder_path);

        if ($deleted) {
            // Ваш код для обработки успешного удаления
            echo '<div id="message" class="notice notice-success is-dismissible">
	                <p>Аддон успешно удален.</p>
                  </div>';
        } else {
            // Ваш код для обработки ошибки удаления
            echo '<div id="message" class="notice notice-error is-dismissible">
	                <p>Ошибка при удалении аддона.</p>
                  </div>';
        }
    } else {
        // Ваш код для обработки случая, когда папка не существует
        echo '<div id="message" class="notice notice-error is-dismissible">
	                <p>Папка аддона не найдена.</p>
             </div>';
    }
}

// Функция для рекурсивного удаления директории
function sdh_recursive_remove_directory($directory) {
    if (!file_exists($directory)) {
        return true;
    }

    if (!is_dir($directory)) {
        return unlink($directory);
    }

    foreach (scandir($directory) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!sdh_recursive_remove_directory($directory . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return rmdir($directory);
}


function parse_info_file ($info_content) {
    $info_lines = explode(";", $info_content);

    $info_data = [];

    foreach ($info_lines as $line) {
        $parts = explode(': ', $line);

        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);

            $info_data[$key] = $value;
        }
    }

    return $info_data;
}

function get_info_files_contents($addons_folder) {
    $info_files_contents = [];

    $addons = scandir($addons_folder);

    $k = 0;
    foreach ($addons as $addon) {
        if ($addon != '.' && $addon != '..' && is_dir($addons_folder . '/' . $addon)) {
            $info_file_path = $addons_folder . '/' . $addon . '/info.txt';

            if (file_exists($info_file_path)) {
                $info_files_contents[$k] = file_get_contents($info_file_path);
                $info_files_contents['f_name_' . $k] = $addon;
                $k++;
            }
        }
    }


    return $info_files_contents;
}

$addons_contents = get_info_files_contents(SDH_PATH . 'addons');

foreach ($addons_contents as $k => $content) {
    if (is_int($k)) {
        $addons_info[] = [parse_info_file($content), $addons_contents['f_name_' . $k]];
    }
}

$active = sdh_get_active_addons ();
?>

<div class="wrap">
    <h2><?php echo get_admin_page_title() ?></h2>
    <div class="service-box">
        <h4>Загрузить дополнение для SDH-Post в формате .zip</h4>
        <p class="install-helps">Если у вас есь архив дополнения к SDH-Post в формате ZIP, то вы можете загрузить и установить его здесь.</p>
        <form class="wp-upload-form" action="" enctype="multipart/form-data" method="post">
            <label class="screen-reader-text" for="addonzip">Архив дополнения</label>
            <input id="addonzip" type="file" name="addonzip" accept=".zip">
            <input id="install-plugin-submit" class="button" type="submit" value="Установить" name="install-addon-submit" disabled="">
        </form>

    </div>

    <table class=" widefat fixed striped plugins ">
        <thead>
        <tr>
            <th style="width: 15%; text-align: center;">
                <a>Дополнения</a>
            </th>
            <th style="width: 15%; text-align: center;">
                <a>Состояние</a>
            </th>
            <th style="text-align: center;">Описание</th>
        </tr>
        </thead>

        <tbody id="the-list">

        <?php
        foreach ($addons_info as $info) {
            $addon = $info[0];
            $addon_folder = $info[1];

            if ($addon['Description'] != '') {
                $img_url = SDH_URL . 'addons/' . $addon_folder . '/icon.jpg';
                $img = file_get_contents($img_url);

                $addon_active = in_array ($addon_folder, $active);
                ?>
                <tr <?=($addon_active) ? 'class="active"' : ''?>>
                    <td style="text-align: center; <?=($addon_active) ? 'border-left: 4px solid #72aee6;' : ''?>">
                        <div style="display: flex; align-items: center; justify-content: center;">
                            <img width="40%" class="circle responsive-img" src="<?= $img_url ?>" alt="<?= $addon['Name'] ?>">
                            <p style="padding: 10px; margin: 10px;"><?= $addon['Name'] ?></p>
                        </div>
                        <div class="row-actions">
                    <span class="delete">
                        <a href="?page=sdh-addons-page&amp;action=delete&amp;addon=<?= $addon_folder ?>">Удалить</a>
                    </span>
                        </div>
                    </td>

                    <td style="text-align: center;">
                        <div style="padding: 10px; margin: 10px;" class="switch">
                            <label>
                                Выкл
                                <input id="<?=$addon_folder?>" class="ab" type="checkbox" <?=($addon_active) ? 'checked' : ''?>>
                                <span class="lever"></span>
                                Вкл
                            </label>
                        </div>
                    </td>

                    <td>
                        <p style="padding: 10px; margin: 10px;"><?= $addon['Description'] ?></p>
                        <div style="padding: 10px; margin: 10px;">
                            <?php if (!empty($addon['Version'])) : ?>
                                <?= $addon['Version'] ?> |
                            <?php endif; ?>
                            <?php if (!empty($addon['Author'])) : ?>
                                Автор:
                                <a title="Посетить страницу автора" href="<?= $addon['Author URI'] ?>" target="_blank"><?= $addon['Author'] ?></a> |
                            <?php endif; ?>
                            <?php if (!empty($addon['Add-on URI'])) : ?>
                                <a title="Посетить страницу дополнения" href="<?= $addon['Add-on URI'] ?>" target="_blank">Страница дополнения</a>
                            <?php endif; ?>
                        </div>

                    </td>
                </tr>
                <?php
            }
        }
        ?>


        </tbody>
    </table>
</div>

<style>
    .service-box {
        background-color: #f5f5f5;
        border: 1px solid #e5e5e5;
        margin: 10px 0;
        padding: 0 10px 4px;
    }

    .install-helps::before {
        color: #ffae19;
        content: "\f534";
        font-family: dashicons;
        font-size: 18px;
        font-style: normal;
        left: 0;
        opacity: 0.4;
        position: static;
        top: 0;
    }

    p.install-helps {
        margin: 8px 0;
        font-style: italic;
    }

    h4 {
        font-weight: 600 !important;
        font-size: 1em!important;
        margin: 1.33em 0!important;
    }
</style>