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
