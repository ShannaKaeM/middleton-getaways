<?php
/**
 * Simple test script to verify breakpoints functionality
 * Access at: /wp-content/plugins/umbral-editor/test-breakpoints.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Make sure we're logged in as admin
if (!current_user_can('manage_options')) {
    die('You must be logged in as an administrator to run this test.');
}

echo "<h1>Umbral Editor Breakpoints Test</h1>";

// Test 1: Check if breakpoints class exists
echo "<h2>Test 1: Class Availability</h2>";
if (class_exists('UmbralEditor_Breakpoints')) {
    echo "✅ UmbralEditor_Breakpoints class exists<br>";
    $breakpoints = UmbralEditor_Breakpoints::getInstance();
    echo "✅ Singleton instance created<br>";
} else {
    echo "❌ UmbralEditor_Breakpoints class not found<br>";
    die();
}

// Test 2: Get default breakpoints
echo "<h2>Test 2: Default Breakpoints</h2>";
$defaults = $breakpoints->getDefaults();
echo "<pre>" . print_r($defaults, true) . "</pre>";

// Test 3: Get current breakpoints
echo "<h2>Test 3: Current Breakpoints</h2>";
$current = $breakpoints->getBreakpoints();
echo "<pre>" . print_r($current, true) . "</pre>";

// Test 4: API format
echo "<h2>Test 4: API Format</h2>";
$api_format = $breakpoints->getBreakpointsForAPI();
echo "<pre>" . print_r($api_format, true) . "</pre>";

// Test 5: Media queries
echo "<h2>Test 5: Media Queries</h2>";
$queries = $breakpoints->getAllMediaQueries();
foreach ($queries as $key => $query) {
    echo "<strong>{$key}:</strong> {$query}<br>";
}

// Test 6: Test REST API endpoint
echo "<h2>Test 6: REST API Status</h2>";
$rest_url = rest_url('umbral-editor/v1/breakpoints');
echo "REST URL: <a href='{$rest_url}' target='_blank'>{$rest_url}</a><br>";

// Test 7: Validate a breakpoint
echo "<h2>Test 7: Breakpoint Validation</h2>";
$test_bp = [
    'label' => 'Test Breakpoint',
    'min_width' => 500,
    'max_width' => 799,
    'icon' => '🧪',
    'description' => 'Test device'
];

// Use reflection to access private method for testing
$reflection = new ReflectionClass($breakpoints);
$validateMethod = $reflection->getMethod('validateBreakpoint');
$validateMethod->setAccessible(true);

try {
    $validated = $validateMethod->invoke($breakpoints, $test_bp);
    echo "✅ Validation passed<br>";
    echo "<pre>" . print_r($validated, true) . "</pre>";
} catch (Exception $e) {
    echo "❌ Validation failed: " . $e->getMessage() . "<br>";
}

echo "<br><strong>All tests completed!</strong>";
?>