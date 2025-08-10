jQuery(function ($) {
    // 新着
    $(document).on('click', '#load-more', function (e) {
        e.preventDefault();
        var button = $(this);
        if (button.prop('disabled')) return;
        var page = parseInt(button.data('page') || 2, 10);

        button.prop('disabled', true).text('読み込み中...');

        $.ajax({
            url: integlightLoadMore.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'integlight_load_more_posts',   // ← PHP と一致
                page: page,
                nonce: integlightLoadMore.nonce
            }
        }).done(function (response) {
            if (response && response.success) {
                $('#latest-posts-grid').append(response.data);
                button.data('page', page + 1).prop('disabled', false).text('もっと見る');
            } else {
                button.remove();
            }
        }).fail(function () {
            button.prop('disabled', false).text('もっと見る');
        });
    });

    // カテゴリ別
    $(document).on('click', '.load-more-cat', function (e) {
        e.preventDefault();
        var button = $(this);
        if (button.prop('disabled')) return;
        var page = parseInt(button.data('page') || 2, 10);
        var cat = parseInt(button.data('cat') || 0, 10);
        if (!cat) return;

        button.prop('disabled', true).text('読み込み中...');

        $.ajax({
            url: integlightLoadMore.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'integlight_load_more_category_posts', // ← PHP と一致
                page: page,
                cat: cat,
                nonce: integlightLoadMore.nonce
            }
        }).done(function (response) {
            if (response && response.success) {
                var target = button.closest('.category-posts').find('.post-grid').first();
                if (target.length) {
                    target.append(response.data);
                } else {
                    button.prev('.post-grid').append(response.data);
                }
                button.data('page', page + 1).prop('disabled', false).text('もっと見る');
            } else {
                button.remove();
            }
        }).fail(function () {
            button.prop('disabled', false).text('もっと見る');
        });
    });
});
