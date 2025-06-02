# PHP Backend Refactor Plan for Component Integration

## Current State Assessment

### ✅ What's Ready:
- **Timber/Twig** is properly installed and configured
- **CMB2** provides structured meta fields
- **Custom Post Types** are well-defined
- **Modular mu-plugins** structure

### ❌ What Needs Work:
- **Mixed rendering** - HTML generated directly in PHP
- **No data preparation layer** - Raw WordPress data passed to views
- **Inline styles** everywhere
- **No component abstraction** - Repeated HTML patterns
- **Tight coupling** - Business logic mixed with presentation

## Refactor Strategy

### Phase 1: Create Data Preparation Layer (Day 1)

#### 1.1 Create Data Transformer Classes
```php
// mu-plugins/villa-data-transformers.php

class VillaPropertyTransformer {
    public static function transform($property) {
        return [
            'id' => $property->ID,
            'title' => $property->post_title,
            'image' => get_the_post_thumbnail_url($property->ID, 'medium'),
            'address' => get_post_meta($property->ID, 'property_address', true),
            'status' => get_post_meta($property->ID, 'property_status', true),
            'listing_status' => get_post_meta($property->ID, 'listing_status', true),
            'bedrooms' => get_post_meta($property->ID, 'property_bedrooms', true),
            'bathrooms' => get_post_meta($property->ID, 'property_bathrooms', true),
            'area' => get_post_meta($property->ID, 'property_area', true),
            'price' => get_post_meta($property->ID, 'property_price', true),
            'owner' => get_the_author_meta('display_name', $property->post_author),
            'permalink' => get_permalink($property->ID),
            'can_edit' => current_user_can('edit_post', $property->ID),
            'edit_link' => admin_url('post.php?post=' . $property->ID . '&action=edit')
        ];
    }
    
    public static function transformCollection($properties) {
        return array_map([self::class, 'transform'], $properties);
    }
}
```

#### 1.2 Create Similar Transformers for:
- `VillaProjectTransformer`
- `VillaGroupTransformer`
- `VillaAnnouncementTransformer`
- `VillaTicketTransformer`
- `VillaUserTransformer`

### Phase 2: Refactor Dashboard Controllers (Day 2)

#### 2.1 Create Base Dashboard Controller
```php
// mu-plugins/villa-dashboard-controller.php

class VillaDashboardController {
    protected $timber;
    
    public function __construct() {
        $this->timber = new Timber\Timber();
    }
    
    protected function render($template, $context = []) {
        // Add global context
        $context = array_merge($context, [
            'user' => wp_get_current_user(),
            'nonce' => wp_create_nonce('villa_dashboard'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'theme_url' => get_template_directory_uri()
        ]);
        
        return Timber::compile($template, $context);
    }
    
    protected function jsonResponse($data, $success = true) {
        if ($success) {
            wp_send_json_success($data);
        } else {
            wp_send_json_error($data);
        }
    }
}
```

#### 2.2 Create Section Controllers
```php
// mu-plugins/villa-properties-controller.php

class VillaPropertiesController extends VillaDashboardController {
    
    public function renderList() {
        $user = wp_get_current_user();
        $properties = villa_get_user_properties($user->ID);
        
        $context = [
            'properties' => VillaPropertyTransformer::transformCollection($properties),
            'total_count' => count($properties),
            'active_tab' => 'properties'
        ];
        
        return $this->render('dashboard/properties-list.twig', $context);
    }
    
    public function renderGrid() {
        $user = wp_get_current_user();
        $properties = villa_get_user_properties($user->ID);
        
        $context = [
            'items' => VillaPropertyTransformer::transformCollection($properties),
            'grid_variant' => 'cards',
            'columns' => ['sm' => 1, 'md' => 2, 'lg' => 3]
        ];
        
        return $this->render('dashboard/properties-grid.twig', $context);
    }
    
    public function ajaxLoadProperties() {
        check_ajax_referer('villa_dashboard', 'nonce');
        
        $user = wp_get_current_user();
        $properties = villa_get_user_properties($user->ID);
        
        $this->jsonResponse([
            'properties' => VillaPropertyTransformer::transformCollection($properties),
            'html' => $this->renderGrid()
        ]);
    }
}
```

### Phase 3: Create Twig Templates (Day 3)

#### 3.1 Dashboard Layout Template
```twig
{# templates/dashboard/layout.twig #}
<div class="villa-dashboard">
    {% include 'dashboard/sidebar.twig' %}
    
    <div class="dashboard-content">
        {% block dashboard_content %}
            {# Content will be inserted here #}
        {% endblock %}
    </div>
</div>
```

#### 3.2 Properties Grid Template
```twig
{# templates/dashboard/properties-grid.twig #}
{% extends 'dashboard/layout.twig' %}

{% block dashboard_content %}
    <div class="dashboard-section">
        <h2>My Properties</h2>
        
        {% include 'component-books/grid-book.twig' with {
            variant: 'cards',
            columns: columns,
            items: items|map(item => {
                'property': item,
                'show_owner': false,
                'show_actions': true
            }),
            item_component: 'component-books/property-card-book.twig'
        } %}
    </div>
{% endblock %}
```

#### 3.3 Dashboard Stats Template
```twig
{# templates/dashboard/stats.twig #}
<div class="dashboard-stats">
    {% include 'component-books/grid-book.twig' with {
        variant: 'stats',
        columns: {sm: 2, md: 4},
        items: [
            {
                value: stats.total_properties,
                label: 'Total Properties',
                icon: 'home',
                color: 'primary',
                href: '?tab=properties'
            },
            {
                value: stats.active_tickets,
                label: 'Active Tickets',
                icon: 'ticket',
                color: stats.active_tickets > 0 ? 'warning' : 'success',
                trend: {
                    direction: 'up',
                    value: '3',
                    label: 'this week'
                },
                href: '?tab=tickets'
            },
            {
                value: stats.upcoming_meetings,
                label: 'Upcoming Meetings',
                icon: 'calendar',
                color: 'info',
                href: '?tab=groups'
            },
            {
                value: stats.unread_announcements,
                label: 'Unread News',
                icon: 'bell',
                color: stats.unread_announcements > 0 ? 'danger' : 'neutral',
                href: '?tab=announcements'
            }
        ],
        item_component: 'component-books/stat-card-book.twig'
    } %}
</div>
```

### Phase 4: Update Shortcode Handler (Day 4)

#### 4.1 Refactor Main Dashboard Shortcode
```php
// mu-plugins/villa-frontend-dashboard-refactored.php

function villa_dashboard_shortcode_refactored($atts) {
    // Initialize controllers
    $controllers = [
        'properties' => new VillaPropertiesController(),
        'projects' => new VillaProjectsController(),
        'groups' => new VillaGroupsController(),
        'tickets' => new VillaTicketsController(),
        'announcements' => new VillaAnnouncementsController()
    ];
    
    // Get current tab
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
    
    // Prepare context
    $context = [
        'user' => wp_get_current_user(),
        'active_tab' => $active_tab,
        'tabs' => villa_get_dashboard_tabs(),
        'stats' => villa_get_dashboard_stats()
    ];
    
    // Handle tab content
    if ($active_tab === 'dashboard') {
        $context['content'] = Timber::compile('dashboard/home.twig', $context);
    } elseif (isset($controllers[$active_tab])) {
        $context['content'] = $controllers[$active_tab]->renderList();
    }
    
    // Render main dashboard
    return Timber::compile('dashboard/main.twig', $context);
}
add_shortcode('villa_dashboard_new', 'villa_dashboard_shortcode_refactored');
```

### Phase 5: Progressive Migration (Days 5-6)

#### 5.1 Migration Strategy
1. Keep both old and new systems running in parallel
2. Use feature flags to switch between them
3. Migrate one section at a time
4. Test thoroughly before switching

#### 5.2 Feature Flag Implementation
```php
function villa_use_new_dashboard() {
    // Start with false, switch to true as sections are ready
    return get_option('villa_use_component_dashboard', false);
}

function villa_dashboard_shortcode($atts) {
    if (villa_use_new_dashboard()) {
        return villa_dashboard_shortcode_refactored($atts);
    }
    return villa_dashboard_shortcode_legacy($atts);
}
```

### Phase 6: Integration with Design Book (Day 7)

#### 6.1 Add Component Preview Routes
```php
// In design-book.php
function villa_design_book_component_preview() {
    $component = $_GET['component'] ?? '';
    $variant = $_GET['variant'] ?? 'default';
    
    // Sample data for previews
    $sample_data = [
        'property-card' => [
            'property' => [
                'title' => '123 Ocean View Drive',
                'image' => '/path/to/sample.jpg',
                'address' => '123 Ocean View Drive, Unit 4B',
                'status' => 'available',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'price' => '$450,000'
            ]
        ]
    ];
    
    $context = $sample_data[$component] ?? [];
    $context['variant'] = $variant;
    
    return Timber::compile("component-books/{$component}-book.twig", $context);
}
```

## Implementation Checklist

### Week 1:
- [ ] Create data transformer classes for all CPTs
- [ ] Create base dashboard controller
- [ ] Create section-specific controllers
- [ ] Set up Twig template structure

### Week 2:
- [ ] Convert properties section to components
- [ ] Convert projects section to components
- [ ] Convert groups section to components
- [ ] Convert announcements section to components

### Week 3:
- [ ] Implement AJAX handlers with JSON responses
- [ ] Add loading states and error handling
- [ ] Integrate with Design Book preview system
- [ ] Performance optimization and caching

### Testing Checklist:
- [ ] All dashboard tabs load correctly
- [ ] AJAX functionality works
- [ ] Mobile responsive behavior
- [ ] User permissions respected
- [ ] No regression in functionality

## Benefits After Refactor:
1. **Clean separation** of data, logic, and presentation
2. **Reusable components** across the entire system
3. **Easier testing** with isolated components
4. **Better performance** with optimized queries
5. **Maintainable code** with clear structure
6. **Visual consistency** through Design Book components