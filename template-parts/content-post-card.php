<div class="grid-item">
    <div class="post-category">
        <p><?php the_category(', '); ?></p>
    </div>

    <a href="<?php the_permalink(); ?>">
        <div class="post-thumbnail">
            <?php Integlight_PostThumbnail::render(); ?>
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
            <?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_content()), 78, ' ...')); ?>
        </p>

        <div class="post-meta">
            <p class="post-date">
                <?php echo __("Published on", "integlight"); ?> : <?php echo get_the_date(); ?>
            </p>
        </div>
    </a>
</div>