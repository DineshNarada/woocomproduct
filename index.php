<?php
get_header();
?>

<?php do_action( 'woocomproduct_hero_banner' ); ?>

<!-- Featured Products Section -->
<section class="featured-products">
    <div class="container">
        <h2 class="section-title">Featured Products</h2>
        <div class="products-grid">
            <?php
            // Get featured products
            $featured_products = wc_get_featured_product_ids();
            if ( ! empty( $featured_products ) ) {
                $args = array(
                    'post_type'      => 'product',
                    'posts_per_page' => 8,
                    'post__in'       => $featured_products,
                    'orderby'        => 'rand'
                );
                $featured_query = new WP_Query( $args );

                if ( $featured_query->have_posts() ) {
                    while ( $featured_query->have_posts() ) {
                        $featured_query->the_post();
                        global $product;
                        ?>
                        <div class="product-card">
                            <a href="<?php the_permalink(); ?>">
                                <?php echo woocommerce_get_product_thumbnail( 'medium' ); ?>
                            </a>
                            <div class="product-info">
                                <h3 class="product-title"><?php the_title(); ?></h3>
                                <div class="product-price"><?php echo $product->get_price_html(); ?></div>
                                <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="add-to-cart-btn" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>" data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>">
                                    <?php echo esc_html( $product->add_to_cart_text() ); ?>
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                    wp_reset_postdata();
                }
            } else {
                // Fallback: show latest products if no featured
                $args = array(
                    'post_type'      => 'product',
                    'posts_per_page' => 8,
                    'orderby'        => 'date',
                    'order'          => 'DESC'
                );
                $latest_query = new WP_Query( $args );

                if ( $latest_query->have_posts() ) {
                    while ( $latest_query->have_posts() ) {
                        $latest_query->the_post();
                        global $product;
                        ?>
                        <div class="product-card">
                            <a href="<?php the_permalink(); ?>">
                                <?php echo woocommerce_get_product_thumbnail( 'medium' ); ?>
                            </a>
                            <div class="product-info">
                                <h3 class="product-title"><?php the_title(); ?></h3>
                                <div class="product-price"><?php echo $product->get_price_html(); ?></div>
                                <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="add-to-cart-btn" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>" data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>">
                                    <?php echo esc_html( $product->add_to_cart_text() ); ?>
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                    wp_reset_postdata();
                }
            }
            ?>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="categories-grid">
            <?php
            $product_categories = get_terms( array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => true,
                'number'     => 6,
                'orderby'    => 'count',
                'order'      => 'DESC'
            ) );

            if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
                foreach ( $product_categories as $index => $category ) {
                    $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
                    $image_url = wp_get_attachment_url( $thumbnail_id );
                    if ( ! $image_url ) {
                        $image_url = wc_placeholder_img_src();
                    }
                    ?>
                    <a href="<?php echo get_term_link( $category ); ?>" class="category-card" style="background-image: url('<?php echo esc_url( $image_url ); ?>'); --category-index: <?php echo $index; ?>;">
                        <div class="category-content">
                            <h3 class="category-title"><?php echo esc_html( $category->name ); ?></h3>
                            <p class="category-count"><?php echo esc_html( $category->count ); ?> products</p>
                        </div>
                    </a>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="newsletter-content">
        <h2 class="newsletter-title">Stay Updated</h2>
        <p class="newsletter-subtitle">Subscribe to our newsletter for the latest products and exclusive offers.</p>
        <form class="newsletter-form" id="newsletter-form" action="#" method="post">
            <input type="email" name="email" placeholder="Enter your email address" class="newsletter-input" required aria-label="Email address">
            <button type="submit" class="newsletter-btn" id="newsletter-submit">
                <span class="btn-text">Subscribe</span>
                <span class="btn-loading" style="display: none;">⏳</span>
            </button>
        </form>
        <div id="newsletter-message" class="newsletter-message" role="alert" aria-live="polite"></div>
    </div>
</section>

<?php
get_footer();
?>
