<?php
/**
 * Manage allowed blocks - Keep only GenerateBlocks and essential core blocks
 * Add this to your child theme's functions.php
 */

add_filter( 'allowed_block_types_all', 'mg_allowed_block_types', 10, 2 );

function mg_allowed_block_types( $allowed_blocks, $editor_context ) {
    // Define which blocks to allow
    $allowed_blocks = array(
        // GenerateBlocks
        'generateblocks/container',
        'generateblocks/button',
        'generateblocks/headline',
        'generateblocks/text',
        'generateblocks/image',
        'generateblocks/button-container',
        'generateblocks/grid',
        
        // Essential Core Blocks (keep these for flexibility)
        'core/paragraph',
        'core/heading',
        'core/html', // Custom HTML block
        'core/shortcode',
        'core/block', // Reusable blocks
        
        // Optional: Add more as needed
        // 'core/image',
        // 'core/list',
    );
    
    return $allowed_blocks;
}

/**
 * Alternative: Remove specific core blocks but keep others
 */
// add_filter( 'allowed_block_types_all', 'mg_remove_specific_blocks', 10, 2 );
// 
// function mg_remove_specific_blocks( $allowed_blocks, $editor_context ) {
//     // Get all registered blocks
//     $registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
//     
//     // Blocks to remove
//     $blocks_to_remove = array(
//         'core/verse',
//         'core/pullquote',
//         'core/preformatted',
//         'core/code',
//         'core/table',
//         'core/calendar',
//         'core/rss',
//         'core/search',
//         'core/tag-cloud',
//         'core/latest-comments',
//         'core/archives',
//         'core/categories',
//     );
//     
//     // Create allowed list by removing unwanted blocks
//     $allowed_blocks = array_keys( $registered_blocks );
//     $allowed_blocks = array_diff( $allowed_blocks, $blocks_to_remove );
//     
//     return array_values( $allowed_blocks );
// }
