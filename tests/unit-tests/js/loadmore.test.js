/**
 * @jest-environment jsdom
 */
import $ from 'jquery';
import { setupLoadMoreHandlers } from '../../../js/src/loadmore.js';

global.jQuery = $;

describe('Load More Button', () => {
    beforeEach(() => {
        jest.useFakeTimers(); // タイマーを偽装


        document.body.innerHTML = `
            <button id="load-more" data-page="1">Load More</button>
            <div id="latest-posts-grid"></div>
        `;

        global.integlightLoadMore = {
            ajax_url: 'http://example.com/ajax',
            nonce: 'dummy_nonce',
            loadingText: 'Loading...',
            loadMoreText: 'Load More',
        };


        setupLoadMoreHandlers();
    });

    afterEach(() => {
        jest.runOnlyPendingTimers(); // 保留タイマーをすべて実行してクリア
        jest.useRealTimers();         // 実タイマーに戻す
    });

    it('クリック時にボタンを無効化しAjax送信される', () => {
        $.ajax = jest.fn(() => ({
            done(callback) {
                setTimeout(() => callback({ success: true, data: '<p>New Post A</p>' }), 0);
                return this;
            },
            fail() {
                return this;
            },
        }));

        $('#load-more').prop('disabled', false);

        $('#load-more').trigger('click');

        expect($('#load-more').prop('disabled')).toBe(true);
        expect($('#load-more').text()).toBe('Loading...');
        expect($.ajax).toHaveBeenCalledTimes(1);

        jest.runAllTimers(); // タイマーを進める（Ajaxコールバックも実行）
    });

    it('Ajax成功時に投稿が追加されボタンが再度有効になる', (done) => {
        $.ajax = jest.fn(() => ({
            done(callback) {
                setTimeout(() => {
                    callback({ success: true, data: '<p>New Post B</p>' });
                }, 0);
                return this;
            },
            fail() {
                return this;
            },
        }));



        $('#load-more').trigger('click');

        jest.runAllTimers(); // タイマーをすべて実行してAjaxコールバックを呼ぶ

        setTimeout(() => {
            expect($('#latest-posts-grid').html()).toBe('<p>New Post B</p>');
            expect($('#load-more').prop('disabled')).toBe(false);
            expect($('#load-more').text()).toBe('Load More');
            expect($('#load-more').data('page')).toBe(2);
            done();
        }, 0);

        jest.runAllTimers(); // setTimeoutの中のsetTimeoutを実行するために2回呼び出しも有効
    });

    it('Ajax失敗時にボタンが再度有効になる', (done) => {
        $.ajax = jest.fn(() => ({
            done() {
                return this;
            },
            fail(callback) {
                setTimeout(() => {
                    callback();
                }, 0);
                return this;
            },
        }));

        $('#load-more').trigger('click');

        jest.runAllTimers(); // タイマーを進める

        setTimeout(() => {
            expect($('#load-more').prop('disabled')).toBe(false);
            expect($('#load-more').text()).toBe('Load More');
            done();
        }, 0);

        jest.runAllTimers();
    });

    it('ボタンがdisabledならクリックしても何もしない', () => {
        $.ajax = jest.fn();

        $('#load-more').prop('disabled', true);
        $('#load-more').trigger('click');

        expect($.ajax).not.toHaveBeenCalled();
    });
});

describe('Category Load More Button', () => {
    beforeEach(() => {
        // カテゴリ用ボタンと投稿グリッドのDOMをセット
        jest.useFakeTimers();  // ← これを追加

        document.body.innerHTML = `
      <div class="category-posts">
        <div class="post-grid"></div>
        <button class="load-more-cat" data-page="1" data-cat="123">Load More Category</button>
      </div>
    `;

        global.integlightLoadMore = {
            ajax_url: 'http://example.com/ajax',
            nonce: 'dummy_nonce',
            loadingText: 'Loading...',
            loadMoreText: 'Load More',
        };

        setupLoadMoreHandlers();
    });

    it('クリック時にボタンを無効化しAjax送信される', () => {
        $.ajax = jest.fn(() => ({
            done(callback) {
                setTimeout(() => callback({ success: true, data: '<p>New Cat Post A</p>' }), 0);
                return this;
            },
            fail() {
                return this;
            },
        }));

        $('.load-more-cat').prop('disabled', false);

        $('.load-more-cat').trigger('click');

        expect($('.load-more-cat').prop('disabled')).toBe(true);
        expect($('.load-more-cat').text()).toBe('Loading...');
        expect($.ajax).toHaveBeenCalledTimes(1);

        jest.runAllTimers();
    });

    it('Ajax成功時に投稿が追加されボタンが再度有効になる', (done) => {
        $.ajax = jest.fn(() => ({
            done(callback) {
                setTimeout(() => callback({ success: true, data: '<p>New Cat Post B</p>' }), 0);
                return this;
            },
            fail() {
                return this;
            },
        }));

        $('.load-more-cat').trigger('click');
        jest.runAllTimers();

        setTimeout(() => {
            expect($('.category-posts .post-grid').html()).toBe('<p>New Cat Post B</p>');
            expect($('.load-more-cat').prop('disabled')).toBe(false);
            expect($('.load-more-cat').text()).toBe('Load More');
            expect($('.load-more-cat').data('page')).toBe(2);
            done();
        }, 0);

        jest.runAllTimers();
    });

    it('Ajax失敗時にボタンが再度有効になる', (done) => {
        $.ajax = jest.fn(() => ({
            done() {
                return this;
            },
            fail(callback) {
                setTimeout(() => callback(), 0);
                return this;
            },
        }));

        $('.load-more-cat').trigger('click');
        jest.runAllTimers();

        setTimeout(() => {
            expect($('.load-more-cat').prop('disabled')).toBe(false);
            expect($('.load-more-cat').text()).toBe('Load More');
            done();
        }, 0);

        jest.runAllTimers();
    });

    it('ボタンがdisabledならクリックしても何もしない', () => {
        $.ajax = jest.fn();

        $('.load-more-cat').prop('disabled', true);
        $('.load-more-cat').trigger('click');

        expect($.ajax).not.toHaveBeenCalled();
    });

    it('data-cat が無効なら何もしない', () => {
        $.ajax = jest.fn();
        $('.load-more-cat').removeAttr('data-cat');
        $('.load-more-cat').trigger('click');
        expect($.ajax).not.toHaveBeenCalled();
    });

    it('クリック時にcat情報を含むAjaxリクエストが送信される', () => {
        $.ajax = jest.fn((options) => {
            // 送信データを検証
            expect(options.data).toBeDefined();
            expect(options.data.cat).toBe(123);       // data-cat="123" の値がセットされているか
            expect(options.data.page).toBe(1);        // 初期ページ番号も確認
            expect(options.type).toBe('POST');        // HTTPメソッドの確認
            expect(options.url).toBe(global.integlightLoadMore.ajax_url);

            return {
                done(callback) {
                    setTimeout(() => callback({ success: true, data: '<p>Test Post</p>' }), 0);
                    return this;
                },
                fail() {
                    return this;
                },
            };
        });

        $('.load-more-cat').trigger('click');
        jest.runAllTimers();
    });
});
