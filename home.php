<?php get_header(); ?>

<?php
if (is_front_page() != false) {
    integlight_display_headerContents();
}

if (true) {
    get_template_part('template-parts/content-home', 'pattern1');
} elseif (false) {
    get_template_part('template-parts/content-home', 'pattern2');
} else {
    get_template_part('template-parts/content-home', 'pattern0');
}

?>
<main id="primary">

</main>
<?php get_footer(); ?>