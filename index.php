<?php
get_header();
?>

<main id="main" class="site-main">
<?php do_action( 'woocomproduct_hero_banner' ); ?>

<!-- Shop by Category Section -->
<section class="shop-categories">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="categories-grid">
            <?php
            // Define the categories to display with their images
            $categories = array(
                'casual-frocks' => array(
                    'name' => 'Casual Frocks',
                    'image' => get_template_directory_uri() . '/assets/categories/casual-frocks.jpg'
                ),
                'party-frocks' => array(
                    'name' => 'Party Frocks',
                    'image' => get_template_directory_uri() . '/assets/categories/party-frocks.jpg'
                ),
                'office-formal-frocks' => array(
                    'name' => 'Office / Formal Frocks',
                    'image' => get_template_directory_uri() . '/assets/categories/office-formal-frocks.webp'
                ),
                'maxi-dresses' => array(
                    'name' => 'Maxi Dresses',
                    'image' => get_template_directory_uri() . '/assets/categories/maxi-dresses.jpg'
                ),
                'summer-frocks' => array(
                    'name' => 'Summer Frocks',
                    'image' => get_template_directory_uri() . '/assets/categories/summer-frocks.avif'
                )
            );

            foreach ( $categories as $slug => $category_data ) {
                $term = get_term_by( 'slug', $slug, 'product_cat' );
                if ( $term && ! is_wp_error( $term ) ) {
                    ?>
                    <div class="category-card">
                        <a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
                            <img src="<?php echo esc_url( $category_data['image'] ); ?>" alt="<?php echo esc_attr( $category_data['name'] ); ?>" />
                            <h3><?php echo esc_html( $category_data['name'] ); ?></h3>
                        </a>
                    </div>
                    <?php
                } else {
                    // Fallback: show category even if term doesn't exist
                    ?>
                    <div class="category-card">
                        <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
                            <img src="<?php echo esc_url( $category_data['image'] ); ?>" alt="<?php echo esc_attr( $category_data['name'] ); ?>" />
                            <h3><?php echo esc_html( $category_data['name'] ); ?></h3>
                        </a>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</section>

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

<!-- New Arrivals Section -->
<section class="new-arrivals">
    <div class="container">
        <h2 class="section-title">New Arrivals</h2>
        <div class="products-grid">
            <?php
            $args = array(
                'post_type'      => 'product',
                'posts_per_page' => 6,
                'orderby'        => 'date',
                'order'          => 'DESC'
            );
            $new_arrivals_query = new WP_Query( $args );

            if ( $new_arrivals_query->have_posts() ) {
                while ( $new_arrivals_query->have_posts() ) {
                    $new_arrivals_query->the_post();
                    global $product;
                    ?>
                    <div class="product-card">
                        <a href="<?php the_permalink(); ?>">
                            <?php echo woocommerce_get_product_thumbnail( 'medium' ); ?>
                        </a>
                        <div class="product-info">
                            <h3 class="product-title"><?php the_title(); ?></h3>
                            <div class="product-price"><?php echo $product->get_price_html(); ?></div>
                            <?php woocommerce_template_loop_add_to_cart(); ?>
                        </div>
                    </div>
                    <?php
                }
                wp_reset_postdata();
            } else {
                echo '<p>No products found.</p>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Why Choose FrockMEE Section -->
<section class="why-choose-frockmee">
    <div class="container">
        <h2 class="section-title">Why Choose FrockMEE</h2>
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">🌟</div>
                <h3>Premium Fabrics</h3>
                <p>High-quality materials that feel luxurious and last longer.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">👗</div>
                <h3>Perfect Fit for Every Body</h3>
                <p>Designed to flatter all body types with inclusive sizing.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">🚚</div>
                <h3>Easy Returns & Fast Delivery</h3>
                <p>Hassle-free returns and quick shipping to get you your frock fast.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section">
    <div class="container">
        <h2>Find Your Perfect Frock Today</h2>
        <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="cta-button">Shop Now</a>
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

</main>

<?php
get_footer();
?>
