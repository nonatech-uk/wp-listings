# Parish Listings

A WordPress plugin for displaying categorized listings (facilities, businesses, events, etc.) with metadata-driven content and flexible shortcode options.

## Installation

1. Copy the `parish-listings` folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin
3. A new "Listings" menu will appear in the admin sidebar

## Features

- Custom post type with featured image support
- Hierarchical categories (listing_category taxonomy)
- Meta fields: website URL, phone, email, address, priority
- Flexible shortcode with multiple display options
- Responsive alternate (zigzag) layout
- Clickable phone (tel:) and email (mailto:) links

## Custom Fields

Each listing supports the following fields:

| Field | Description |
|-------|-------------|
| Title | Listing name (standard WP title) |
| Description | Main content (standard WP editor) |
| Featured Image | Displayed in the layout |
| Website URL | Clickable link to external site |
| Phone | Clickable tel: link |
| Email | Clickable mailto: link |
| Address | Physical location |
| Priority | Sort order (lower numbers first) |

## Shortcode Usage

```
[parish_listings]
```

### Attributes

| Attribute | Default | Description |
|-----------|---------|-------------|
| `category` | `''` | Category slug or ID to filter by |
| `limit` | `-1` | Maximum listings to show (-1 = all) |
| `per_page` | `0` | Items per page (0 = no pagination) |
| `orderby` | `priority` | Sort by: `priority`, `title`, `date`, `rand` |
| `order` | `ASC` | Sort direction: `ASC` or `DESC` |
| `layout` | `alternate` | Image position: `alternate`, `left`, `right` |
| `show_image` | `true` | Show featured image |
| `show_url` | `true` | Show website link |
| `show_phone` | `true` | Show phone number |
| `show_email` | `true` | Show email address |
| `show_address` | `true` | Show physical address |

### Examples

Display all listings in a category:
```
[parish_listings category="local-business"]
```

Display 5 random listings:
```
[parish_listings category="hostelry" orderby="rand" limit="5"]
```

Alphabetical with pagination:
```
[parish_listings category="sports-facility" orderby="title" per_page="10"]
```

Images always on left:
```
[parish_listings category="annual-events" layout="left"]
```

Hide contact details:
```
[parish_listings show_phone="false" show_email="false" show_address="false"]
```

## Layout Options

### Alternate (default)
Odd items: image left, content right
Even items: image right, content left

### Left
All images on the left

### Right
All images on the right

On mobile (< 768px), all layouts stack vertically.

## Categories

Default categories created by migration:
- `local-business` - Local Business
- `hostelry` - Hostelry (pubs, restaurants, hotels)
- `sports-facility` - Sports Facility
- `annual-events` - Annual Events

Add new categories via Listings > Categories in the admin.

## Styling

The plugin uses these CSS classes for customization:

```css
.parish-listings-container    /* Main wrapper */
.parish-listing-item          /* Individual listing card */
.parish-listing-image         /* Image container */
.parish-listing-content       /* Content container */
.parish-listing-title         /* Listing title (h3) */
.parish-listing-description   /* Main content */
.parish-listing-meta          /* Contact details wrapper */
.parish-listing-url           /* Website field */
.parish-listing-phone         /* Phone field */
.parish-listing-email         /* Email field */
.parish-listing-address       /* Address field */
.parish-listings-pagination   /* Pagination nav */
```

Theme colors:
- Primary: `#4a6741` (Albury Estate Green)
- Hover: `#3d5c3a`

## Migration from Business Directory Plugin

A migration script is included at `migrate-from-bdp.php`. Run it once after installation to import existing listings:

```bash
# Via PHP CLI in the WordPress container
php /path/to/wp-content/plugins/parish-listings/migrate-from-bdp.php
```

Or visit the script URL while logged in as admin:
```
/wp-content/plugins/parish-listings/migrate-from-bdp.php?run=1
```

## File Structure

```
parish-listings/
├── parish-listings.php           # Main plugin file
├── includes/
│   └── class-parish-listings.php # Core class (CPT, taxonomy, shortcode)
├── templates/
│   └── listings-display.php      # Frontend template
├── assets/
│   └── css/
│       └── parish-listings.css   # Styles
└── migrate-from-bdp.php          # Migration script
```

## Requirements

- WordPress 5.0+
- PHP 7.4+

## License

GPL v2 or later
