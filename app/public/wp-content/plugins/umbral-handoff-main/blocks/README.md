# Umbral Editor - WordPress Blocks

This directory contains WordPress block definitions for the Umbral Editor plugin.

## Structure

```
blocks/
├── README.md
└── components/
    ├── block.json       # Block configuration
    ├── index.js         # Editor JavaScript
    └── index.php        # Server-side render
```

## Available Blocks

### Example Block (`umbral-editor/example-block`)

A simple demonstration block that shows how to integrate WordPress blocks with the Umbral Editor system.

**Features:**
- Server-side rendering via PHP
- Simple editor interface
- Text category placement
- Basic attribute handling

**Usage:**
1. Add block in Gutenberg editor
2. Block appears under "Text" category
3. Search for "Example Block"
4. Displays "I'm a block (Editor View)" in editor
5. Renders "I'm a block" on frontend

## Block Development

### Adding New Blocks

1. **Create block directory** under `blocks/`
2. **Add block.json** with block configuration
3. **Create index.php** for server-side rendering
4. **Add index.js** for editor interface (optional)
5. **Register in main plugin** (automatic for blocks in this structure)

### Block Registration

Blocks are automatically registered by the main plugin's `registerBlocks()` method in `umbral-editor.php`. The registration process:

1. Scans `blocks/` directory
2. Looks for `block.json` files
3. Calls `register_block_type()` with directory path
4. WordPress handles the rest automatically

### File Requirements

#### block.json (Required)
```json
{
    "apiVersion": 3,
    "name": "umbral-editor/block-name",
    "title": "Block Title",
    "category": "common",
    "render": "file:./index.php"
}
```

#### index.php (Required for server-side rendering)
```php
<?php
// $attributes, $content, $block available
$wrapper_attributes = get_block_wrapper_attributes();
?>
<div <?php echo $wrapper_attributes; ?>>
    <!-- Block output -->
</div>
```

#### index.js (Optional for editor interface)
```javascript
wp.blocks.registerBlockType('umbral-editor/block-name', {
    edit: function() {
        return wp.element.createElement('div', 
            wp.blockEditor.useBlockProps(),
            'Block content in editor'
        );
    }
});
```

## Integration with CMB2

These blocks can work alongside the Umbral Editor CMB2 components field system:

- **CMB2 Field**: For admin/backend content management
- **Blocks**: For frontend/Gutenberg content creation
- **Shared Components**: Both can use the same component registry

## Best Practices

1. **Use server-side rendering** for better performance and SEO
2. **Follow WordPress coding standards** for PHP and JavaScript
3. **Include proper escaping** in PHP render callbacks
4. **Use `get_block_wrapper_attributes()`** for proper block wrapper
5. **Keep editor JavaScript minimal** for faster loading
6. **Test in both editor and frontend** contexts

## Development Workflow

1. **Create block files** in new directory
2. **Test in WordPress admin** - should auto-register
3. **Verify editor appearance** in Gutenberg
4. **Check frontend rendering** on public pages
5. **Debug with error logs** if issues occur

## Debugging

The main plugin includes comprehensive logging for block registration. Check WordPress debug logs for:

- Block directory scanning
- Registration success/failure
- File existence checks
- Registration results

Enable `WP_DEBUG` and `WP_DEBUG_LOG` to see detailed registration information.