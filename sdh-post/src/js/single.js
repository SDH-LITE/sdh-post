jQuery(document).ready(function($) {
    $('.sdh-div h2').each(function() {
        // Создать новый элемент strong
        var strongElement = $('<strong></strong>');
        // Заменить h2 на strong
        $(this).replaceWith(strongElement.text($(this).text()));
    });

    function checkUserLike(postID) {
        var $likeButton = $('.like-button');
        var $heartIcon = $likeButton.find('.fa-heart');
        var $hearNofon = $likeButton.find('.nofon');

        $heartIcon.show();

        var productData = {
            action: 'check_user_like', // Действие для обработки на сервере
            postID: postID,
        };

        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url, // Путь к серверному обработчику AJAX
            data: productData,
            success: function (response) {
                if (response.liked) {
                    $heartIcon.show();
                    $hearNofon.hide();
                } else {
                    $heartIcon.hide();
                    $hearNofon.show();
                }
            }
        });
    }

    checkUserLike ( $('.like-button').find('.postID').text() );

    $(document).on('click', '.like-button', function() {
        var $likeButton = $(this);
        var $heartIcon = $likeButton.find('.fa-heart');
        var $hearNofon = $likeButton.find('.nofon');
        var $likeCount = $likeButton.find('.like-count');
        var currentCount = parseInt($likeCount.text(), 10);

        var postID = $likeButton.find('.postID').text();

        if (postID > 0) {
            var productData = {
                action: 'get_like', // Действие для обработки на сервере
                postID: postID,
            };

            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url, // Путь к серверному обработчику AJAX
                data: productData,
                success: function (response) {
                    if (response.liked) {
                        $heartIcon.show();
                        $hearNofon.hide();

                        $likeCount.text(currentCount + 1);
                    } else {
                        $heartIcon.hide();
                        $hearNofon.show();

                        $likeCount.text(currentCount - 1);
                    }
                },

            });
        }
    });

    $(document).on('click', '.like-comments', function() {
        var id = $(this).attr('id'); // Используйте метод .attr() для получения значения атрибута 'id'
        var numbers = id.replace(/\D/g, ''); // Заменить все нецифровые символы на пустую строку

        var productData = {
            action: 'like_comment', // Действие для обработки на сервере
            comment_id: numbers,
        };

        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: productData,
            success: function (response) {
                SDHshowToast(response.title, response.message)

                if (response.status) {
                    if (response.result) { // лайкнуто
                        var old_like = Number ($('#l_' + numbers).html ())

                        $('#l_' + numbers).html (old_like + 1);
                    } else {
                        var old_like = Number($('#l_' + numbers).html ())

                        $('#l_' + numbers).html (old_like - 1)
                    }
                }

            }
        });
    });


    $(document).on('click', '#submit-comment', function() {
        var commentText = $('#comment-text').val();
        var post_id = $('#post_id').val()

        if (commentText != '') {
            var productData = {
                action: 'send_comment', // Действие для обработки на сервере
                post_id: post_id,
                comment: commentText
            };

            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                data: productData,
                success: function (response) {
                    if (response.status) {

                        $('#comment-text').val('');
                        $('.card-body.p-4.com').append(response.html);

                        $('html, body').animate({
                            scrollTop: $('#sdh_comment_' + response.comment_id).offset().top
                        }, 100);
                    } else {
                        SDHshowToast(sdh_tnaslate ("System Notification"), response)
                    }

                }
            });
        }

    });

    $(document).on('click', '#submit-replay', function() {
        var commentText = $('#comment-text').val();
        var post_id = $('#post_id').val()
        var replay_id = $('#replay_id_val').val()

        if (commentText != '') {
            var productData = {
                action: 'send_replay', // Действие для обработки на сервере
                post_id: post_id,
                comment: commentText,
                replay_id: replay_id
            };

            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                data: productData,
                success: function (response) {
                    SDHshowToast (response.title, response.message)
                    $('#comment-text').val('');
                    $('#sdh_reply_' + replay_id).append(response.html);

                    $('html, body').animate({
                        scrollTop: $('#sdh_reply_' + response.replay_id).offset().top
                    }, 100);

                    hideReplyForm()
                }
            });
        }

    });

    $(document).on('click', '.reply-btn', function() {
        var commentId = $(this).data('comment-id');
        showReplyForm(commentId, this);
    });

    $(document).on('click', '.edit_confirm', function() {
        var id = $(this).attr('id'); // Используйте метод .attr() для получения значения атрибута 'id'
        var numbers = id.replace(/\D/g, ''); // Заменить все нецифровые символы на пустую строку

        var newText = $("#comment-text_edit_" + numbers).val();
        $("#text_comment_" + numbers).html (`<p>${newText}</p>`)

        var productData = {
            action: 'edit_comment', // Действие для обработки на сервере
            comment_id: numbers,
            text: newText,
        };

        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: productData,
            success: function (response) {
                SDHshowToast(response.title, response.message)
            }
        });
    });

    $(document).on('click', '.edit', function() {
        var id = $(this).attr('id'); // Используйте метод .attr() для получения значения атрибута 'id'
        var numbers = id.replace(/\D/g, ''); // Заменить все нецифровые символы на пустую строку

        cleanText = $("#text_comment_" + numbers).html().replace(/<\/?[^>]+(>|$)/g, "");

        var TA = `<textarea class="form-control" id="comment-text_edit_${numbers}" rows="4">${cleanText.trim()}</textarea>
                 <div class="float-end mt-2 pt-1">
                    <button id="edit-comment_${numbers}" type="button" class="btn btn-primary btn-sm edit_confirm">${sdh_tnaslate ("Edit")}</button>
                </div>`;
        var $textZone = $("#text_comment_" + numbers).html (TA)

    });


    $(document).on('click', '.remove', function() {
        var id = $(this).attr('id'); // Используйте метод .attr() для получения значения атрибута 'id'
        var numbers = id.replace(/\D/g, ''); // Заменить все нецифровые символы на пустую строку


        var productData = {
            action: 'del_comment', // Действие для обработки на сервере
            comment_id: numbers,
        };

        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: productData,
            success: function (response) {
                SDHshowToast(response.title, response.message)
                if (response.status) {
                    $("#sdh_comment_" + numbers).slideUp("slow", function () {
                        $(this).remove();
                        $("#h_" + numbers).remove();
                    });
                }
            }
        });

    });

    $(document).on('click', '#cansel-comment', function() {
        hideReplyForm()
    });

    function sdh_tnaslate (text) {
        var res = '';

        var productData = {
            action: 'translate', // Действие для обработки на сервере
            text: text,
        };

        if (text != '') {
            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                data: productData,
                async: false,
                success: function (response) {
                    res = response.value;
                }
            });

            return res;
        }
    }

    function showReplyForm(commentId, e) {
        $('html, body').animate({
            scrollTop: $('#comment-text').offset().top - 100
        }, 100);

        $('#comment-text').val('')
        setCursorById('comment-text');

        var nickReplayValue = $(e).attr("nick-replay");
        var commentIdValue = $(e).attr("comment-id");

        $("#cansel-comment").css("display", "unset");
        $("#submit-comment").css("display", "none");
        $("#submit-replay").css("display", "unset");

        $('#replay_id_val').val (commentIdValue);
        document.getElementById('replay_id_val').value = commentIdValue


        $("#textReplay").text( sdh_tnaslate ("Comment to the post") + " " + nickReplayValue);
    }

    function hideReplyForm() {
        $("#cansel-comment").css("display", "none");
        $("#submit-replay").css("display", "none");
        $("#submit-comment").css("display", "flex");
        $("#textReplay").text("");
        $('#replay_id').val(0)
        document.getElementById('replay_id_val').value = 0
    }

    function setCursorById(elementId) {
        var element = document.getElementById(elementId);

        if (element) {
            // Установить фокус на элементе
            element.focus();

            // Получить текстовый узел (если таковой есть) внутри элемента
            var textNode = element.childNodes[0];

            if (textNode) {
                // Создать Range
                var range = document.createRange();

                // Установить Range на текстовый узел
                range.setStart(textNode, textNode.length);
                range.collapse(true);

                // Создать Selection
                var selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
            }
        }
    }

    $(document).on('click', '#buyOrderBtn', function() {
        $('#buyOrder').modal('show')
    });

    $(document).on('click', '#closeModal', function() {
        $('#buyOrder').modal('hide')
    });

    $(document).on('click', '#buyConfirm', function() {
        var $buy = $(this);
        var $buyZone = $('#buyZone')
        var selectedPayment = $('#PayMents').val();

        var post_id = $('.like-button').find('.postID').text()

        $buy.text ( sdh_tnaslate ("Getting Details") + "..." )

        var productData = {
            action: 'buy', // Действие для обработки на сервере
            order_id: post_id,
            payment_method: selectedPayment,
        };


        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: productData,
            success: function (response) {
                if (response.status) {
                    $buy.text ( sdh_tnaslate ("Data received") )
                    $buyZone.html (response.html)
                    $buy.remove();
                } else {
                    SDHshowToast (response.title, response.message)
                }
            }
        });


    });

    $('.wp-block-image img').fancybox({
        afterClose: function (instance, current) {
            // Восстанавливаем стили изображения после закрытия
            $(current.src).css('display', 'block');
        }
    });

    $('.post-image img').fancybox({
        afterClose: function (instance, current) {
            // Восстанавливаем стили изображения после закрытия
            $(current.src).css('display', 'block');
        }
    });


    function handleData (data) {
        if (data.result == 'ok') {
            var html = data.html;

            if (html != '') {
                $('#buyZone').html (html)
            }

        }
    }


    var poolData = {
        'SDHAction': 'PaymentsCheck',
        'SDHData' : {
            'i' : 1,
        }

    };

    pollServer (poolData, 2000, handleData)

});
