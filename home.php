<?php get_header(); ?>

<?php
integlight_display_slider_or_image();

?>
<?php if (have_posts()) : ?>
    <div class="post-grid">
        <?php while (have_posts()) : the_post(); ?>
            <div class="grid-item">
                <a href="<?php the_permalink(); ?>">
                    <?php
                    // キャッチ画像があるか確認
                    if (has_post_thumbnail()) : ?>
                        <div class="post-thumbnail">
                            <?php the_post_thumbnail('medium'); ?>
                        </div>
                        <?php else :
                        // コンテンツから最初の画像を取得して表示
                        $content = get_the_content();
                        preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $image);
                        if (! empty($image)) : ?>
                            <div class="post-thumbnail">
                                <img src="<?php echo esc_url($image['src']); ?>" alt="First image from the post">
                            </div>
                    <?php endif;
                    endif; ?>

                    <!-- タイトルをタイルの左上に大きく表示 -->
                    <h2><?php the_title(); ?></h2>

                    <!-- 本文の先頭200文字を表示 -->
                    <p class="post-excerpt"><?php echo wp_trim_words(get_the_content(), 100, '...'); ?></p>

                    <!-- 下部に日付、カテゴリ、タグを表示 -->
                    <div class="post-meta">
                        <p class="post-date">Published on: <?php echo get_the_date(); ?></p>
                        <p class="post-category">Category: <?php the_category(', '); ?></p>
                        <p class="post-tags">Tags: <?php the_tags('', ', '); ?></p>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
<?php else : ?>
    <p>No posts found.</p>
<?php endif; ?>

<?php get_footer(); ?>