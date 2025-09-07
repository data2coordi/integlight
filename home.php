<?php get_header(); ?>

<?php
if (is_front_page() != false) {
    integlight_display_headerContents();
}
$home_type = get_theme_mod('integlight_hometype_setting', 'home1');
?>

<main id="primary" class=<?php echo $home_type; ?>>

    >
    <?php

    $cache = new Integlight_Cache_TemplatePart();
    $key = 'home_content_' . $home_type;
    $cache->displayTemplatePart($key, 'template-parts/content-home', $home_type);

    ?>


</main>
<?php get_footer(); ?>