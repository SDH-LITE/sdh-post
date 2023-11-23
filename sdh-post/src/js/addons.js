jQuery(document).ready(function($) {

    $(document).on('click', '.ab', function() {
        var addon_name = $(this).attr('id');
        var addon_work = this.checked;

        var productData = {
            action: 'addon_set', // Действие для обработки на сервере
            addon_name: addon_name,
            status_set: addon_work
        };

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: productData,
            success: function (response) {
                console.log (response)
                if (response.status) {

                }
            }
        });
    });

});



