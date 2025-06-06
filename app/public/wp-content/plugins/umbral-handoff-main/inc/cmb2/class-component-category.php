<?php
/**
 * Component Category Management
 */

class UmbralEditor_Component_Category {
    
    /**
     * Initialize default components
     */
    public static function initDefaultComponents() {
        // Hero Components
        umbral_register_component('hero', 'hero_banner', [
            'label' => __('Hero Banner', 'umbral-editor'),
            'description' => __('Full-width banner with title, subtitle and background', 'umbral-editor'),
            'icon' => '🎯',
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'label' => __('Title', 'umbral-editor'),
                    'required' => true
                ],
                'subtitle' => [
                    'type' => 'textarea',
                    'label' => __('Subtitle', 'umbral-editor'),
                    'rows' => 3
                ],
                'background_image' => [
                    'type' => 'file',
                    'label' => __('Background Image', 'umbral-editor'),
                    'options' => ['url' => false]
                ],
                'button_text' => [
                    'type' => 'text',
                    'label' => __('Button Text', 'umbral-editor')
                ],
                'button_url' => [
                    'type' => 'text_url',
                    'label' => __('Button URL', 'umbral-editor')
                ],
                'alignment' => [
                    'type' => 'select',
                    'label' => __('Text Alignment', 'umbral-editor'),
                    'options' => [
                        'left' => __('Left', 'umbral-editor'),
                        'center' => __('Center', 'umbral-editor'),
                        'right' => __('Right', 'umbral-editor')
                    ],
                    'default' => 'center'
                ]
            ]
        ]);
        
        umbral_register_component('hero', 'hero_video', [
            'label' => __('Hero Video', 'umbral-editor'),
            'description' => __('Hero section with video background', 'umbral-editor'),
            'icon' => '🎬',
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'label' => __('Title', 'umbral-editor'),
                    'required' => true
                ],
                'subtitle' => [
                    'type' => 'textarea',
                    'label' => __('Subtitle', 'umbral-editor')
                ],
                'video_url' => [
                    'type' => 'oembed',
                    'label' => __('Video URL', 'umbral-editor')
                ],
                'fallback_image' => [
                    'type' => 'file',
                    'label' => __('Fallback Image', 'umbral-editor')
                ]
            ]
        ]);
        
        // Testimonial Components
        umbral_register_component('testimonials', 'testimonial_single', [
            'label' => __('Single Testimonial', 'umbral-editor'),
            'description' => __('Single testimonial with quote and author', 'umbral-editor'),
            'icon' => '💭',
            'fields' => [
                'quote' => [
                    'type' => 'textarea',
                    'label' => __('Quote', 'umbral-editor'),
                    'required' => true,
                    'rows' => 4
                ],
                'author_name' => [
                    'type' => 'text',
                    'label' => __('Author Name', 'umbral-editor'),
                    'required' => true
                ],
                'author_title' => [
                    'type' => 'text',
                    'label' => __('Author Title/Company', 'umbral-editor')
                ],
                'author_image' => [
                    'type' => 'file',
                    'label' => __('Author Photo', 'umbral-editor')
                ],
                'rating' => [
                    'type' => 'select',
                    'label' => __('Rating', 'umbral-editor'),
                    'options' => [
                        '5' => '★★★★★',
                        '4' => '★★★★☆',
                        '3' => '★★★☆☆',
                        '2' => '★★☆☆☆',
                        '1' => '★☆☆☆☆'
                    ]
                ]
            ]
        ]);
        
        umbral_register_component('testimonials', 'testimonial_grid', [
            'label' => __('Testimonial Grid', 'umbral-editor'),
            'description' => __('Grid of multiple testimonials', 'umbral-editor'),
            'icon' => '📊',
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'label' => __('Section Title', 'umbral-editor')
                ],
                'testimonials' => [
                    'type' => 'group',
                    'label' => __('Testimonials', 'umbral-editor'),
                    'repeatable' => true,
                    'options' => [
                        'group_title' => __('Testimonial {#}', 'umbral-editor'),
                        'add_button' => __('Add Testimonial', 'umbral-editor'),
                        'remove_button' => __('Remove Testimonial', 'umbral-editor'),
                        'sortable' => true
                    ],
                    'fields' => [
                        'quote' => [
                            'type' => 'textarea',
                            'label' => __('Quote', 'umbral-editor'),
                            'rows' => 3
                        ],
                        'author' => [
                            'type' => 'text',
                            'label' => __('Author', 'umbral-editor')
                        ],
                        'title' => [
                            'type' => 'text',
                            'label' => __('Title/Company', 'umbral-editor')
                        ]
                    ]
                ],
                'columns' => [
                    'type' => 'select',
                    'label' => __('Grid Columns', 'umbral-editor'),
                    'options' => [
                        '1' => __('1 Column', 'umbral-editor'),
                        '2' => __('2 Columns', 'umbral-editor'),
                        '3' => __('3 Columns', 'umbral-editor'),
                        '4' => __('4 Columns', 'umbral-editor')
                    ],
                    'default' => '3'
                ]
            ]
        ]);
        
        // Content Components
        umbral_register_component('content', 'text_block', [
            'label' => __('Text Block', 'umbral-editor'),
            'description' => __('Rich text content block', 'umbral-editor'),
            'icon' => '📝',
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'label' => __('Title', 'umbral-editor')
                ],
                'content' => [
                    'type' => 'wysiwyg',
                    'label' => __('Content', 'umbral-editor'),
                    'options' => [
                        'textarea_rows' => 8,
                        'media_buttons' => true
                    ]
                ],
                'text_align' => [
                    'type' => 'select',
                    'label' => __('Text Alignment', 'umbral-editor'),
                    'options' => [
                        'left' => __('Left', 'umbral-editor'),
                        'center' => __('Center', 'umbral-editor'),
                        'right' => __('Right', 'umbral-editor')
                    ],
                    'default' => 'left'
                ]
            ]
        ]);
        
        umbral_register_component('content', 'image_text', [
            'label' => __('Image + Text', 'umbral-editor'),
            'description' => __('Image with accompanying text content', 'umbral-editor'),
            'icon' => '🖼️',
            'fields' => [
                'image' => [
                    'type' => 'file',
                    'label' => __('Image', 'umbral-editor'),
                    'required' => true
                ],
                'title' => [
                    'type' => 'text',
                    'label' => __('Title', 'umbral-editor')
                ],
                'content' => [
                    'type' => 'wysiwyg',
                    'label' => __('Content', 'umbral-editor')
                ],
                'layout' => [
                    'type' => 'select',
                    'label' => __('Layout', 'umbral-editor'),
                    'options' => [
                        'image_left' => __('Image Left', 'umbral-editor'),
                        'image_right' => __('Image Right', 'umbral-editor'),
                        'image_top' => __('Image Top', 'umbral-editor')
                    ],
                    'default' => 'image_left'
                ],
                'image_size' => [
                    'type' => 'select',
                    'label' => __('Image Size', 'umbral-editor'),
                    'options' => [
                        'small' => __('Small (33%)', 'umbral-editor'),
                        'medium' => __('Medium (50%)', 'umbral-editor'),
                        'large' => __('Large (66%)', 'umbral-editor')
                    ],
                    'default' => 'medium'
                ]
            ]
        ]);
    }
}

// Initialize default components when CMB2 is ready
add_action('cmb2_init', ['UmbralEditor_Component_Category', 'initDefaultComponents'], 20);