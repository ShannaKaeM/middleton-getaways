<?php
/**
 * The front page template file
 *
 * @package miGV
 * @version 1.0.0
 */

$context = Timber\Timber::context();

// Add any custom data for the home page
$context['hero_title'] = 'Luxurious Furniture Starts with the Best Quality Materials';
$context['hero_subtitle'] = '';
$context['hero_description'] = 'Donec et odio pellentesque diam volutpat commodo amet consectetur adipiscing elit ut aliquam purus vitae et leo duis ut diam quam nulla porttitor. Sodales ut eu sem integer vitae justo eget magna.';

// Add furniture gallery content for the hero
$context['furniture_gallery'] = '
<div class="furniture-gallery" style="
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--wp--custom--layout--spacing--lg);
    height: 100%;
">
    <div class="main-image" style="
        background: var(--wp--preset--color--base-lightest);
        border-radius: var(--wp--custom--layout--border-radius--lg);
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 400px;
        position: relative;
        overflow: hidden;
    ">
        <div style="
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--wp--preset--color--base-lightest) 0%, var(--wp--preset--color--neutral-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--wp--preset--color--base-dark);
            font-family: var(--wp--preset--font-family--montserrat);
            font-size: var(--wp--preset--font-size--large);
        ">
            Main Furniture Showcase
        </div>
    </div>
    <div class="side-images" style="
        display: grid;
        grid-template-rows: 1fr 1fr;
        gap: var(--wp--custom--layout--spacing--lg);
    ">
        <div style="
            background: var(--wp--preset--color--neutral-light);
            border-radius: var(--wp--custom--layout--border-radius--lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--wp--preset--color--base-dark);
            font-family: var(--wp--preset--font-family--montserrat);
        ">
            Detail View 1
        </div>
        <div style="
            background: var(--wp--preset--color--neutral);
            border-radius: var(--wp--custom--layout--border-radius--lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--wp--preset--color--extreme-light);
            font-family: var(--wp--preset--font-family--montserrat);
        ">
            Detail View 2
        </div>
    </div>
</div>';

// Get recent blog posts
$context['recent_posts'] = Timber\Timber::get_posts([
    'post_type' => 'post',
    'posts_per_page' => 3
]);

// Render the template
Timber\Timber::render('front-page.twig', $context);