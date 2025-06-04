<?php
/**
 * Design System Core Functions
 *
 * This file contains functions related to the core design system,
 * such as generating CSS custom properties from primitive JSON files.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Generates CSS custom properties from primitive JSON files.
 *
 * Reads colors.json, typography.json, and spacing.json from the theme's
 * /primitives/ directory, converts their contents into CSS custom properties,
 * and returns them as a string wrapped in a :root {} block.
 *
 * @return string CSS custom properties string or empty string if no primitives found.
 */
function migv_generate_primitive_css_variables() {
    $primitive_files = [
        'colors'     => get_template_directory() . '/primitives/colors.json',
        'typography' => get_template_directory() . '/primitives/typography.json',
        'spacing'    => get_template_directory() . '/primitives/spacing.json',
        'borders'    => get_template_directory() . '/primitives/borders.json',
        'shadows'    => get_template_directory() . '/primitives/shadows.json',
        'layout'     => get_template_directory() . '/primitives/layout.json',
        // Add other primitive files here as needed
    ];

    $css_variables = [];

    foreach ( $primitive_files as $prefix => $file_path ) {
        if ( ! file_exists( $file_path ) ) {
            // Optionally log this: error_log( "Primitive file not found: " . $file_path );
            continue;
        }

        $json_content = file_get_contents( $file_path );
        if ( false === $json_content ) {
            // Optionally log this: error_log( "Could not read primitive file: " . $file_path );
            continue;
        }

        $data = json_decode( $json_content, true );
        if ( null === $data ) {
            // Optionally log this: error_log( "Invalid JSON in primitive file: " . $file_path );
            continue;
        }

        // Helper function to recursively generate CSS variables
        // We define it inside or make it a static helper if this function grows larger
        $generate_vars_recursive = function ( $array, $parent_key = '' ) use ( &$generate_vars_recursive, &$css_variables, $prefix ) {
            foreach ( $array as $key => $value ) {
                $css_var_name = $parent_key ? "--{$prefix}-{$parent_key}-{$key}" : "--{$prefix}-{$key}";
                // Sanitize key: replace underscores with hyphens for CSS convention, though CSS vars can have underscores.
                $css_var_name = str_replace( '_', '-', $css_var_name ); 

                if ( is_array( $value ) ) {
                    $generate_vars_recursive( $value, $parent_key ? "{$parent_key}-{$key}" : $key );
                } else {
                    $css_variables[] = esc_attr( $css_var_name ) . ': ' . esc_attr( $value ) . ';';
                }
            }
        };

        $generate_vars_recursive( $data );
    }

    if ( empty( $css_variables ) ) {
        return '';
    }

    return ":root {\n    " . implode( "\n    ", $css_variables ) . "\n}\n";
}

// Example usage (you would call this where you need to output the CSS):
// $generated_css = migv_generate_primitive_css_variables();
// if ( ! empty( $generated_css ) ) {
//     echo "<style id='migv-primitive-variables'>\n" . $generated_css . "</style>\n";
// }
