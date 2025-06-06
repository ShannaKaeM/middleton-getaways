<?php
/**
 * Field Renderer - Handles frontend rendering of components
 */

class UmbralEditor_Field_Renderer {
    
    /**
     * Render components data for frontend display
     */
    public static function render($components_data, $args = []) {
        if (!is_array($components_data) || empty($components_data)) {
            return '';
        }
        
        $defaults = [
            'wrapper_class' => 'umbral-components-wrapper',
            'component_class' => 'umbral-component',
            'echo' => true
        ];
        
        $args = wp_parse_args($args, $defaults);
        $registry = UmbralEditor_Component_Registry::getInstance();
        
        ob_start();
        
        echo '<div class="' . esc_attr($args['wrapper_class']) . '">';
        
        foreach ($components_data as $component_data) {
            if (!$registry->validateComponent($component_data)) {
                continue;
            }
            
            $component_def = $registry->getComponent($component_data['category'], $component_data['component']);
            if (!$component_def) {
                continue;
            }
            
            echo '<div class="' . esc_attr($args['component_class']) . ' component-' . esc_attr($component_data['category']) . '-' . esc_attr($component_data['component']) . '" data-component-id="' . esc_attr($component_data['id']) . '">';
            
            self::renderSingleComponent($component_data, $component_def);
            
            echo '</div>';
        }
        
        echo '</div>';
        
        $output = ob_get_clean();
        
        if ($args['echo']) {
            echo $output;
        }
        
        return $output;
    }
    
    /**
     * Render a single component
     */
    private static function renderSingleComponent($component_data, $component_def) {
        $category = $component_data['category'];
        $component = $component_data['component'];
        $fields = $component_data['fields'] ?? [];
        
        // Check for custom template
        $template_file = self::getTemplatePath($category, $component);
        
        if ($template_file && file_exists($template_file)) {
            // Use custom template
            include $template_file;
        } else {
            // Use default rendering
            self::renderDefaultComponent($component_data, $component_def);
        }
    }
    
    /**
     * Get template path for component
     */
    private static function getTemplatePath($category, $component) {
        $theme_path = get_template_directory() . "/umbral-components/{$category}/{$component}.php";
        $child_theme_path = get_stylesheet_directory() . "/umbral-components/{$category}/{$component}.php";
        $plugin_path = UMBRAL_EDITOR_DIR . "templates/{$category}/{$component}.php";
        
        // Check child theme first, then parent theme, then plugin
        if (file_exists($child_theme_path)) {
            return $child_theme_path;
        } elseif (file_exists($theme_path)) {
            return $theme_path;
        } elseif (file_exists($plugin_path)) {
            return $plugin_path;
        }
        
        return null;
    }
    
    /**
     * Default component rendering
     */
    private static function renderDefaultComponent($component_data, $component_def) {
        $category = $component_data['category'];
        $component = $component_data['component'];
        $fields = $component_data['fields'] ?? [];
        
        echo '<div class="component-inner">';
        
        switch ($category) {
            case 'hero':
                self::renderHeroComponent($component, $fields);
                break;
                
            case 'testimonials':
                self::renderTestimonialComponent($component, $fields);
                break;
                
            case 'content':
                self::renderContentComponent($component, $fields);
                break;
                
            default:
                self::renderGenericComponent($component_def, $fields);
        }
        
        echo '</div>';
    }
    
    /**
     * Render hero components
     */
    private static function renderHeroComponent($component, $fields) {
        switch ($component) {
            case 'hero_banner':
                $alignment = $fields['alignment'] ?? 'center';
                $bg_image = !empty($fields['background_image']) ? wp_get_attachment_url($fields['background_image']) : '';
                
                echo '<div class="hero-banner text-' . esc_attr($alignment) . '"';
                if ($bg_image) {
                    echo ' style="background-image: url(' . esc_url($bg_image) . ');"';
                }
                echo '>';
                
                if (!empty($fields['title'])) {
                    echo '<h1 class="hero-title">' . esc_html($fields['title']) . '</h1>';
                }
                
                if (!empty($fields['subtitle'])) {
                    echo '<p class="hero-subtitle">' . esc_html($fields['subtitle']) . '</p>';
                }
                
                if (!empty($fields['button_text']) && !empty($fields['button_url'])) {
                    echo '<a href="' . esc_url($fields['button_url']) . '" class="hero-button">' . esc_html($fields['button_text']) . '</a>';
                }
                
                echo '</div>';
                break;
                
            case 'hero_video':
                echo '<div class="hero-video">';
                
                if (!empty($fields['title'])) {
                    echo '<h1 class="hero-title">' . esc_html($fields['title']) . '</h1>';
                }
                
                if (!empty($fields['subtitle'])) {
                    echo '<p class="hero-subtitle">' . esc_html($fields['subtitle']) . '</p>';
                }
                
                if (!empty($fields['video_url'])) {
                    echo '<div class="hero-video-embed">' . wp_oembed_get($fields['video_url']) . '</div>';
                }
                
                echo '</div>';
                break;
        }
    }
    
    /**
     * Render testimonial components
     */
    private static function renderTestimonialComponent($component, $fields) {
        switch ($component) {
            case 'testimonial_single':
                echo '<div class="testimonial-single">';
                
                if (!empty($fields['quote'])) {
                    echo '<blockquote class="testimonial-quote">' . esc_html($fields['quote']) . '</blockquote>';
                }
                
                echo '<div class="testimonial-author">';
                
                if (!empty($fields['author_image'])) {
                    $image_url = wp_get_attachment_url($fields['author_image']);
                    echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($fields['author_name'] ?? '') . '" class="author-photo">';
                }
                
                if (!empty($fields['author_name'])) {
                    echo '<cite class="author-name">' . esc_html($fields['author_name']) . '</cite>';
                }
                
                if (!empty($fields['author_title'])) {
                    echo '<span class="author-title">' . esc_html($fields['author_title']) . '</span>';
                }
                
                if (!empty($fields['rating'])) {
                    echo '<div class="testimonial-rating">';
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= intval($fields['rating']) ? '★' : '☆';
                    }
                    echo '</div>';
                }
                
                echo '</div></div>';
                break;
                
            case 'testimonial_grid':
                $columns = $fields['columns'] ?? 3;
                
                echo '<div class="testimonial-grid columns-' . esc_attr($columns) . '">';
                
                if (!empty($fields['title'])) {
                    echo '<h2 class="testimonial-grid-title">' . esc_html($fields['title']) . '</h2>';
                }
                
                if (!empty($fields['testimonials']) && is_array($fields['testimonials'])) {
                    echo '<div class="testimonial-grid-items">';
                    
                    foreach ($fields['testimonials'] as $testimonial) {
                        echo '<div class="testimonial-item">';
                        
                        if (!empty($testimonial['quote'])) {
                            echo '<p class="testimonial-quote">' . esc_html($testimonial['quote']) . '</p>';
                        }
                        
                        if (!empty($testimonial['author'])) {
                            echo '<cite class="testimonial-author">' . esc_html($testimonial['author']);
                            
                            if (!empty($testimonial['title'])) {
                                echo '<span class="author-title">, ' . esc_html($testimonial['title']) . '</span>';
                            }
                            
                            echo '</cite>';
                        }
                        
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
                
                echo '</div>';
                break;
        }
    }
    
    /**
     * Render content components
     */
    private static function renderContentComponent($component, $fields) {
        switch ($component) {
            case 'text_block':
                $alignment = $fields['text_align'] ?? 'left';
                
                echo '<div class="text-block text-' . esc_attr($alignment) . '">';
                
                if (!empty($fields['title'])) {
                    echo '<h2 class="text-block-title">' . esc_html($fields['title']) . '</h2>';
                }
                
                if (!empty($fields['content'])) {
                    echo '<div class="text-block-content">' . wp_kses_post($fields['content']) . '</div>';
                }
                
                echo '</div>';
                break;
                
            case 'image_text':
                $layout = $fields['layout'] ?? 'image_left';
                $image_size = $fields['image_size'] ?? 'medium';
                
                echo '<div class="image-text layout-' . esc_attr($layout) . ' image-size-' . esc_attr($image_size) . '">';
                
                if (!empty($fields['image'])) {
                    $image_url = wp_get_attachment_url($fields['image']);
                    echo '<div class="image-text-image">';
                    echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($fields['title'] ?? '') . '">';
                    echo '</div>';
                }
                
                echo '<div class="image-text-content">';
                
                if (!empty($fields['title'])) {
                    echo '<h2 class="image-text-title">' . esc_html($fields['title']) . '</h2>';
                }
                
                if (!empty($fields['content'])) {
                    echo '<div class="image-text-text">' . wp_kses_post($fields['content']) . '</div>';
                }
                
                echo '</div></div>';
                break;
        }
    }
    
    /**
     * Generic component rendering fallback
     */
    private static function renderGenericComponent($component_def, $fields) {
        echo '<div class="generic-component">';
        
        if (!empty($component_def['label'])) {
            echo '<h3>' . esc_html($component_def['label']) . '</h3>';
        }
        
        foreach ($component_def['fields'] as $field_key => $field_def) {
            $value = $fields[$field_key] ?? '';
            
            if (!empty($value)) {
                echo '<div class="field-' . esc_attr($field_key) . '">';
                echo '<strong>' . esc_html($field_def['label']) . ':</strong> ';
                echo esc_html($value);
                echo '</div>';
            }
        }
        
        echo '</div>';
    }
}

// Helper function for themes
function umbral_render_components($components_data, $args = []) {
    return UmbralEditor_Field_Renderer::render($components_data, $args);
}