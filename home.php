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
                                <img src="<?php echo esc_url($image['src']); ?>" alt="">
                            </div>
                    <?php endif;
                    endif; ?>

                    <!-- カテゴリ表示 -->
                    <div class="post-category">
                        <p>Category: <?php the_category(', '); ?></p>
                    </div>


                    <a href="<?php the_permalink(); ?>">

                        <!-- タイトルをタイルの左上に大きく表示 -->

                        <h2><?php
                            $tmpTitle = get_the_title();
                            echo htmlspecialchars(
                                (mb_strlen($tmpTitle, 'UTF-8') > 19) ? mb_substr($tmpTitle, 0, 19) . ' ...' : $tmpTitle,
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?></h2>

                        <!-- 本文の先頭200文字を表示 -->
                        <p class="post-excerpt">
                            <?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_content()), 78, ' ...')); ?>
                        </p>


                        <!-- 下部に日付、カテゴリ、タグを表示 -->
                        <div class="post-meta">
                            <p class="post-date">Published on: <?php echo get_the_date(); ?></p>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <p>No posts found.</p>
    <?php endif; ?>
</main>
<?php get_footer(); ?>