<?php
class Integlight_loadMore
{
    private $new_posts_per_page = 4;
    private $cat_posts_per_page = 2;

    public function __construct()
    {
        add_action('template_redirect', [$this, 'pre_enqueue_scripts']); //PF対応!!!
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']); //PF対応!!!

        // ← 正しいフック名で登録する（wp_ajax_ / wp_ajax_nopriv_）
        add_action('wp_ajax_integlight_load_more_posts', [$this, 'ajax_load_more_posts']);
        add_action('wp_ajax_nopriv_integlight_load_more_posts', [$this, 'ajax_load_more_posts']);

        add_action('wp_ajax_integlight_load_more_category_posts', [$this, 'ajax_load_more_category_posts']);
        add_action('wp_ajax_nopriv_integlight_load_more_category_posts', [$this, 'ajax_load_more_category_posts']);
    }

    public function pre_enqueue_scripts()
    {

        if (is_home() && 'home2' === get_theme_mod('integlight_hometype_setting', 'home1')) {
            Integlight_outerAssets_js_forCall::regLoadMoreScripts();
        }
    }

    public function enqueue_scripts()
    {

        if (is_home() && 'home2' === get_theme_mod('integlight_hometype_setting', 'home1')) {

            wp_localize_script(Integlight_outerAssets_js_forCall::getLoadMoreScriptsHandleName(), 'integlightLoadMore', [
                'loadMoreText'      => __('もっと見る', 'integlight'),
                'loadingText'       => __('読み込み中...', 'integlight'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('integlight_load_more_nonce'),
            ]);
        }
    }

    public function ajax_load_more_posts()
    {
        check_ajax_referer('integlight_load_more_nonce', 'nonce');

        $paged = max(1, intval($_POST['page'] ?? 1));

        $query = new WP_Query([
            'posts_per_page' => $this->new_posts_per_page,
            'paged'          => $paged,
            'post_status'    => 'publish',
        ]);

        if ($query->have_posts()) {
            ob_start();
            while ($query->have_posts()) {
                $query->the_post();
                get_template_part('template-parts/content', 'post-card');
            }
            $html = ob_get_clean();
            wp_reset_postdata(); // 重要
            wp_send_json_success($html);
        } else {
            wp_reset_postdata();
            wp_send_json_error('no_more_posts');
        }
    }

    public function ajax_load_more_category_posts()
    {
        check_ajax_referer('integlight_load_more_nonce', 'nonce');

        $paged  = max(1, intval($_POST['page'] ?? 1));
        $cat_id = intval($_POST['cat'] ?? 0);

        if (!$cat_id) {
            wp_send_json_error('invalid_category');
            wp_die();
        }

        $query = new WP_Query([
            'cat'            => $cat_id,
            'posts_per_page' => $this->cat_posts_per_page,
            'paged'          => $paged,
            'post_status'    => 'publish',
        ]);

        if ($query->have_posts()) {
            ob_start();
            while ($query->have_posts()) {
                $query->the_post();
                get_template_part('template-parts/content', 'post-card');
            }
            $html = ob_get_clean();
            wp_reset_postdata();
            wp_send_json_success($html);
        } else {
            wp_reset_postdata();
            wp_send_json_error('no_more_posts');
        }
    }
}


new Integlight_loadMore();
