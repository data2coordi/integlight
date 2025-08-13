/**
 * @jest-environment jsdom
 */
import { setupLoadMoreHandlers } from '../../../js/src/loadmore.js';

// 非同期解決を待つためのユーティリティ
const flushPromises = () => new Promise(jest.requireActual('timers').setImmediate);

describe('Load More Button', () => {
    beforeEach(() => {
        jest.useFakeTimers();

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
        jest.runOnlyPendingTimers();
        jest.useRealTimers();
        jest.resetAllMocks();
    });

    it('クリック時にボタンを無効化しfetch送信される', async () => {
        global.fetch = jest.fn(() =>
            Promise.resolve({
                json: () => Promise.resolve({ success: true, data: '<p>New Post A</p>' })
            })
        );

        const btn = document.querySelector('#load-more');
        btn.disabled = false;

        btn.click();

        expect(btn.disabled).toBe(true);
        expect(btn.textContent).toBe('Loading...');
        expect(global.fetch).toHaveBeenCalledTimes(1);

        await flushPromises();
        jest.runAllTimers();
    });

    it('fetch成功時に投稿が追加されボタンが再度有効になる', async () => {
        global.fetch = jest.fn(() =>
            Promise.resolve({
                json: () => Promise.resolve({ success: true, data: '<p>New Post B</p>' })
            })
        );

        const btn = document.querySelector('#load-more');
        btn.click();

        await flushPromises();
        jest.runAllTimers();

        const grid = document.querySelector('#latest-posts-grid');
        expect(grid.innerHTML).toBe('<p>New Post B</p>');
        expect(btn.disabled).toBe(false);
        expect(btn.textContent).toBe('Load More');
        expect(btn.dataset.page).toBe('2');
    });

    it('fetch失敗時にボタンが再度有効になる', async () => {
        global.fetch = jest.fn(() => Promise.reject());

        const btn = document.querySelector('#load-more');
        btn.click();

        await flushPromises();
        jest.runAllTimers();

        expect(btn.disabled).toBe(false);
        expect(btn.textContent).toBe('Load More');
    });

    it('ボタンがdisabledならクリックしてもfetchは呼ばれない', () => {
        global.fetch = jest.fn();

        const btn = document.querySelector('#load-more');
        btn.disabled = true;
        btn.click();

        expect(global.fetch).not.toHaveBeenCalled();
    });
});

describe('Category Load More Button', () => {
    beforeEach(() => {
        jest.useFakeTimers();

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

    afterEach(() => {
        jest.runOnlyPendingTimers();
        jest.useRealTimers();
        jest.resetAllMocks();
    });

    it('クリック時にボタンを無効化しfetch送信される', async () => {
        global.fetch = jest.fn(() =>
            Promise.resolve({
                json: () => Promise.resolve({ success: true, data: '<p>New Cat Post A</p>' })
            })
        );

        const btn = document.querySelector('.load-more-cat');
        btn.disabled = false;

        btn.click();

        expect(btn.disabled).toBe(true);
        expect(btn.textContent).toBe('Loading...');
        expect(global.fetch).toHaveBeenCalledTimes(1);

        await flushPromises();
        jest.runAllTimers();
    });

    it('fetch成功時に投稿が追加されボタンが再度有効になる', async () => {
        global.fetch = jest.fn(() =>
            Promise.resolve({
                json: () => Promise.resolve({ success: true, data: '<p>New Cat Post B</p>' })
            })
        );

        const btn = document.querySelector('.load-more-cat');
        btn.click();

        await flushPromises();
        jest.runAllTimers();

        const grid = document.querySelector('.category-posts .post-grid');
        expect(grid.innerHTML).toBe('<p>New Cat Post B</p>');
        expect(btn.disabled).toBe(false);
        expect(btn.textContent).toBe('Load More');
        expect(btn.dataset.page).toBe('2');
    });

    it('fetch失敗時にボタンが再度有効になる', async () => {
        global.fetch = jest.fn(() => Promise.reject());

        const btn = document.querySelector('.load-more-cat');
        btn.click();

        await flushPromises();
        jest.runAllTimers();

        expect(btn.disabled).toBe(false);
        expect(btn.textContent).toBe('Load More');
    });

    it('ボタンがdisabledならクリックしてもfetchは呼ばれない', () => {
        global.fetch = jest.fn();

        const btn = document.querySelector('.load-more-cat');
        btn.disabled = true;
        btn.click();

        expect(global.fetch).not.toHaveBeenCalled();
    });

    it('data-cat が無効ならfetchは呼ばれない', () => {
        global.fetch = jest.fn();

        const btn = document.querySelector('.load-more-cat');
        btn.removeAttribute('data-cat');
        btn.click();

        expect(global.fetch).not.toHaveBeenCalled();
    });

    it('クリック時にcat情報を含むfetchリクエストが送信される', async () => {
        global.fetch = jest.fn((url, options) => {
            const body = new URLSearchParams(options.body);
            expect(body.get('cat')).toBe('123');
            expect(body.get('page')).toBe('1');
            expect(options.method).toBe('POST');
            expect(url).toBe(global.integlightLoadMore.ajax_url);

            return Promise.resolve({
                json: () => Promise.resolve({ success: true, data: '<p>Test Post</p>' })
            });
        });

        const btn = document.querySelector('.load-more-cat');
        btn.click();

        await flushPromises();
        jest.runAllTimers();
    });
});
