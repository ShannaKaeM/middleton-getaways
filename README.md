# Middleton Getaways - WordPress Theme Project

## Project Overview

Middleton Getaways (miGV) is a custom WordPress theme built with modern development practices, featuring a comprehensive design system, custom post types for properties and businesses, and a membership system.

## Key Features

- **Design Book System**: JSON-based design token management with visual editors
- **Timber/Twig Integration**: Modern templating with separation of logic and presentation
- **Custom Post Types**: Properties, Businesses, and Testimonials
- **Membership System**: Built-in user registration and profile management
- **Responsive Design**: Mobile-first approach with modern CSS
- **Performance Optimized**: Efficient asset loading and caching strategies

## Technology Stack

- **WordPress**: 6.x
- **PHP**: 7.4+
- **Timber/Twig**: For templating
- **CMB2**: Custom metaboxes and fields
- **Composer**: Dependency management
- **jQuery**: For interactive features
- **Design Tokens**: JSON-based design system

## Project Structure

```
middleton-getaways/
├── app/
│   └── public/
│       └── wp-content/
│           └── themes/
│               └── miGV/          # Main theme directory
│                   ├── assets/    # CSS, JS, images
│                   ├── blocks/    # Gutenberg blocks
│                   ├── inc/       # PHP includes
│                   ├── miDocs/    # Documentation
│                   ├── primitives/# Design tokens
│                   └── templates/ # Twig templates
├── composer.json
├── vendor/
└── [deployment configs]
```

## Design Book System

The theme features a comprehensive Design Book System for managing design tokens:

### Features
- **Visual Editors**: User-friendly interfaces for managing colors, typography, spacing, and borders
- **JSON-Based**: All design tokens stored in version-controlled JSON files
- **Live Preview**: See changes in real-time before saving
- **CSS Variables**: Automatic generation of CSS custom properties
- **WordPress Integration**: Optional sync with theme.json

### Accessing Design Book Editors
1. Colors: `/primitive-colors`
2. Typography: `/primitive-typography`
3. Spacing: `/primitive-spacing`
4. Borders: `/primitive-borders`

### Documentation
- [Design Book Overview](/app/public/wp-content/themes/miGV/miDocs/DesignBook/DESIGN-BOOK-OVERVIEW.md)
- [Technical Documentation](/app/public/wp-content/themes/miGV/miDocs/SYSTEMS/DESIGN-BOOK-TECHNICAL.md)

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- WordPress 6.0 or higher
- Composer

### Setup Instructions

1. **Clone the repository**
```bash
git clone [repository-url] middleton-getaways
cd middleton-getaways
```

2. **Install dependencies**
```bash
composer install
```

3. **Configure WordPress**
- Set up database
- Configure wp-config.php
- Run WordPress installation

4. **Activate theme**
- Go to WordPress admin > Appearance > Themes
- Activate "miGV" theme

5. **Create Design Book pages** (optional)
- Create pages with these slugs:
  - `primitive-colors`
  - `primitive-typography`
  - `primitive-spacing`
  - `primitive-borders`
- Assign corresponding page templates

## Development

### Local Development
The project uses Local by Flywheel for local development. Configuration files are included.

### Build Process
Currently, there's no build process required. All assets are ready to use.

### Coding Standards
- Follow WordPress coding standards
- Use meaningful variable and function names
- Comment complex logic
- Keep functions focused and small

### Working with Design Tokens

#### Using in Templates
```twig
{# Include primitive books #}
{% include 'primitive-books/color-book.twig' with {
    background: 'primary',
    color: 'white'
} %}
```

#### Using CSS Variables
```css
.component {
    color: var(--colors-primary-default);
    padding: var(--spacing-scale-lg);
}
```

## Custom Post Types

### Properties
- Custom fields for real estate listings
- Taxonomies: Categories, Locations
- Template: `single-property.twig`

### Businesses
- Local business directory
- Custom fields for business information
- Template: `single-business.twig`

### Testimonials
- Customer reviews and testimonials
- Template: `single-testimonial.twig`

## Deployment

### Local Development
1. Clone repository
2. Run `composer install`
3. Set up local WordPress environment
4. Activate miGV theme

### Production Deployment
1. Push to git repository
2. Pull on production server
3. Run `composer install --no-dev`
4. Clear caches

## Documentation

### Theme Documentation
- [Design Book System](/app/public/wp-content/themes/miGV/miDocs/DesignBook/)
- [Systems Documentation](/app/public/wp-content/themes/miGV/miDocs/SYSTEMS/)
- [Villa Roadmaps](/app/public/wp-content/themes/miGV/miDocs/VILLA-ROADMAPS/)

### WordPress Documentation
- [Theme Handbook](https://developer.wordpress.org/themes/)
- [Timber Documentation](https://timber.github.io/docs/)
- [CMB2 Documentation](https://cmb2.io/)

## Troubleshooting

### Common Issues

1. **Design tokens not updating**
   - Clear browser cache
   - Check file permissions on primitives folder
   - Verify JSON syntax

2. **Twig templates not found**
   - Check Timber is properly installed
   - Verify template paths
   - Clear Twig cache

3. **Custom post types not showing**
   - Flush permalinks (Settings > Permalinks > Save)
   - Check if CPT plugin is active

## Contributing

1. Create feature branch
2. Make changes
3. Test thoroughly
4. Submit pull request
5. Code review
6. Merge to main

## Support

For support and questions:
- Check documentation first
- Review existing issues
- Contact development team

## License

This is a proprietary theme for Middleton Getaways. All rights reserved.

---

**Version**: 1.0.0  
**Last Updated**: [Current Date]  
**Maintainer**: mi agency
