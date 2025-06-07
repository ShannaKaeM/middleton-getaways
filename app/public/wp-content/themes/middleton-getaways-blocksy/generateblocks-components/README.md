# Luxury Furniture Section - GenerateBlocks Component

A responsive luxury furniture showcase component built with GenerateBlocks and integrated with Blocksy theme variables.

## Files Included

- `luxury-furniture-section.html` - WordPress block markup
- `luxury-furniture-section.css` - Scoped CSS with Blocksy integration
- `luxury-furniture-pattern.json` - Block pattern for import
- `README.md` - This documentation

## Installation

### Method 1: Copy Block Markup
1. Copy the content from `luxury-furniture-section.html`
2. In WordPress admin, go to Pages/Posts → Add New
3. Click the "+" button to add a block
4. Click the three dots menu → "Code Editor"
5. Paste the copied markup
6. Switch back to Visual Editor

### Method 2: Import as Pattern (GenerateBlocks Pro)
1. Go to WordPress Admin → GenerateBlocks → Block Library
2. Click "Import Pattern"
3. Upload `luxury-furniture-pattern.json`
4. The pattern will be available in the block inserter

### Method 3: Manual Block Creation
1. Add GenerateBlocks Container
2. Set up the grid layout as described below
3. Add content blocks (headlines, buttons)
4. Add image containers with background images

## CSS Integration

Add the CSS from `luxury-furniture-section.css` to your child theme:

### Option 1: Add to style.css
Copy the CSS content and paste it into your child theme's `style.css` file.

### Option 2: Enqueue as separate file
Add this to your child theme's `functions.php`:

```php
function mg_enqueue_luxury_component_styles() {
    wp_enqueue_style(
        'mg-luxury-component',
        get_stylesheet_directory_uri() . '/generateblocks-components/luxury-furniture-section.css',
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'mg_enqueue_luxury_component_styles');
```

## Component Structure

```
Main Container (.luxury-furniture-section)
├── Grid Container (2 columns on desktop, 1 on mobile)
    ├── Left Content Column
    │   ├── Main Headline (h2)
    │   ├── Description Paragraph
    │   └── CTA Button
    └── Right Images Grid
        ├── Large Image (spans 2 rows)
        ├── Top Right Image
        └── Bottom Right Image
```

## Customization

### Scoped CSS Variables
The component uses scoped CSS variables for easy customization:

```css
.luxury-furniture-section {
  --luxury-bg-color: #f8f9fa;
  --luxury-text-primary: var(--theme-text-color);
  --luxury-accent-color: var(--mg-primary);
  --luxury-font-primary: var(--mg-font-montserrat);
  /* ... more variables */
}
```

### Blocksy Integration
- Uses Blocksy theme variables (`--theme-text-color`, `--theme-background-color`)
- Integrates with your child theme's Montserrat font
- Supports Blocksy's dark mode
- Responsive breakpoints match Blocksy's system

### Responsive Behavior
- **Desktop (1024px+)**: 2-column grid layout
- **Tablet (768px-1024px)**: Single column, centered content
- **Mobile (480px-768px)**: Stacked layout, full-width button
- **Small Mobile (<480px)**: Single column image grid

## Content Customization

### Text Content
1. Edit the headline text in the GenerateBlocks Headline block
2. Modify the description paragraph
3. Update the button text and link URL

### Images
1. Click on each image container
2. In the block settings, update the "Background Image"
3. Replace with your own images or different Unsplash URLs
4. Recommended image sizes:
   - Large image: 600x800px
   - Small images: 400x300px

### Colors
Update the CSS variables to match your brand:

```css
.luxury-furniture-section {
  --luxury-accent-color: #your-brand-color;
  --luxury-accent-hover: #your-hover-color;
}
```

## Performance Notes

- Images are loaded as CSS background images for better grid control
- Component uses CSS Grid for optimal layout performance
- Scoped variables prevent style conflicts
- Responsive images automatically optimized by Unsplash

## Browser Support

- Modern browsers with CSS Grid support
- Fallbacks included for older browsers
- Tested with Blocksy theme compatibility

## Troubleshooting

### Images not showing
- Check that image URLs are accessible
- Verify background-image CSS is applied
- Try different image URLs

### Layout issues
- Ensure GenerateBlocks plugin is active and updated
- Check that CSS is properly enqueued
- Verify no conflicting CSS from other plugins

### Font not loading
- Confirm Montserrat is loaded in your child theme
- Check CSS variable definitions
- Verify font fallbacks are working

## Support

For issues specific to this component, check:
1. GenerateBlocks plugin is active
2. CSS is properly loaded
3. No JavaScript errors in browser console
4. Theme compatibility with GenerateBlocks
