<?php
// 新着4件
$args_latest = [
    'posts_per_page' => 4,
    'post_status'    => 'publish',
];
$latest_query = new WP_Query($args_latest);

if ($latest_query->have_posts()) :
    echo '<section class="latest-posts">';
    echo '<h2>新着情報</h2>';
    echo '<div id="latest-posts-grid" class="post-grid">';

    while ($latest_query->have_posts()) : $latest_query->the_post();
        get_template_part('template-parts/content', 'post-card');
    endwhile;

    echo '</div>'; // .post-grid
    echo '<button id="load-more" data-page="2">' . __('もっと見る', 'integlight') . '</button>';
    echo '</section>';
    wp_reset_postdata();
endif;

// カテゴリー別
echo '<section class="top-categories-posts">';
echo '<h2>トップカテゴリー毎の記事</h2>';

$categories = get_categories([
    'parent'     => 0, //最上位カテゴリのみ
    'hide_empty' => true, //空カテゴリは無視
    'number'     => 0, //上限なし
]);

foreach ($categories as $cat) :
    echo '<section class="category-posts">';
    echo '<h3>' . esc_html($cat->name) . '</h3>';
    echo '<div class="post-grid">';

    $cat_query = new WP_Query([
        'cat'            => $cat->term_id,
        'posts_per_page' => 2,
        'post_status'    => 'publish',
    ]);

    if ($cat_query->have_posts()) :
        while ($cat_query->have_posts()) : $cat_query->the_post();
            get_template_part('template-parts/content', 'post-card');
        endwhile;
        wp_reset_postdata();
    else :
        echo '<p>投稿がありません。</p>';
    endif;

    echo '</div>';
    echo '<button class="load-more-cat" data-page="2" data-cat="' . esc_attr($cat->term_id) . '">'
        . __('もっと見る', 'integlight') . '</button>';
    echo '</section>';
endforeach;

echo '</section>';
