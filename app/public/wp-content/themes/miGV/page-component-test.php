<?php
/**
 * Template Name: Component Test
 * 
 * Test page for Design Book components
 */

get_header();

// Ensure Timber is available
if (!class_exists('Timber')) {
    echo '<p>Timber is required for components to work.</p>';
    get_footer();
    return;
}

// Sample data for testing
$sample_property = [
    'id' => 1,
    'title' => '123 Ocean View Drive',
    'image' => 'https://via.placeholder.com/400x300',
    'address' => '123 Ocean View Drive, Unit 4B',
    'status' => 'available',
    'listing_status' => 'for_sale',
    'bedrooms' => 3,
    'bathrooms' => 2,
    'area' => '1,200 sq ft',
    'price' => '$450,000',
    'owner' => 'John Doe',
    'permalink' => '#',
    'can_edit' => true,
    'edit_link' => '#edit'
];

$context = [
    'title' => 'Component Test Page',
    'components' => [
        'badge' => [
            'name' => 'Badge Component',
            'template' => 'element-books/badge-book.twig',
            'variants' => [
                ['content' => 'Available', 'variant' => 'success'],
                ['content' => 'Pending', 'variant' => 'warning'],
                ['content' => 'Urgent', 'variant' => 'danger'],
                ['content' => 'Info', 'variant' => 'info', 'count' => 5]
            ]
        ],
        'avatar' => [
            'name' => 'Avatar Component',
            'template' => 'element-books/avatar-book.twig',
            'variants' => [
                ['name' => 'John Doe', 'variant' => 'user'],
                ['name' => 'Tech Committee', 'variant' => 'group'],
                ['name' => 'Jane Smith', 'variant' => 'user', 'status' => 'online']
            ]
        ],
        'stat-card' => [
            'name' => 'Stat Card Component',
            'template' => 'component-books/stat-card-book.twig',
            'variants' => [
                ['value' => '24', 'label' => 'Properties', 'color' => 'primary', 'icon' => '🏠'],
                ['value' => '5', 'label' => 'Active Tickets', 'color' => 'warning', 'icon' => '🎫', 'trend' => ['direction' => 'up', 'value' => '2', 'label' => 'this week']]
            ]
        ],
        'property-card' => [
            'name' => 'Property Card Component',
            'template' => 'component-books/property-card-book.twig',
            'variants' => [
                ['property' => $sample_property, 'show_actions' => true]
            ]
        ]
    ]
];

?>

<div class="component-test-page" style="padding: 40px; max-width: 1200px; margin: 0 auto;">
    <h1 style="font-family: var(--wp--preset--font-family--montserrat); font-size: 3rem; margin-bottom: 2rem;">
        🧪 Design Book Component Test
    </h1>
    
    <div style="background: #f0f0f0; padding: 20px; border-radius: 8px; margin-bottom: 40px;">
        <p><strong>This page demonstrates the new component system.</strong></p>
        <p>Each component below is rendered using the atomic design system with Twig templates.</p>
        <p>Add <code>?debug=1</code> to the URL to see component boundaries.</p>
    </div>
    
    <?php foreach ($context['components'] as $key => $component): ?>
        <section style="margin-bottom: 60px;">
            <h2 style="font-family: var(--wp--preset--font-family--montserrat); font-size: 2rem; margin-bottom: 1rem;">
                <?php echo $component['name']; ?>
            </h2>
            <p style="color: #666; margin-bottom: 2rem;">Template: <code><?php echo $component['template']; ?></code></p>
            
            <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start;">
                <?php foreach ($component['variants'] as $variant): ?>
                    <div style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                        <?php echo Timber::compile($component['template'], $variant); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
    
    <section style="margin-bottom: 60px;">
        <h2 style="font-family: var(--wp--preset--font-family--montserrat); font-size: 2rem; margin-bottom: 1rem;">
            Grid Component with Cards
        </h2>
        <p style="color: #666; margin-bottom: 2rem;">Template: <code>component-books/grid-book.twig</code></p>
        
        <?php
        $grid_context = [
            'variant' => 'cards',
            'columns' => ['sm' => 1, 'md' => 2, 'lg' => 3],
            'items' => [
                ['property' => $sample_property, 'show_actions' => true],
                ['property' => array_merge($sample_property, ['title' => '456 Beach Front', 'status' => 'occupied']), 'show_actions' => true],
                ['property' => array_merge($sample_property, ['title' => '789 Mountain View', 'status' => 'maintenance']), 'show_actions' => true]
            ],
            'item_component' => 'component-books/property-card-book.twig'
        ];
        echo Timber::compile('component-books/grid-book.twig', $grid_context);
        ?>
    </section>
</div>

<?php get_footer(); ?>