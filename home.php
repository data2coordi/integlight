<?php get_header(); ?>

<?php
if (is_front_page() != false) {
    integlight_display_headerContents();
}

$pattern = isset($_GET['pattern']) ? $_GET['pattern'] : '';

if ($pattern === '0') {
    get_template_part('template-parts/content-home', 'pattern0');
} elseif ($pattern === '2') {
    get_template_part('template-parts/content-home', 'pattern2');
} else {
    get_template_part('template-parts/content-home', 'pattern2');
}

?>
<main id="primary">

</main>
<?php get_footer(); ?>