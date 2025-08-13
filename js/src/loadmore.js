// load-more.js

// グローバル変数 integlightLoadMore は外部でセットされている想定です

export function setupLoadMoreHandlers() {
    // 新着投稿のロードモアボタン
    document.addEventListener('click', function (e) {
        const button = e.target.closest('#load-more');
        if (!button) return;

        e.preventDefault();
        if (button.disabled) return;

        let page = parseInt(button.dataset.page || 2, 10);
        button.disabled = true;
        button.textContent = window.integlightLoadMore.loadingText;

        fetch(window.integlightLoadMore.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'integlight_load_more_posts',
                page: page,
                nonce: window.integlightLoadMore.nonce
            })
        })
            .then(res => res.json())
            .then(response => {
                if (response && response.success) {
                    const grid = document.querySelector('#latest-posts-grid');
                    if (grid) grid.insertAdjacentHTML('beforeend', response.data);
                    button.dataset.page = page + 1;
                    button.disabled = false;
                    button.textContent = window.integlightLoadMore.loadMoreText;
                } else {
                    button.remove();
                }
            })
            .catch(() => {
                button.disabled = false;
                button.textContent = window.integlightLoadMore.loadMoreText;
            });
    });

    // カテゴリ別投稿のロードモアボタン
    document.addEventListener('click', function (e) {
        const button = e.target.closest('.load-more-cat');
        if (!button) return;

        e.preventDefault();
        if (button.disabled) return;

        let page = parseInt(button.dataset.page || 2, 10);
        let cat = parseInt(button.dataset.cat || 0, 10);
        if (!cat) return;

        button.disabled = true;
        button.textContent = window.integlightLoadMore.loadingText;

        fetch(window.integlightLoadMore.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'integlight_load_more_category_posts',
                page: page,
                cat: cat,
                nonce: window.integlightLoadMore.nonce
            })
        })
            .then(res => res.json())
            .then(response => {
                if (response && response.success) {
                    const categoryPosts = button.closest('.category-posts');
                    let target = categoryPosts ? categoryPosts.querySelector('.post-grid') : null;

                    if (target) {
                        target.insertAdjacentHTML('beforeend', response.data);
                    } else {
                        const prevGrid = button.previousElementSibling;
                        if (prevGrid && prevGrid.classList.contains('post-grid')) {
                            prevGrid.insertAdjacentHTML('beforeend', response.data);
                        }
                    }

                    button.dataset.page = page + 1;
                    button.disabled = false;
                    button.textContent = window.integlightLoadMore.loadMoreText;
                } else {
                    button.remove();
                }
            })
            .catch(() => {
                button.disabled = false;
                button.textContent = window.integlightLoadMore.loadMoreText;
            });
    });
}

// ページロード時に自動初期化
document.addEventListener('DOMContentLoaded', function () {
    setupLoadMoreHandlers();
});
