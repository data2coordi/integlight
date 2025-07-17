<?php get_header(); ?>

<?php
if (is_front_page() != false) {
    integlight_display_headerContents();
}


?>
<main id="primary">
    <?php
    // ▼ここにカテゴリ一覧を追加▼
    // ▼最上位＆第2階層カテゴリ一覧を表示▼
    $top_categories = get_categories(['parent' => 0, 'hide_empty' => false]);
    if ($top_categories) :
        echo '<div class="category-list">';
        foreach ($top_categories as $top_cat) {
            echo '<div class="category-item">';
            echo '<a href="' . esc_url(get_category_link($top_cat->term_id)) . '" class="category-link">';
            echo esc_html($top_cat->name);
            echo '</a>';

            // 子カテゴリを取得（第2階層）
            /*
            $child_cats = get_categories(['parent' => $top_cat->term_id, 'hide_empty' => false]);
            if ($child_cats) {
                echo '<div class="child-categories">';
                foreach ($child_cats as $child_cat) {
                    echo '<a href="' . esc_url(get_category_link($child_cat->term_id)) . '" class="child-category-link">';
                    echo esc_html($child_cat->name);
                    echo '</a>';
                }
                echo '</div>';
            }
                */


            echo '</div>'; // .category-item
        }
        echo '</div>'; // .category-list
    endif;
    ?>
    <?php if (have_posts()) : ?>
        <div class="post-grid">
            <?php while (have_posts()) : the_post(); ?>
                <div class="grid-item">

                    <div class="post-thumbnail">
                        <?php Integlight_PostThumbnail::render(); ?>
                    </div>

                    <!-- カテゴリ表示 -->
                    <div class="post-category">
                        <p><?php the_category(', '); ?></p>
                    </div>


                    <a href="<?php the_permalink(); ?>">

                        <!-- タイトルをタイルの左上に大きく表示 -->

                        <h2><?php
                            $tmpTitle = get_the_title();
                            echo esc_html(
                                (strlen($tmpTitle) > 25) ? wp_html_excerpt($tmpTitle, 25) . esc_html__(' ...', 'integlight') : $tmpTitle
                            );
                            ?></h2>

                        <!-- 本文の先頭200文字を表示 -->
                        <p class="post-excerpt">
                            <?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_content()), 78, esc_html__(' ...', 'integlight'))); ?>
                        </p>


                        <!-- 下部に日付、カテゴリ、タグを表示 -->
                        <div class="post-meta">
                            <p class="post-date"><?php echo __("Published on", "integlight"); ?> : <?php echo get_the_date(); ?></p>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <p><?php esc_html_e('No posts found.', 'integlight'); ?></p>
    <?php endif; ?>
</main>
<?php get_footer(); ?>