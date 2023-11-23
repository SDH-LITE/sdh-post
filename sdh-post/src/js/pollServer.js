// pollServer.js

function pollServer (Data, time_pull = 2000, callback) {
    Data['action'] = 'SDH_callback';
    // Отправка AJAX-запроса на сервер
    jQuery.ajax({
        type: 'POST',
        url: ajax_object.ajax_url, // URL-адрес обработчика AJAX-запросов WordPress
        data: Data,
        success: function (response) {
            // Обработка ответа от сервера
            if (response.status === 'success') {
                // Ваш код обработки данных
                if (typeof callback === 'function') {
                    callback(response.data);
                }
            } else {
                console.log('Error:', response.message);
                // Ваш код обработки ошибки
            }

            // Повторный опрос через time_pull секунд (или другой интервал)
            setTimeout(function () {
                pollServer(Data, time_pull, callback);
            }, time_pull);
        },
        error: function (error) {
            console.log('AJAX Error:', error);
            // Ваш код обработки ошибки AJAX

            // Повторный опрос через time_pull секунд (или другой интервал)
            setTimeout(function () {
                pollServer(Data, time_pull, callback);
            }, time_pull);
        }
    });
}
