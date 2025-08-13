<?php get_header(); ?>

<?php
if (is_front_page() != false) {
    integlight_display_headerContents();
}
?>
<main id="primary">

    <?php
    $home_type = get_theme_mod('integlight_hometype_setting', 'home1');

    get_template_part('template-parts/content-home', $home_type);






    ?>


</main>
<?php get_footer(); ?>