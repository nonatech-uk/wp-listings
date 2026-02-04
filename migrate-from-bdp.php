<?php
/**
 * Migration script: Business Directory Plugin -> Parish Listings
 *
 * Run via WP-CLI: wp eval-file migrate-from-bdp.php
 * Or visit: /wp-content/plugins/parish-listings/migrate-from-bdp.php?run=1
 */

// Load WordPress if running directly
if ( ! defined( 'ABSPATH' ) ) {
    // Find wp-load.php
    $wp_load = dirname( __FILE__ ) . '/../../../wp-load.php';
    if ( file_exists( $wp_load ) ) {
        require_once $wp_load;
    } else {
        die( 'WordPress not found. Run this script from the plugins directory.' );
    }
}

// Security check for web access
if ( php_sapi_name() !== 'cli' ) {
    if ( ! isset( $_GET['run'] ) || $_GET['run'] !== '1' ) {
        die( 'Add ?run=1 to execute migration' );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        die( 'You must be logged in as an administrator' );
    }
}

// Register the CPT and taxonomy if not already registered
if ( ! post_type_exists( 'parish_listing' ) ) {
    register_post_type( 'parish_listing', array(
        'public'   => true,
        'supports' => array( 'title', 'editor', 'thumbnail' ),
    ) );
}

if ( ! taxonomy_exists( 'listing_category' ) ) {
    register_taxonomy( 'listing_category', 'parish_listing', array(
        'hierarchical' => true,
        'public'       => true,
    ) );
}

// Mapping of BDP categories to new category slugs
$category_mapping = array(
    'Local Business'   => 'local-business',
    'Hostelry'         => 'hostelry',
    'Sports Facility'  => 'sports-facility',
    'Annual Events'    => 'annual-events',
);

echo "Starting migration from Business Directory Plugin to Parish Listings...\n\n";

// Step 1: Create categories
echo "Step 1: Creating categories...\n";
foreach ( $category_mapping as $name => $slug ) {
    $existing = term_exists( $slug, 'listing_category' );
    if ( ! $existing ) {
        $result = wp_insert_term( $name, 'listing_category', array( 'slug' => $slug ) );
        if ( is_wp_error( $result ) ) {
            echo "  ERROR creating category '$name': " . $result->get_error_message() . "\n";
        } else {
            echo "  Created category: $name ($slug)\n";
        }
    } else {
        echo "  Category already exists: $name ($slug)\n";
    }
}

// Step 2: Get BDP listings
echo "\nStep 2: Fetching Business Directory listings...\n";

$bdp_listings = get_posts( array(
    'post_type'      => 'wpbdp_listing',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
) );

echo "  Found " . count( $bdp_listings ) . " listings to migrate\n";

// Step 3: Migrate each listing
echo "\nStep 3: Migrating listings...\n";

$migrated = 0;
$skipped = 0;
$errors = 0;

foreach ( $bdp_listings as $bdp_post ) {
    // Check if already migrated (by title match)
    $existing = get_posts( array(
        'post_type'  => 'parish_listing',
        'title'      => $bdp_post->post_title,
        'posts_per_page' => 1,
    ) );

    if ( ! empty( $existing ) ) {
        echo "  SKIP (exists): {$bdp_post->post_title}\n";
        $skipped++;
        continue;
    }

    // Get BDP meta fields
    $website = get_post_meta( $bdp_post->ID, '_wpbdp[fields][5]', true );
    $phone   = get_post_meta( $bdp_post->ID, '_wpbdp[fields][6]', true );
    $email   = get_post_meta( $bdp_post->ID, '_wpbdp[fields][7]', true );
    $address = get_post_meta( $bdp_post->ID, '_wpbdp[fields][9]', true );

    // Handle serialized or array website URLs
    if ( is_serialized( $website ) ) {
        $website = maybe_unserialize( $website );
    }
    if ( is_array( $website ) ) {
        // Find first non-empty URL in the array
        $website = '';
        foreach ( $website as $url ) {
            if ( ! empty( $url ) && is_string( $url ) ) {
                $website = $url;
                break;
            }
        }
    }
    // Ensure website is a string
    if ( ! is_string( $website ) ) {
        $website = '';
    }

    // Get BDP categories
    $bdp_categories = wp_get_object_terms( $bdp_post->ID, 'wpbdp_category', array( 'fields' => 'names' ) );

    // Clean up content (remove block comments if needed)
    $content = $bdp_post->post_content;
    // Strip WordPress block comments for cleaner display
    $content = preg_replace( '/<!-- wp:[^>]+-->/s', '', $content );
    $content = preg_replace( '/<!-- \/wp:[^>]+-->/s', '', $content );
    $content = trim( $content );

    // Create new parish listing
    $new_post_data = array(
        'post_type'    => 'parish_listing',
        'post_title'   => $bdp_post->post_title,
        'post_content' => $content,
        'post_status'  => 'publish',
    );

    $new_post_id = wp_insert_post( $new_post_data );

    if ( is_wp_error( $new_post_id ) ) {
        echo "  ERROR: {$bdp_post->post_title} - " . $new_post_id->get_error_message() . "\n";
        $errors++;
        continue;
    }

    // Add meta fields
    if ( $website ) {
        update_post_meta( $new_post_id, '_listing_url', esc_url_raw( $website ) );
    }
    if ( $phone ) {
        update_post_meta( $new_post_id, '_listing_phone', sanitize_text_field( $phone ) );
    }
    if ( $email ) {
        update_post_meta( $new_post_id, '_listing_email', sanitize_email( $email ) );
    }
    if ( $address ) {
        update_post_meta( $new_post_id, '_listing_address', sanitize_textarea_field( $address ) );
    }

    // Set default priority (order by title position)
    update_post_meta( $new_post_id, '_listing_priority', 10 );

    // Assign categories
    if ( ! empty( $bdp_categories ) ) {
        $term_ids = array();
        foreach ( $bdp_categories as $cat_name ) {
            if ( isset( $category_mapping[ $cat_name ] ) ) {
                $term = get_term_by( 'slug', $category_mapping[ $cat_name ], 'listing_category' );
                if ( $term ) {
                    $term_ids[] = $term->term_id;
                }
            }
        }
        if ( ! empty( $term_ids ) ) {
            wp_set_object_terms( $new_post_id, $term_ids, 'listing_category' );
        }
    }

    // Copy featured image
    $thumbnail_id = get_post_thumbnail_id( $bdp_post->ID );
    if ( $thumbnail_id ) {
        set_post_thumbnail( $new_post_id, $thumbnail_id );
    }

    echo "  MIGRATED: {$bdp_post->post_title}";
    if ( ! empty( $bdp_categories ) ) {
        echo " [" . implode( ', ', $bdp_categories ) . "]";
    }
    echo "\n";

    $migrated++;
}

// Summary
echo "\n========================================\n";
echo "Migration Complete!\n";
echo "========================================\n";
echo "Migrated: $migrated\n";
echo "Skipped (already exist): $skipped\n";
echo "Errors: $errors\n";
echo "\nTotal BDP listings: " . count( $bdp_listings ) . "\n";
