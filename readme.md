# LF Team Profiles

A WordPress plugin for displaying team members with ACF (Advanced Custom Fields) integration, department filtering, and native HTML popovers.

## Features

- **Team Member Management**: Custom post type for managing team members
- **Department Filtering**: Taxonomy-based department organization
- **Responsive Grid Layout**: 2-6 column layouts with mobile optimization
- **Native HTML Popovers**: Modern popover API with fallback support
- **ACF Integration**: Rich custom fields for team member details
- **Gutenberg Block**: Native block editor support
- **Shortcode Support**: Legacy shortcode for flexible placement
- **Priority Ordering**: Custom sorting by priority field
- **LinkedIn Integration**: Direct links to team member profiles

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Advanced Custom Fields (ACF) plugin

## Installation

1. Download the plugin files
2. Upload to your WordPress `/wp-content/plugins/` directory
3. Activate the plugin through the WordPress admin
4. Install and activate Advanced Custom Fields if not already installed

## Usage

### Gutenberg Block

1. In the block editor, add a "Team Profiles" block (found in Widgets category)
2. Configure settings in the block sidebar:
   - **Department**: Filter by department slug(s), comma-separated
   - **Columns**: Choose 2-6 columns
   - **Order By**: Sort by menu order, title, date, or priority
   - **Order**: Ascending or descending

### Shortcode

Use the `[team_profiles]` shortcode with optional parameters:

```
[team_profiles]
[team_profiles department="marketing"]
[team_profiles department="marketing,sales" columns="3"]
[team_profiles orderby="meta_value_num" order="ASC"]
```

#### Shortcode Parameters

| Parameter | Description | Default | Options |
|-----------|-------------|---------|---------|
| `department` | Filter by department slug(s), comma-separated | `""` | Any department slug |
| `columns` | Number of columns in grid | `"4"` | `"2"`, `"3"`, `"4"`, `"5"`, `"6"` |
| `orderby` | Field to sort by | `"menu_order"` | `"title"`, `"date"`, `"menu_order"`, `"meta_value_num"` |
| `order` | Sort direction | `"ASC"` | `"ASC"`, `"DESC"` |

### Priority Sorting

To sort team members by priority:
- Set `orderby="meta_value_num"`
- Lower priority numbers appear first
- Members with the same priority are sorted alphabetically by title

## Team Member Fields

Each team member includes the following fields:

- **Title**: Member's name (WordPress post title)
- **Photo**: Profile image (ACF image field)
- **Job Title**: Position or role
- **Team**: Team or department name
- **Biography**: Rich text description
- **LinkedIn URL**: Profile link
- **Priority**: Numeric sorting value (lower = higher priority)
- **Featured Image**: Alternative photo option

## Department Management

- Navigate to **Team Profiles > Departments** in WordPress admin
- Create hierarchical department structure
- Assign team members to departments
- Filter displays by department slug

## Styling

### CSS Classes

The plugin provides extensive CSS classes for customization:

- `.lf-team-profiles-grid` - Main grid container
- `.lf-team-member` - Individual team member card
- `.lf-team-photo` - Profile photo
- `.lf-team-name` - Member name
- `.lf-team-job-title` - Job title
- `.lf-team-team` - Team name
- `.lf-team-popover` - Popover modal
- `.lf-team-bio` - Biography content

### Responsive Breakpoints

- **Desktop**: 4+ columns (configurable)
- **Tablet** (768px): 2 columns
- **Mobile** (480px): 1 column

## Browser Support

- **Modern browsers**: Native HTML popover API
- **Legacy browsers**: JavaScript fallback implementation
- **Mobile**: Touch-friendly interactions

## File Structure

```
lf-team-profiles/
├── assets/
│   ├── css/
│   │   ├── lf-team-profiles.css
│   │   └── lf-team-profiles.min.css
│   └── js/
│       ├── lf-team-profiles.js
│       └── lf-team-profiles.min.js
├── blocks/
│   ├── team-profiles-block.js
│   └── team-profiles-block-debug.js
├── lf-team-profiles.php
└── README.md
```

## Customization

### Custom CSS

Add custom styles to your theme:

```css
.lf-team-profiles-grid {
    gap: 40px; /* Increase spacing */
}

.lf-team-photo-wrapper {
    width: 200px; /* Larger photos */
    height: 200px;
}

.lf-team-popover-inner {
    max-width: 800px; /* Wider popovers */
}
```

### Hooks and Filters

The plugin follows WordPress coding standards and provides standard hooks for customization.

## Performance

- **Conditional Loading**: Assets only load when shortcode/block is present
- **Minified Assets**: Production-ready compressed files
- **Optimized Images**: Automatic image size handling
- **Caching Friendly**: Server-side rendering for better performance

## Accessibility

- **Keyboard Navigation**: Full keyboard support for popovers
- **Screen Readers**: Proper ARIA labels and semantic markup
- **Focus Management**: Logical tab order and focus indicators
- **High Contrast**: Respects system color preferences

## Troubleshooting

### Common Issues

1. **No team members showing**: Check if ACF is installed and team members exist
2. **Styles not loading**: Verify the shortcode/block is present on the page
3. **Popovers not working**: Ensure JavaScript is enabled and not blocked
4. **Images not displaying**: Check image upload permissions and file paths

### Debug Mode

Enable WordPress debug mode and check the browser console for JavaScript errors. The plugin includes debug logging for troubleshooting.

## Changelog

### Version 2.1.1
- Added native HTML popover support
- Improved browser compatibility
- Enhanced accessibility features
- Optimized performance

## License

GPL v2 or later

## Support

For issues and feature requests, please contact the plugin author or submit issues through your preferred support channel.

## Credits

- **Author**: Luis Alvarez
- **Plugin URI**: https://github.com/jabuta/lf-team-profiles
- **Requires**: Advanced Custom Fields plugin