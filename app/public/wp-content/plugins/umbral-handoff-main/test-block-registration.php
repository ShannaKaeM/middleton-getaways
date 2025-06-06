<?php
/**
 * Test block registration
 */

// Test if block directory exists
$block_dir = plugin_dir_path(__FILE__) . 'blocks/components';
echo "Block directory exists: " . (is_dir($block_dir) ? 'YES' : 'NO') . "\n";
echo "Block directory path: " . $block_dir . "\n";

// Test if block.json exists
$block_json = $block_dir . '/block.json';
echo "Block.json exists: " . (file_exists($block_json) ? 'YES' : 'NO') . "\n";

// Test if block.json is readable
if (file_exists($block_json)) {
    $json_content = file_get_contents($block_json);
    echo "Block.json content length: " . strlen($json_content) . "\n";
    
    $decoded = json_decode($json_content, true);
    echo "JSON decode success: " . (json_last_error() === JSON_ERROR_NONE ? 'YES' : 'NO') . "\n";
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON error: " . json_last_error_msg() . "\n";
    }
}

// Test block registration
echo "\nAttempting to register block...\n";
$result = register_block_type($block_dir);
if ($result) {
    echo "Block registration SUCCESS\n";
    echo "Block name: " . $result->name . "\n";
    echo "Block title: " . $result->title . "\n";
} else {
    echo "Block registration FAILED\n";
}

// Check if block is registered
$registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
if (isset($registered_blocks['umbral-editor/components'])) {
    echo "Block is in registry: YES\n";
} else {
    echo "Block is in registry: NO\n";
    echo "Available blocks: " . implode(', ', array_keys($registered_blocks)) . "\n";
}