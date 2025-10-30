<div class="grid-item">
    <div class="post-category">
        <p><?php the_category(', '); ?></p>
    </div>

    <a href="<?php the_permalink(); ?>">
        <div class="post-thumbnail">
            <?php
            // 上位から渡された fetchpriority をそのまま使用
            if (!empty($args['attr'])) {
                Integlight_PostThumbnail::render(null, 'medium', '', $args['attr']);
            } else {
                Integlight_PostThumbnail::render(null, 'medium', '');
            }

            ?>
        </div>

        <h2>
            <?php
            $tmpTitle = get_the_title();
            echo esc_html(
                (strlen($tmpTitle) > 25) ? wp_html_excerpt($tmpTitle, 25) . ' ...' : $tmpTitle
            );
            ?>
        </h2>

        <p class="post-excerpt">
            <?php echo Integlight_excerpt_trim(); ?>
        </p>

        <div class="post-meta">
            <p class="post-date">
                <?php echo __("Published on", "integlight"); ?> : <?php echo get_the_date(); ?>
            </p>
        </div>
    </a>
</div>