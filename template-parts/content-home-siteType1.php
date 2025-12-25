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
        <div id="siteType1-post-grid" class="post-grid">
            <?php while (have_posts()) : the_post(); ?>
                <div class="grid-item">


                    <!-- カテゴリ表示 -->
                    <div class="post-category">
                        <p><?php the_category(', '); ?></p>
                    </div>


                    <a href="<?php the_permalink(); ?>">
                        <div class="post-thumbnail">
                            <?php

                            $attr = Integlight_getAttr_byImageCount::getBodyImageAttr($wp_query->current_post);
                            Integlight_PostThumbnail::render(null, 'medium', '', $attr);

                            ?>
                        </div>
                        <!-- タイトルをタイルの左上に大きく表示 -->

                        <h2><?php
                            $tmpTitle = get_the_title();
                            echo esc_html(
                                (mb_strlen($tmpTitle) > 42)
                                    ? wp_html_excerpt($tmpTitle, 42) . esc_html__(' ...', 'integlight')
                                    : $tmpTitle
                            );
                            ?></h2>



                        <!-- 下部に日付、カテゴリ、タグを表示 -->
                        <div class="post-meta">
                            <p class="post-date"><?php echo __("Published on", "integlight"); ?> : <?php echo get_the_date(); ?></p>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>


        </div>
        <?php
        the_posts_pagination( //ページャーを出力
            array(
                'mid_size' => 2, // 現在ページの左右に表示するページ番号の数
                'prev_next' => true,
                'prev_text' => '<i class="fa-regular fa-square-caret-left"></i> ' . esc_html__('prev', 'integlight'),
                'next_text' => esc_html__('next', 'integlight') . ' <i class="fa-regular fa-square-caret-right"></i>',
                'type' => 'plain', // 戻り値の指定 (plain/list)
                'format'    => '?paged=%#%#siteType1-post-grid', // ここでアンカー付与
            )
        );
        ?>
    <?php else : ?>
        <p><?php esc_html_e('No posts found.', 'integlight'); ?></p>
    <?php endif; ?>

    <?php
