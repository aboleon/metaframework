function instant_search_results(result) {
    let list = '<div class="suggestions"><ul>', c = jQuery('#instant-search'), i = 0;
    if (result.items.length) {
        for (i = 0; i < result.items.length; ++i) {
            list = list.concat('<li data-id="' + result.items[i].id + '" class="flex spb val">');
            list = list.concat('<div><a href="/panel/meta/show/'+result.items[i].type + '/' + result.items[i].id + '">' + result.items[i].title);
/*
            if (result.items[i].type == 'bloc') {
                list = list.concat(' ('+ result.items[i].has_parent.title +')');
            }

 */
            list = list.concat('</a></div><div>');
            if (!result.hasOwnProperty('hide_type')) {
                list = list.concat('<span class="badge badge-info type">' + result.items[i].type + '</span>');
            }
            list = list.concat('<span class="badge badge-' + (result.items[i].published == 1 ? 'success' : 'danger') + '">' + (result.items[i].published == 1 ? 'En ligne' : 'Hors ligne') + '</span>');
            list = list.concat('</div></li>');
        }
    } else {
        list = list.concat('<li>Aucun résultat</li>');
    }
    list = list.concat('</ul></div>');
    c.append(list).find('.suggestions').show();
}


(function ($) {
    $('#instant-search input').keyup(function () {
        let container = $('#instant-search'),
            data = $(this).val(),
            tag = $(this).data('type'),
            callback = 'instant_search_results';
        container.find('.suggestions').remove();
        setDelay(function () {
            if (data.length > 2) {
                let formData = 'action=instantSearch&callback=' + callback + '&keyword=' + data + '&type=' + tag;
                mfwAjax(formData, container);
            } else {
                $('.suggestions').empty();
            }
        }, 500);
    });
})(jQuery);
