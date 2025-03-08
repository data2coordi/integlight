<?php get_header(); ?>

<?php
integlight_display_headerContents();

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

                    <!-- カテゴリ表示 -->
                    <div class="post-category">
                        <p>Category: <?php the_category(', '); ?></p>
                    </div>


                    <!-- タイトルをタイルの左上に大きく表示 -->

                    <h2><?php
                        $title = get_the_title();
                        echo (mb_strlen($title) > 19) ? mb_substr($title, 0, 19) . ' ...' : $title;
                        ?></h2>

                    <!-- 本文の先頭200文字を表示 -->
                    <p class="post-excerpt"><?php echo wp_trim_words(get_the_content(), 78, ' ...'); ?></p>

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

<?php get_footer(); ?>