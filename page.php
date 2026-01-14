<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main id="site-content" class="site-main container">
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) : the_post();
            echo '<article id="post-' . get_the_ID() . '" ' . get_post_class() . '>';

            // Page title
            echo '<header class="entry-header">';
            the_title( '<h1 class="entry-title">', '</h1>' );
            echo '</header>';

            // Page content (this will render the Cart shortcode)
            echo '<div class="entry-content">';
            the_content();
            echo '</div>';

            echo '</article>';
        endwhile;
    else :
        echo '<p>' . esc_html__( 'No content found.', 'woocomproduct' ) . '</p>';
    endif;
    ?>
</main>

<?php
get_footer();
