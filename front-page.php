<?php
/**
 * Front Page Template for FrockMEE Theme
 *
 * This template displays the homepage with various sections for the ladies frock shop.
 *
 * @package WoocomProduct
 */

get_header(); ?>

<main id="main" class="site-main">
    <!-- Hero Section -->
    <section class="hero-section" style="background-image: url('<?php echo esc_url( get_theme_mod( 'hero_background_image', '' ) ); ?>');">
        <div class="hero-content">
            <div class="hero-logo-container">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/logo/logo-with-name.png" alt="FrockMEE Logo" class="hero-logo">
            </div>
            <h1 class="hero-title">Dress Yourself Beautifully</h1>
            <p class="hero-subtitle">Discover casual, party, office & summer frocks</p>
            <div class="hero-buttons">
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-primary">Shop Now</a>
                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) . '?orderby=date' ); ?>" class="btn btn-secondary">New Arrivals</a>
            </div>
        </div>
    </section>

    <!-- Shop by Category Section -->
    <section class="categories-section" aria-labelledby="categories-heading">
        <div class="container">
            <div class="section-header">
                <h2 id="categories-heading" class="section-title">Shop by Category</h2>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="view-all-link">View All Categories</a>
            </div>

            <ul class="categories-grid" role="list">
                <?php
                $categories = get_terms( array(
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => true,
                    'number'     => 6, // Limit to 6 for grid
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                ) );

                if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
                    foreach ( $categories as $category ) {
                        $category_id = $category->term_id;
                        $category_name = $category->name;
                        $category_slug = $category->slug;
                        $category_link = get_term_link( $category );
                        $category_count = $category->count;
                        $thumbnail_id = get_term_meta( $category_id, 'thumbnail_id', true );
                        $category_image = $thumbnail_id ? wp_get_attachment_image_src( $thumbnail_id, 'medium' ) : null;
                        $category_image_url = $category_image ? $category_image[0] : wc_placeholder_img_src();
                        $is_featured = get_term_meta( $category_id, 'featured_category', true );
                        ?>
                        <li class="category-item <?php echo $is_featured ? 'featured' : ''; ?>" data-category-id="<?php echo esc_attr( $category_id ); ?>" data-category-name="<?php echo esc_attr( $category_name ); ?>" data-category-slug="<?php echo esc_attr( $category_slug ); ?>">
                            <a href="<?php echo esc_url( $category_link ); ?>" class="category-link" aria-label="Shop <?php echo esc_attr( $category_name ); ?> category with <?php echo esc_attr( $category_count ); ?> products">
                                <div class="category-image-wrapper">
                                    <img src="<?php echo esc_url( $category_image_url ); ?>"
                                         alt="<?php echo esc_attr( $category_name ); ?> category"
                                         class="category-image"
                                         loading="lazy"
                                         data-src="<?php echo esc_url( $category_image_url ); ?>">
                                    <?php if ( $is_featured ) : ?>
                                        <span class="category-badge">Featured</span>
                                    <?php endif; ?>
                                </div>
                                <div class="category-content">
                                    <h3 class="category-title"><?php echo esc_html( $category_name ); ?></h3>
                                    <p class="category-meta"><?php echo esc_html( $category_count ); ?> products</p>
                                </div>
                            </a>
                        </li>
                        <?php
                    }
                } else {
                    echo '<li class="no-categories"><p>No categories found.</p></li>';
                }
                ?>
            </ul>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products-section">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <div class="products-grid">
                <?php
                $featured_products = wc_get_products( array(
                    'limit'     => 6,
                    'featured'  => true,
                    'status'    => 'publish',
                ) );

                if ( ! empty( $featured_products ) ) {
                    woocommerce_product_loop_start();
                    foreach ( $featured_products as $product ) {
                        $post_object = get_post( $product->get_id() );
                        setup_postdata( $GLOBALS['post'] =& $post_object );
                        echo '<div class="product-item featured-product">';
                        wc_get_template_part( 'content', 'product' );
                        echo '<span class="product-badge featured-badge">Featured</span>';
                        echo '</div>';
                    }
                    woocommerce_product_loop_end();
                } else {
                    echo '<p>No featured products found.</p>';
                }
                wp_reset_postdata();
                ?>
            </div>
        </div>
    </section>

    <!-- New Arrivals Section -->
    <section class="new-arrivals-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">New Arrivals</h2>
                <div class="section-actions">
                    <button class="filter-toggle" aria-controls="new-arrivals-filters" aria-expanded="false">Filter & Sort</button>
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="view-all-link">View All</a>
                </div>
            </div>

            <!-- Promotion Banner -->
            <div class="promotion-banner">
                <div class="banner-content">
                    <h3>✨ Fresh Collection Alert!</h3>
                    <p>Discover the latest trends in ladies' fashion</p>
                </div>
                <div class="banner-cta">
                    <a href="#new-arrivals-grid" class="btn btn-secondary">Shop New</a>
                </div>
            </div>

            <!-- Filters (collapsible) -->
            <div id="new-arrivals-filters" class="filters-panel" hidden>
                <div class="filter-group">
                    <label for="sort-select">Sort by:</label>
                    <select id="sort-select" class="filter-select">
                        <option value="date">Newest First</option>
                        <option value="price">Price: Low to High</option>
                        <option value="price-desc">Price: High to Low</option>
                        <option value="rating">Highest Rated</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Price Range:</label>
                    <div class="price-range">
                        <input type="range" id="price-min" min="0" max="500" value="0">
                        <input type="range" id="price-max" min="0" max="500" value="500">
                        <span id="price-display">$0 - $500</span>
                    </div>
                </div>
            </div>

            <div id="new-arrivals-grid" class="products-grid new-arrivals-grid">
                <?php
                $new_arrivals = wc_get_products( array(
                    'limit'   => 8,
                    'orderby' => 'date',
                    'order'   => 'DESC',
                    'status'  => 'publish',
                ) );

                if ( ! empty( $new_arrivals ) ) {
                    foreach ( $new_arrivals as $product ) {
                        $product_id = $product->get_id();
                        $product_link = get_permalink( $product_id );
                        $product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'medium' );
                        $product_image_url = $product_image ? $product_image[0] : wc_placeholder_img_src();
                        $product_gallery = $product->get_gallery_image_ids();
                        $alt_image = !empty($product_gallery) ? wp_get_attachment_image_src( $product_gallery[0], 'medium' )[0] : $product_image_url;
                        $product_name = $product->get_name();
                        $product_price = $product->get_price_html();
                        $is_on_sale = $product->is_on_sale();
                        $is_featured = $product->is_featured();
                        $attributes = $product->get_attributes();
                        $size_attr = isset($attributes['pa_size']) ? $attributes['pa_size']->get_options() : [];
                        $color_attr = isset($attributes['pa_color']) ? $attributes['pa_color']->get_options() : [];
                        $attr_line = '';
                        if (!empty($size_attr)) $attr_line .= 'Sizes: ' . implode(', ', array_slice($size_attr, 0, 3));
                        if (!empty($color_attr)) $attr_line .= (!empty($attr_line) ? ' | ' : '') . 'Colors: ' . implode(', ', array_slice($color_attr, 0, 3));
                        ?>
                        <div class="product-card" data-product-id="<?php echo esc_attr( $product_id ); ?>">
                            <div class="product-image-wrapper">
                                <a href="<?php echo esc_url( $product_link ); ?>" class="product-link">
                                    <img src="<?php echo esc_url( $product_image_url ); ?>"
                                         alt="<?php echo esc_attr( $product_name ); ?>"
                                         class="product-image primary-image"
                                         loading="lazy">
                                    <img src="<?php echo esc_url( $alt_image ); ?>"
                                         alt="<?php echo esc_attr( $product_name ); ?> - alternate view"
                                         class="product-image alt-image"
                                         loading="lazy">
                                </a>
                                <?php if ( $is_on_sale ) : ?>
                                    <span class="product-badge sale-badge">Sale</span>
                                <?php endif; ?>
                                <?php if ( $is_featured ) : ?>
                                    <span class="product-badge featured-badge">Featured</span>
                                <?php endif; ?>
                                <span class="product-badge new-badge">New</span>
                                <button class="quick-view-btn" data-product-id="<?php echo esc_attr( $product_id ); ?>" aria-label="Quick view <?php echo esc_attr( $product_name ); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
                                </button>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="<?php echo esc_url( $product_link ); ?>"><?php echo esc_html( $product_name ); ?></a>
                                </h3>
                                <div class="product-price"><?php echo $product_price; ?></div>
                                <?php if ( !empty($attr_line) ) : ?>
                                    <div class="product-attributes"><?php echo esc_html( $attr_line ); ?></div>
                                <?php endif; ?>
                                <div class="product-actions">
                                    <?php
                                    if ( $product->is_purchasable() && $product->is_in_stock() ) {
                                        echo '<a href="' . esc_url( $product_link ) . '" class="view-btn btn" aria-label="View ' . esc_attr( $product_name ) . '">View</a>';
                                    } else {
                                        echo '<span class="out-of-stock">Out of Stock</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p>No new arrivals found.</p>';
                }
                wp_reset_postdata();
                ?>
            </div>
        </div>
    </section>

    <!-- Why Choose FrockMEE Section -->
    <section class="why-choose-section">
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
                    <p>Hassle-free returns and quick shipping to get your frocks faster.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Find Your Perfect Frock Today</h2>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-primary">Shop Now</a>
        </div>
    </section>
</main>

<?php get_footer(); ?>