<?php
// 新着4件表示
$args_latest = [
    'posts_per_page' => 4,
    'post_status' => 'publish',
];
$latest_query = new WP_Query($args_latest);
if ($latest_query->have_posts()) :
    echo '<section class="latest-posts">';
    echo '<h2>新着情報</h2>';
    echo '<div class="post-grid">'; // グリッドラップ

    while ($latest_query->have_posts()) : $latest_query->the_post();
?>
        <div class="grid-item">
            <div class="post-category">
                <p><?php the_category(', '); ?></p>
            </div>

            <a href="<?php the_permalink(); ?>">
                <div class="post-thumbnail">
                    <?php Integlight_PostThumbnail::render(); ?>
                </div>

                <h2><?php
                    $tmpTitle = get_the_title();
                    echo esc_html(
                        (strlen($tmpTitle) > 25) ? wp_html_excerpt($tmpTitle, 25) . ' ...' : $tmpTitle
                    );
                    ?></h2>

                <p class="post-excerpt">
                    <?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_content()), 78, ' ...')); ?>
                </p>

                <div class="post-meta">
                    <p class="post-date"><?php echo __("Published on", "integlight"); ?> : <?php echo get_the_date(); ?></p>
                </div>
            </a>
        </div>
        <?php
    endwhile;

    echo '</div>'; // .post-grid
    echo '<p class="more-link"><a href="' . esc_url(home_url('/news/')) . '">もっと見る</a></p>';

    // 固定ページのブログ一覧へのリンク（設定がない場合は home_url('/')などに変更）
    echo '</section>';
    wp_reset_postdata();
endif;

// 上位カテゴリごとに2件表示
echo '<section class="top-categories-posts">';
echo '<h2>トップカテゴリー毎の記事</h2>';

$categories = get_categories([
    'parent' => 0,
    'hide_empty' => true,
    'number' => 3, // 必要なら3つに限定
]);

foreach ($categories as $cat) :
    echo '<section class="category-posts">';
    echo '<h3>' . esc_html($cat->name) . '</h3>';
    echo '<div class="post-grid">';

    $cat_query = new WP_Query([
        'cat' => $cat->term_id,
        'posts_per_page' => 2,
        'post_status' => 'publish',
    ]);
    if ($cat_query->have_posts()) :
        while ($cat_query->have_posts()) : $cat_query->the_post();
        ?>
            <div class="grid-item">
                <div class="post-category">
                    <p><?php the_category(', '); ?></p>
                </div>

                <a href="<?php the_permalink(); ?>">
                    <div class="post-thumbnail">
                        <?php Integlight_PostThumbnail::render(); ?>
                    </div>

                    <h2><?php
                        $tmpTitle = get_the_title();
                        echo esc_html(
                            (strlen($tmpTitle) > 25) ? wp_html_excerpt($tmpTitle, 25) . ' ...' : $tmpTitle
                        );
                        ?></h2>

                    <p class="post-excerpt">
                        <?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_content()), 78, ' ...')); ?>
                    </p>

                    <div class="post-meta">
                        <p class="post-date"><?php echo __("Published on", "integlight"); ?> : <?php echo get_the_date(); ?></p>
                    </div>
                </a>
            </div>
<?php
        endwhile;
        wp_reset_postdata();
    else :
        echo '<p>投稿がありません。</p>';
    endif;


    echo '</div>'; // .post-grid
    echo '<p class="more-link"><a href="' . esc_url(get_category_link($cat->term_id)) . '">もっと見る</a></p>';
    echo '</section>';


endforeach;

echo '</section>';
