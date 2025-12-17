<?php

/********************************************************************/
/* ブロックエディター用のパターン登録s*/
/********************************************************************/


/**
 * Class Integlight_blockItemPattern
 *
 * Registers custom block styles and patterns for the theme.
 */
class Integlight_blockItemPattern
{

  /**
   * Constructor. Hooks into WordPress init action.
   */
  public function __construct()
  {
    // Both styles and patterns should be registered during the 'init' action.
    add_action('init', [$this, 'register_assets']);
  }

  /**
   * Registers both block styles and patterns.
   * Action: init
   */
  public function register_assets()
  {
    $this->register_block_styles();
    $this->register_block_patterns();
  }

  /**
   * Registers custom block styles.
   * Called by register_assets during the 'init' action.
   */
  private function register_block_styles()
  {
    register_block_style(
      'core/heading',
      [
        'name'         => 'no-underline',
        'label'        => __('No Underline', 'integlight'), // Use __() for translatable strings
        'inline_style' => '.wp-block-heading.is-style-no-underline::after { display: none !important; }',
      ]
    );
    // Add more block styles here if needed
  }

  /**
   * Registers custom block patterns.
   * Called by register_assets during the 'init' action.
   */
  private function register_block_patterns()
  {
    // Check if the function exists before calling it (good practice)
    if (function_exists('register_block_pattern')) {
      register_block_pattern(
        'integlight/media-and-text-pattern',
        array(
          'title'       => __('media and text', 'integlight'),
          'categories'  => array('featured'),
          'content'     => '
<!-- wp:media-text {"mediaPosition":"left","mediaType":"image","mediaLink":"","isStackedOnMobile":true,"verticalAlignment":"center"} -->
<div class="wp-block-media-text is-stacked-on-mobile is-vertically-aligned-center">
  <figure class="wp-block-media-text__media">
    <img src="' . esc_url(get_template_directory_uri() . '/assets/pattern-woman1.webp') . '" alt="Firefly image" />
  </figure>
  <div class="wp-block-media-text__content">
    <!-- wp:heading {"level":4} -->
    <h4 class="wp-block-heading">Director</h4>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>"Less experienced and uneasy about design and development, struggling to keep projects on track." <br />
    One common challenge for less experienced directors is lacking sufficient knowledge in specialized design.</p>
    <!-- /wp:paragraph -->
  </div>
</div>
<!-- /wp:media-text -->
        ',
        )
      );
      // Add more block patterns here if needed




      register_block_pattern(
        'integlight/text and media',
        array(
          'title'       => __('text and media', 'integlight'),
          'categories'  => array('featured'),
          'content'     => '
<!-- wp:media-text {"mediaType":"image","mediaPosition":"right","mediaId":0,"mediaUrl":"/assets/pattern-woman2.webp","isStackedOnMobile":true} -->
<div class="wp-block-media-text has-media-on-the-right is-stacked-on-mobile">
  <div class="wp-block-media-text__content">
    <!-- wp:heading {"level":4} -->
    <h4 class="wp-block-heading">For Production Agencies</h4>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>
      "We want to deliver WordPress sites tailored to our clients\' needs, but lack the in-house expertise and technical skills to do so."<br><br>
      Many production agencies face the challenge 	.
    </p>
    <!-- /wp:paragraph -->
  </div>
  <figure class="wp-block-media-text__media">
<img src="' . esc_url(get_template_directory_uri() . '/assets/pattern-woman2.webp') . '" alt="Firefly image" />  </figure>
</div>
<!-- /wp:media-text -->
        ',
        )
      );













      register_block_pattern(
        'integlight/big-quote',
        array(
          'title'       => __('big quote', 'integlight'),
          'categories'  => array('featured'),

          'content'     => '
<!-- wp:quote -->
<blockquote class="wp-block-quote">
  <!-- wp:paragraph -->
  <p><strong>"When I first used [integlight], I was amazed. It’s intuitive to use, yet the site loads incredibly fast. This ensures readers can enjoy articles without any stress."</strong></p>
  <!-- /wp:paragraph -->

  <!-- wp:paragraph -->
  <p><strong>"What I especially like is the 8 color variations. They are all sophisticated, and just by choosing one, my blog\'s impression becomes much more stylish. Even someone like me, who lacks design confidence, could easily create a consistent site."</strong></p>
  <!-- /wp:paragraph -->

  <!-- wp:paragraph -->
  <p><strong>"Moreover, by adding [aurora-design-blocks], I could use sliders and balloon features, which greatly enhanced article expressiveness. And it only costs 1,980 yen — truly amazing. I’m glad I found it!"</strong></p>
  <!-- /wp:paragraph -->
</blockquote>
<!-- /wp:quote -->
',
        )
      );

      register_block_pattern(
        'integlight/strong-table',
        array(
          'title'       => __('strong table', 'integlight'),
          'categories'  => array('featured'),

          'content'     => '
<!-- wp:table {"hasFixedLayout":true} -->
<figure class="wp-block-table">
<table class="has-fixed-layout">
  <thead>
    <tr>
      <th>Common Mistake</th>
      <th>Solution</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>No event is triggered</td>
      <td>Check for <code>Click ID</code> typos or insufficient tag wait time</td>
    </tr>
    <tr>
      <td>Event not showing</td>
      <td>Twitter embed buttons cannot be captured by GTM</td>
    </tr>
    <tr>
      <td>No click event detected</td>
      <td>Likely blocked by JavaScript or iframe handling</td>
    </tr>
  </tbody>
</table>
</figure>
<!-- /wp:table -->
',
        )
      );

      register_block_pattern(
        'integlight/post-columns',
        array(
          'title'       => __('Post Columns – Morning Routine', 'integlight'),
          'categories'  => array('featured'),

          'content'     => <<<HTML
<!-- wp:columns -->
<div class="wp-block-columns">
  <!-- wp:column {"style":{"color":{"background":"#7fdde7"},"border":{"radius":"10px"},"spacing":{"padding":{"right":"var:preset|spacing|40","left":"var:preset|spacing|40"}}}} -->
  <div class="wp-block-column has-background" style="border-radius:10px;background-color:#7fdde7;padding-right:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
    <!-- wp:heading {"className":"is-style-no-underline"} -->
    <h2 class="wp-block-heading is-style-no-underline">Post 01</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>On Sunday mornings, I wake up a bit later than on weekdays. No alarm. Just the soft morning light filtering through the curtains, gently saying “you can wake up now.”</p>
    <!-- /wp:paragraph -->
  </div>
  <!-- /wp:column -->

  <!-- wp:column {"style":{"color":{"background":"#7fdde7"},"border":{"radius":"10px"},"spacing":{"padding":{"right":"var:preset|spacing|40","left":"var:preset|spacing|40"}}}} -->
  <div class="wp-block-column has-background" style="border-radius:10px;background-color:#7fdde7;padding-right:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
    <!-- wp:heading {"className":"is-style-no-underline"} -->
    <h2 class="wp-block-heading is-style-no-underline">Post 02</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>I move to the living room and boil some water. Since I’ve been cutting back on caffeine, I started drinking rooibos tea instead. It’s mild, and feels healthy somehow—just the way I like it.</p>
    <!-- /wp:paragraph -->
  </div>
  <!-- /wp:column -->
</div>
<!-- /wp:columns -->
HTML
        )
      );

      $icon_url = esc_url(get_template_directory_uri() . '/assets/icon.webp');

      register_block_pattern(
        'integlight/promo-box',
        array(
          'title'      => __('Integlight Promo Box', 'integlight'),
          'categories' => array('featured'),
          'content'    => <<<HTML
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
  <!-- wp:group {"style":{"color":{"background":"#d9ffed","text":"#566b65"},"border":{"radius":"10px","color":"#566b65","width":"1px"},"spacing":{"padding":{"top":"0","bottom":"0","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}},"elements":{"link":{"color":{"text":"#566b65"}}}},"layout":{"type":"constrained"}} -->
  <div class="wp-block-group has-border-color has-text-color has-background has-link-color" style="border-color:#566b65;border-width:1px;border-radius:10px;color:#566b65;background-color:#d9ffed;padding-top:0;padding-right:var(--wp--preset--spacing--50);padding-bottom:0;padding-left:var(--wp--preset--spacing--50)">
    
    <!-- wp:image {"width":"100px","height":"100px","scale":"cover","sizeSlug":"full","linkDestination":"none","align":"center"} -->
    <figure class="wp-block-image aligncenter size-full is-resized">
      <img src="{$icon_url}" alt="" style="object-fit:cover;width:100px;height:100px" />
    </figure>
    <!-- /wp:image -->

    <!-- wp:heading {"textAlign":"center","className":"is-style-no-underline"} -->
    <h2 class="wp-block-heading has-text-align-center is-style-no-underline">Integlight</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"align":"center"} -->
    <p class="has-text-align-center">
      Build a professional website for free.<br>
      The “Integlight” theme, officially approved by WordPress, combines sleek design with lightning-fast performance.<br>
      No complex settings. No hassle. Just launch your SEO-ready digital asset today.
    </p>
    <!-- /wp:paragraph -->
  </div>
  <!-- /wp:group -->
</div>
<!-- /wp:group -->
HTML
        )
      );
    }
  }
}

// Instantiate the class to initialize the functionality
new Integlight_blockItemPattern();




/********************************************************************/
/* ブロックエディター用のパターン登録e*/
/********************************************************************/
