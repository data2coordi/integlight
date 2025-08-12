/**
 * @jest-environment jsdom
 */

import $ from 'jquery';
global.jQuery = $;  // グローバル定義（必須）

// テスト用グローバル変数
global.integlightLoadMore = {
    ajax_url: 'http://example.com/ajax',
    nonce: 'dummy_nonce',
    loadingText: 'Loading...',
    loadMoreText: 'Load More'
};

function setupLoadMoreHandlers() {
    // イベント重複防止のため必ずoffしてからon
    $(document).off('click', '#load-more');
    $(document).on('click', '#load-more', function (e) {
        e.preventDefault();
        var button = $(this);
        if (button.prop('disabled')) return;
        var page = parseInt(button.data('page') || 2, 10);

        button.prop('disabled', true).text(integlightLoadMore.loadingText);

        $.ajax({
            url: integlightLoadMore.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'integlight_load_more_posts',
                page: page,
                nonce: integlightLoadMore.nonce
            }
        }).done(function (response) {
            if (response && response.success) {
                $('#latest-posts-grid').append(response.data);
                button.data('page', page + 1).prop('disabled', false).text(integlightLoadMore.loadMoreText);
            } else {
                button.remove();
            }
        }).fail(function () {
            button.prop('disabled', false).text(integlightLoadMore.loadMoreText);
        });
    });
}

describe('Load More Button', () => {
    beforeEach(() => {
        // DOM初期化
        document.body.innerHTML = `
            <button id="load-more" data-page="1">Load More</button>
            <div id="latest-posts-grid"></div>
        `;
        setupLoadMoreHandlers();
    });


    it('クリック時にボタンを無効化しAjax送信される', () => {
        $.ajax = jest.fn(() => ({
            done(callback) {
                setTimeout(() => callback({ success: true, data: '<p>New Post</p>' }), 0);
                return this;
            },
            fail() {
                return this;
            }
        }));

        $('#load-more').prop('disabled', false);
        $('#load-more').trigger('click');

        // クリックイベントは同期処理なので即座にdisabledはtrueに
        expect($('#load-more').prop('disabled')).toBe(true);
        expect($('#load-more').text()).toBe('Loading...');

        expect($.ajax).toHaveBeenCalledTimes(1);
    });

    it('Ajax成功時に投稿が追加されボタンが再度有効になる', (done) => {
        $.ajax = jest.fn(() => ({
            done(callback) {
                setTimeout(() => {
                    callback({ success: true, data: '<p>New Post</p>' });
                    // done()はここで呼ぶ
                    done();
                }, 0);
                return this;
            },
            fail() {
                return this;
            }
        }));

        $('#load-more').trigger('click');

        setTimeout(() => {
            expect($('#latest-posts-grid').html()).toBe('<p>New Post</p>');
            expect($('#load-more').prop('disabled')).toBe(false);
            expect($('#load-more').text()).toBe('Load More');
            expect($('#load-more').data('page')).toBe(2);
        }, 10);
    });




    it('Ajax成功時に投稿が追加されボタンが再度有効になる', (done) => {
        $.ajax = jest.fn(() => ({
            done(callback) {
                callback({ success: true, data: '<p>New Post</p>' });
                return this;
            },
            fail() {
                return this;
            }
        }));

        $('#load-more').trigger('click');

        setTimeout(() => {
            expect($('#latest-posts-grid').html()).toBe('<p>New Post</p>');
            expect($('#load-more').prop('disabled')).toBe(false);
            expect($('#load-more').text()).toBe('Load More');
            expect($('#load-more').data('page')).toBe(2);
            done();
        }, 0);
    });

    it('Ajax失敗時にボタンが再度有効になる', (done) => {
        $.ajax = jest.fn(() => ({
            done() {
                return this;
            },
            fail(callback) {
                callback();
                return this;
            }
        }));

        $('#load-more').trigger('click');

        setTimeout(() => {
            expect($('#load-more').prop('disabled')).toBe(false);
            expect($('#load-more').text()).toBe('Load More');
            done();
        }, 0);
    });

    it('ボタンがdisabledならクリックしても何もしない', () => {
        $.ajax = jest.fn();

        $('#load-more').prop('disabled', true);
        $('#load-more').trigger('click');

        expect($.ajax).not.toHaveBeenCalled();
    });
});
