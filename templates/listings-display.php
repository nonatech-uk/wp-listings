<?php
/**
 * Parish Listings Display Template
 *
 * Variables available:
 * - $listings: WP_Query object
 * - $atts: Shortcode attributes array
 * - $paged: Current page number
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$layout_class = 'parish-listings-layout-' . esc_attr( $atts['layout'] );
?>

<div class="parish-listings-container <?php echo $layout_class; ?>">
    <?php if ( $listings->have_posts() ) : ?>
        <?php
        $item_index = 0;
        while ( $listings->have_posts() ) :
            $listings->the_post();
            $item_index++;

            // Get meta values
            $url     = get_post_meta( get_the_ID(), '_listing_url', true );
            $phone   = get_post_meta( get_the_ID(), '_listing_phone', true );
            $email   = get_post_meta( get_the_ID(), '_listing_email', true );
            $address = get_post_meta( get_the_ID(), '_listing_address', true );

            // Determine image position class
            $image_position = 'parish-listing-image-left';
            if ( $atts['layout'] === 'right' ) {
                $image_position = 'parish-listing-image-right';
            } elseif ( $atts['layout'] === 'alternate' ) {
                $image_position = ( $item_index % 2 === 0 ) ? 'parish-listing-image-right' : 'parish-listing-image-left';
            }

            $has_image = $atts['show_image'] && has_post_thumbnail();
            ?>

            <div class="parish-listing-item <?php echo esc_attr( $image_position ); ?><?php echo $has_image ? '' : ' parish-listing-no-image'; ?>">
                <?php if ( $has_image ) : ?>
                    <div class="parish-listing-image">
                        <?php the_post_thumbnail( 'medium_large', array( 'alt' => get_the_title() ) ); ?>
                    </div>
                <?php endif; ?>

                <div class="parish-listing-content">
                    <h3 class="parish-listing-title"><?php the_title(); ?></h3>

                    <?php if ( get_the_content() ) : ?>
                        <div class="parish-listing-description">
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ( $atts['show_phone'] && $phone ) || ( $atts['show_email'] && $email ) || ( $atts['show_address'] && $address ) || ( $atts['show_url'] && $url ) ) : ?>
                        <div class="parish-listing-meta">
                            <?php if ( $atts['show_url'] && $url ) : ?>
                                <span class="parish-listing-url">
                                    <strong><?php _e( 'Website:', 'parish-listings' ); ?></strong>
                                    <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html( preg_replace( '#^https?://#', '', $url ) ); ?>
                                    </a>
                                </span>
                            <?php endif; ?>

                            <?php if ( $atts['show_phone'] && $phone ) : ?>
                                <span class="parish-listing-phone">
                                    <strong><?php _e( 'Phone:', 'parish-listings' ); ?></strong>
                                    <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>">
                                        <?php echo esc_html( $phone ); ?>
                                    </a>
                                </span>
                            <?php endif; ?>

                            <?php if ( $atts['show_email'] && $email ) : ?>
                                <span class="parish-listing-email">
                                    <strong><?php _e( 'Email:', 'parish-listings' ); ?></strong>
                                    <a href="mailto:<?php echo esc_attr( $email ); ?>">
                                        <?php echo esc_html( $email ); ?>
                                    </a>
                                </span>
                            <?php endif; ?>

                            <?php if ( $atts['show_address'] && $address ) : ?>
                                <span class="parish-listing-address">
                                    <strong><?php _e( 'Address:', 'parish-listings' ); ?></strong>
                                    <?php echo nl2br( esc_html( $address ) ); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php endwhile; ?>

        <?php
        // Pagination
        if ( $atts['per_page'] > 0 && $listings->max_num_pages > 1 ) :
            $current_url = remove_query_arg( 'listings_page' );
            ?>
            <nav class="parish-listings-pagination">
                <?php if ( $paged > 1 ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'listings_page', $paged - 1, $current_url ) ); ?>" class="parish-listings-prev">
                        &laquo; <?php _e( 'Previous', 'parish-listings' ); ?>
                    </a>
                <?php endif; ?>

                <span class="parish-listings-page-info">
                    <?php printf( __( 'Page %1$d of %2$d', 'parish-listings' ), $paged, $listings->max_num_pages ); ?>
                </span>

                <?php if ( $paged < $listings->max_num_pages ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'listings_page', $paged + 1, $current_url ) ); ?>" class="parish-listings-next">
                        <?php _e( 'Next', 'parish-listings' ); ?> &raquo;
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

    <?php else : ?>
        <p class="parish-listings-none"><?php _e( 'No listings found.', 'parish-listings' ); ?></p>
    <?php endif; ?>
</div>
