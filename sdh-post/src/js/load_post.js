jQuery(document).ready(function($) {
    var page = 2; // Номер следующей страницы
    var loading = false; // Переменная для предотвращения повторной загрузки
    //  экземпляр bootstrap.Toast по ID

    // Функция для загрузки записей
    function loadPosts() {
        if (!loading) {
            loading = true;
            $.ajax({
                url: ajax_object.ajax_url, // URL для обработки Ajax-запросов
                type: 'POST',
                data: {
                    action: 'load_more_posts', // Действие на сервере для обработки запроса
                    page: page,
                    posts_per_page: 5, // Количество записей на странице
                },
                success: function(response) {
                    if (response.status) {
                        var $newPosts = $(response.post).hide();
                        $('.row.justify-content-center').append(response.post);
                        $newPosts.fadeIn('slow');
                        page++;
                        loading = false;
                    } else {
                        SDHshowToast (response.title, response.message)
                    }
                },
            });
        }
    }

    // Вызов функции при прокрутке страницы
    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
            loadPosts();
        }
    });




});