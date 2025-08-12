// load-more.js
import $ from 'jquery';

// グローバル変数 integlightLoadMore は外部でセットされている想定です

export function setupLoadMoreHandlers() {
    // 新着投稿のロードモアボタン
    $(document).off('click', '#load-more').on('click', '#load-more', function (e) {
        e.preventDefault();
        var button = $(this);
        if (button.prop('disabled')) return;
        var page = parseInt(button.data('page') || 2, 10);

        button.prop('disabled', true).text(window.integlightLoadMore.loadingText);

        $.ajax({
            url: window.integlightLoadMore.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'integlight_load_more_posts',
                page: page,
                nonce: window.integlightLoadMore.nonce
            }
        }).done(function (response) {
            if (response && response.success) {
                $('#latest-posts-grid').append(response.data);
                button.data('page', page + 1).prop('disabled', false).text(window.integlightLoadMore.loadMoreText);
            } else {
                button.remove();
            }
        }).fail(function () {
            button.prop('disabled', false).text(window.integlightLoadMore.loadMoreText);
        });
    });

    // カテゴリ別投稿のロードモアボタン
    $(document).off('click', '.load-more-cat').on('click', '.load-more-cat', function (e) {
        e.preventDefault();
        var button = $(this);
        if (button.prop('disabled')) return;
        var page = parseInt(button.data('page') || 2, 10);
        var cat = parseInt(button.data('cat') || 0, 10);
        if (!cat) return;

        button.prop('disabled', true).text(window.integlightLoadMore.loadingText);

        $.ajax({
            url: window.integlightLoadMore.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'integlight_load_more_category_posts',
                page: page,
                cat: cat,
                nonce: window.integlightLoadMore.nonce
            }
        }).done(function (response) {
            if (response && response.success) {
                var target = button.closest('.category-posts').find('.post-grid').first();
                if (target.length) {
                    target.append(response.data);
                } else {
                    button.prev('.post-grid').append(response.data);
                }
                button.data('page', page + 1).prop('disabled', false).text(window.integlightLoadMore.loadMoreText);
            } else {
                button.remove();
            }
        }).fail(function () {
            button.prop('disabled', false).text(window.integlightLoadMore.loadMoreText);
        });
    });
}

// ページロード時に自動初期化する場合はコメントを外す
$(function () {
    setupLoadMoreHandlers();
});
