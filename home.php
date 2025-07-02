<?php get_header(); ?>

<?php
if (is_front_page() != false) {
    integlight_display_headerContents();
}

?>
<main id="primary">

    <?php if (have_posts()) : ?>
        <div class="post-grid">
            <?php while (have_posts()) : the_post(); ?>
                <div class="grid-item">


                    <?php Integlight_PostThumbnail::render(); ?>

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