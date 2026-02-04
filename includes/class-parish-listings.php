<?php
/**
 * Parish Listings Core Class
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Parish_Listings {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'init', array( $this, 'register_taxonomy' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_parish_listing', array( $this, 'save_meta_box' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_ajax_parish_listings_paginate', array( $this, 'ajax_paginate' ) );
        add_action( 'wp_ajax_nopriv_parish_listings_paginate', array( $this, 'ajax_paginate' ) );
        add_shortcode( 'parish_listings', array( $this, 'shortcode_handler' ) );
    }

    /**
     * Register Custom Post Type
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x( 'Listings', 'Post type general name', 'parish-listings' ),
            'singular_name'         => _x( 'Listing', 'Post type singular name', 'parish-listings' ),
            'menu_name'             => _x( 'Parish Listings', 'Admin Menu text', 'parish-listings' ),
            'add_new'               => __( 'Add New', 'parish-listings' ),
            'add_new_item'          => __( 'Add New Listing', 'parish-listings' ),
            'edit_item'             => __( 'Edit Listing', 'parish-listings' ),
            'new_item'              => __( 'New Listing', 'parish-listings' ),
            'view_item'             => __( 'View Listing', 'parish-listings' ),
            'view_items'            => __( 'View Listings', 'parish-listings' ),
            'search_items'          => __( 'Search Listings', 'parish-listings' ),
            'not_found'             => __( 'No listings found', 'parish-listings' ),
            'not_found_in_trash'    => __( 'No listings found in Trash', 'parish-listings' ),
            'all_items'             => __( 'All Listings', 'parish-listings' ),
            'archives'              => __( 'Listing Archives', 'parish-listings' ),
            'attributes'            => __( 'Listing Attributes', 'parish-listings' ),
            'insert_into_item'      => __( 'Insert into listing', 'parish-listings' ),
            'uploaded_to_this_item' => __( 'Uploaded to this listing', 'parish-listings' ),
            'featured_image'        => __( 'Listing Image', 'parish-listings' ),
            'set_featured_image'    => __( 'Set listing image', 'parish-listings' ),
            'remove_featured_image' => __( 'Remove listing image', 'parish-listings' ),
            'use_featured_image'    => __( 'Use as listing image', 'parish-listings' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'listing' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 2.3,  // Just below Dashboard, alphabetical
            'menu_icon'          => 'dashicons-store',
            'supports'           => array( 'title', 'editor', 'thumbnail' ),
            'show_in_rest'       => true,
        );

        register_post_type( 'parish_listing', $args );
    }

    /**
     * Register Taxonomy
     */
    public function register_taxonomy() {
        $labels = array(
            'name'              => _x( 'Listing Categories', 'taxonomy general name', 'parish-listings' ),
            'singular_name'     => _x( 'Listing Category', 'taxonomy singular name', 'parish-listings' ),
            'search_items'      => __( 'Search Listing Categories', 'parish-listings' ),
            'all_items'         => __( 'All Listing Categories', 'parish-listings' ),
            'parent_item'       => __( 'Parent Listing Category', 'parish-listings' ),
            'parent_item_colon' => __( 'Parent Listing Category:', 'parish-listings' ),
            'edit_item'         => __( 'Edit Listing Category', 'parish-listings' ),
            'update_item'       => __( 'Update Listing Category', 'parish-listings' ),
            'add_new_item'      => __( 'Add New Listing Category', 'parish-listings' ),
            'new_item_name'     => __( 'New Listing Category Name', 'parish-listings' ),
            'menu_name'         => __( 'Categories', 'parish-listings' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'listing-category' ),
            'show_in_rest'      => true,
        );

        register_taxonomy( 'listing_category', array( 'parish_listing' ), $args );
    }

    /**
     * Add Meta Boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'parish_listing_details',
            __( 'Listing Details', 'parish-listings' ),
            array( $this, 'render_meta_box' ),
            'parish_listing',
            'normal',
            'high'
        );
    }

    /**
     * Render Meta Box
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( 'parish_listing_meta_box', 'parish_listing_meta_box_nonce' );

        $url      = get_post_meta( $post->ID, '_listing_url', true );
        $phone    = get_post_meta( $post->ID, '_listing_phone', true );
        $email    = get_post_meta( $post->ID, '_listing_email', true );
        $address  = get_post_meta( $post->ID, '_listing_address', true );
        $priority = get_post_meta( $post->ID, '_listing_priority', true );

        ?>
        <table class="form-table">
            <tr>
                <th><label for="listing_url"><?php _e( 'Website URL', 'parish-listings' ); ?></label></th>
                <td>
                    <input type="url" id="listing_url" name="listing_url" value="<?php echo esc_url( $url ); ?>" class="regular-text">
                    <p class="description"><?php _e( 'Full URL including https://', 'parish-listings' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="listing_phone"><?php _e( 'Phone Number', 'parish-listings' ); ?></label></th>
                <td>
                    <input type="text" id="listing_phone" name="listing_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="listing_email"><?php _e( 'Email Address', 'parish-listings' ); ?></label></th>
                <td>
                    <input type="email" id="listing_email" name="listing_email" value="<?php echo esc_attr( $email ); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="listing_address"><?php _e( 'Address', 'parish-listings' ); ?></label></th>
                <td>
                    <textarea id="listing_address" name="listing_address" rows="3" class="large-text"><?php echo esc_textarea( $address ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label for="listing_priority"><?php _e( 'Priority', 'parish-listings' ); ?></label></th>
                <td>
                    <input type="number" id="listing_priority" name="listing_priority" value="<?php echo esc_attr( $priority ); ?>" class="small-text" min="0" step="1">
                    <p class="description"><?php _e( 'Lower numbers appear first (default: 10)', 'parish-listings' ); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save Meta Box Data
     */
    public function save_meta_box( $post_id ) {
        // Check nonce
        if ( ! isset( $_POST['parish_listing_meta_box_nonce'] ) ||
             ! wp_verify_nonce( $_POST['parish_listing_meta_box_nonce'], 'parish_listing_meta_box' ) ) {
            return;
        }

        // Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save fields
        if ( isset( $_POST['listing_url'] ) ) {
            update_post_meta( $post_id, '_listing_url', esc_url_raw( $_POST['listing_url'] ) );
        }

        if ( isset( $_POST['listing_phone'] ) ) {
            update_post_meta( $post_id, '_listing_phone', sanitize_text_field( $_POST['listing_phone'] ) );
        }

        if ( isset( $_POST['listing_email'] ) ) {
            update_post_meta( $post_id, '_listing_email', sanitize_email( $_POST['listing_email'] ) );
        }

        if ( isset( $_POST['listing_address'] ) ) {
            update_post_meta( $post_id, '_listing_address', sanitize_textarea_field( $_POST['listing_address'] ) );
        }

        if ( isset( $_POST['listing_priority'] ) ) {
            update_post_meta( $post_id, '_listing_priority', absint( $_POST['listing_priority'] ) );
        }
    }

    /**
     * Enqueue Styles
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'parish-listings',
            PARISH_LISTINGS_PLUGIN_URL . 'assets/css/parish-listings.css',
            array(),
            PARISH_LISTINGS_VERSION
        );
    }

    /**
     * Shortcode Handler
     */
    public function shortcode_handler( $atts ) {
        $atts = shortcode_atts( array(
            'category'     => '',
            'limit'        => -1,
            'per_page'     => 0,
            'orderby'      => 'priority',
            'order'        => 'ASC',
            'layout'       => 'alternate',
            'show_image'   => 'true',
            'show_phone'   => 'true',
            'show_email'   => 'true',
            'show_address' => 'true',
            'show_url'     => 'true',
        ), $atts, 'parish_listings' );

        // Convert string booleans
        $atts['show_image']   = filter_var( $atts['show_image'], FILTER_VALIDATE_BOOLEAN );
        $atts['show_phone']   = filter_var( $atts['show_phone'], FILTER_VALIDATE_BOOLEAN );
        $atts['show_email']   = filter_var( $atts['show_email'], FILTER_VALIDATE_BOOLEAN );
        $atts['show_address'] = filter_var( $atts['show_address'], FILTER_VALIDATE_BOOLEAN );
        $atts['show_url']     = filter_var( $atts['show_url'], FILTER_VALIDATE_BOOLEAN );

        // Convert numeric values
        $atts['limit']    = intval( $atts['limit'] );
        $atts['per_page'] = intval( $atts['per_page'] );

        // Get current page
        $paged = isset( $_GET['listings_page'] ) ? absint( $_GET['listings_page'] ) : 1;

        // Build query args
        $query_args = array(
            'post_type'      => 'parish_listing',
            'post_status'    => 'publish',
            'posts_per_page' => $atts['per_page'] > 0 ? $atts['per_page'] : $atts['limit'],
            'paged'          => $atts['per_page'] > 0 ? $paged : 1,
        );

        // Handle category filter
        if ( ! empty( $atts['category'] ) ) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'listing_category',
                    'field'    => is_numeric( $atts['category'] ) ? 'term_id' : 'slug',
                    'terms'    => $atts['category'],
                ),
            );
        }

        // Handle ordering
        switch ( $atts['orderby'] ) {
            case 'priority':
                $query_args['meta_key'] = '_listing_priority';
                $query_args['orderby']  = 'meta_value_num';
                break;
            case 'title':
                $query_args['orderby'] = 'title';
                break;
            case 'date':
                $query_args['orderby'] = 'date';
                break;
            case 'rand':
                $query_args['orderby'] = 'rand';
                break;
            default:
                $query_args['orderby'] = 'title';
        }

        $query_args['order'] = strtoupper( $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC';

        // Run query
        $listings = new WP_Query( $query_args );

        // Start output buffering
        ob_start();

        // Include template
        include PARISH_LISTINGS_PLUGIN_DIR . 'templates/listings-display.php';

        // Reset post data
        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * AJAX Pagination Handler
     */
    public function ajax_paginate() {
        check_ajax_referer( 'parish_listings_nonce', 'nonce' );

        $atts = array(
            'category'     => isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '',
            'limit'        => isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : -1,
            'per_page'     => isset( $_POST['per_page'] ) ? intval( $_POST['per_page'] ) : 0,
            'orderby'      => isset( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'priority',
            'order'        => isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'ASC',
            'layout'       => isset( $_POST['layout'] ) ? sanitize_text_field( $_POST['layout'] ) : 'alternate',
            'show_image'   => isset( $_POST['show_image'] ) ? filter_var( $_POST['show_image'], FILTER_VALIDATE_BOOLEAN ) : true,
            'show_phone'   => isset( $_POST['show_phone'] ) ? filter_var( $_POST['show_phone'], FILTER_VALIDATE_BOOLEAN ) : true,
            'show_email'   => isset( $_POST['show_email'] ) ? filter_var( $_POST['show_email'], FILTER_VALIDATE_BOOLEAN ) : true,
            'show_address' => isset( $_POST['show_address'] ) ? filter_var( $_POST['show_address'], FILTER_VALIDATE_BOOLEAN ) : true,
            'show_url'     => isset( $_POST['show_url'] ) ? filter_var( $_POST['show_url'], FILTER_VALIDATE_BOOLEAN ) : true,
        );

        $paged = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

        // Build query args
        $query_args = array(
            'post_type'      => 'parish_listing',
            'post_status'    => 'publish',
            'posts_per_page' => $atts['per_page'],
            'paged'          => $paged,
        );

        if ( ! empty( $atts['category'] ) ) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'listing_category',
                    'field'    => is_numeric( $atts['category'] ) ? 'term_id' : 'slug',
                    'terms'    => $atts['category'],
                ),
            );
        }

        switch ( $atts['orderby'] ) {
            case 'priority':
                $query_args['meta_key'] = '_listing_priority';
                $query_args['orderby']  = 'meta_value_num';
                break;
            case 'title':
                $query_args['orderby'] = 'title';
                break;
            case 'date':
                $query_args['orderby'] = 'date';
                break;
            case 'rand':
                $query_args['orderby'] = 'rand';
                break;
        }

        $query_args['order'] = strtoupper( $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC';

        $listings = new WP_Query( $query_args );

        ob_start();
        include PARISH_LISTINGS_PLUGIN_DIR . 'templates/listings-display.php';
        $html = ob_get_clean();

        wp_reset_postdata();

        wp_send_json_success( array( 'html' => $html ) );
    }
}
