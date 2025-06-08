# 📋 Copy & Paste Patterns for WordPress

## 🏠 Single Property Card
**File:** `gb-pattern-card-html.html`  
**What it creates:** One property card with title, description, price, and buttons

### Copy everything below (including the HTML comments):

```html
<!-- wp:generateblocks/container {"className":"card @container/card"} -->
<div class="gb-container card @container/card">
  <!-- wp:generateblocks/container {"className":"card-content"} -->
  <div class="gb-container card-content">
    
    <!-- wp:paragraph {"className":"text-primary-600 text-sm uppercase tracking-wider"} -->
    <p class="text-primary-600 text-sm uppercase tracking-wider">Featured Property</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:heading {"level":3,"className":"card-title"} -->
    <h3 class="card-title">Mountain Vista Retreat</h3>
    <!-- /wp:heading -->
    
    <!-- wp:paragraph {"className":"card-description"} -->
    <p class="card-description">Escape to this luxurious mountain cabin with panoramic views, modern amenities, and direct access to hiking trails. Perfect for a romantic getaway or family adventure.</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:generateblocks/container {"className":"flex flex-wrap gap-2 my-4"} -->
    <div class="gb-container flex flex-wrap gap-2 my-4">
      <!-- wp:paragraph {"className":"px-3 py-1 bg-base-100 text-base-700 rounded-full text-sm"} -->
      <span class="px-3 py-1 bg-base-100 text-base-700 rounded-full text-sm">3 Bedrooms</span>
      <!-- /wp:paragraph -->
      <!-- wp:paragraph {"className":"px-3 py-1 bg-base-100 text-base-700 rounded-full text-sm"} -->
      <span class="px-3 py-1 bg-base-100 text-base-700 rounded-full text-sm">2 Baths</span>
      <!-- /wp:paragraph -->
      <!-- wp:paragraph {"className":"px-3 py-1 bg-base-100 text-base-700 rounded-full text-sm"} -->
      <span class="px-3 py-1 bg-base-100 text-base-700 rounded-full text-sm">Hot Tub</span>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:generateblocks/container -->
    
    <!-- wp:paragraph {"className":"text-2xl font-bold text-primary-700 mb-4"} -->
    <div class="text-2xl font-bold text-primary-700 mb-4">$350<span class="text-base font-normal text-base-600">/night</span></div>
    <!-- /wp:paragraph -->
    
    <!-- wp:generateblocks/container {"className":"card-footer"} -->
    <div class="gb-container card-footer">
      <!-- wp:generateblocks/button {"className":"btn btn-primary","text":"Book Now"} -->
      <a class="gb-button btn btn-primary" href="#">Book Now</a>
      <!-- /wp:generateblocks/button -->
      
      <!-- wp:generateblocks/button {"className":"btn btn-secondary","text":"View Details"} -->
      <a class="gb-button btn btn-secondary" href="#">View Details</a>
      <!-- /wp:generateblocks/button -->
    </div>
    <!-- /wp:generateblocks/container -->
    
  </div>
  <!-- /wp:generateblocks/container -->
</div>
<!-- /wp:generateblocks/container -->
```

---

## 🏘️ 3-Column Property Grid
**File:** `gb-pattern-grid.html`  
**What it creates:** Responsive grid with 3 property cards

### Copy everything below:

```html
<!-- wp:generateblocks/container {"className":"@container"} -->
<div class="gb-container @container">
  <!-- wp:generateblocks/container {"className":"grid grid-cols-1 @sm:grid-cols-2 @lg:grid-cols-3 gap-6"} -->
  <div class="gb-container grid grid-cols-1 @sm:grid-cols-2 @lg:grid-cols-3 gap-6">
    
    <!-- Card 1 -->
    <!-- wp:generateblocks/container {"className":"card"} -->
    <div class="gb-container card">
      <!-- wp:generateblocks/container {"className":"card-content"} -->
      <div class="gb-container card-content">
        <!-- wp:paragraph {"className":"text-primary-600 text-sm uppercase tracking-wider"} -->
        <p class="text-primary-600 text-sm uppercase tracking-wider">Mountain Escape</p>
        <!-- /wp:paragraph -->
        <!-- wp:heading {"level":3,"className":"card-title"} -->
        <h3 class="card-title">Alpine Chalet</h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph {"className":"card-description"} -->
        <p class="card-description">Cozy mountain retreat with stunning views and modern amenities.</p>
        <!-- /wp:paragraph -->
        <!-- wp:paragraph {"className":"text-xl font-bold text-primary-700"} -->
        <p class="text-xl font-bold text-primary-700">$250/night</p>
        <!-- /wp:paragraph -->
        <!-- wp:generateblocks/button {"className":"btn btn-primary btn-sm","text":"View Details"} -->
        <a class="gb-button btn btn-primary btn-sm" href="#">View Details</a>
        <!-- /wp:generateblocks/button -->
      </div>
      <!-- /wp:generateblocks/container -->
    </div>
    <!-- /wp:generateblocks/container -->
    
    <!-- Card 2 -->
    <!-- wp:generateblocks/container {"className":"card"} -->
    <div class="gb-container card">
      <!-- wp:generateblocks/container {"className":"card-content"} -->
      <div class="gb-container card-content">
        <!-- wp:paragraph {"className":"text-primary-600 text-sm uppercase tracking-wider"} -->
        <p class="text-primary-600 text-sm uppercase tracking-wider">Lakefront Living</p>
        <!-- /wp:paragraph -->
        <!-- wp:heading {"level":3,"className":"card-title"} -->
        <h3 class="card-title">Sunset Cottage</h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph {"className":"card-description"} -->
        <p class="card-description">Charming lakeside cottage with private dock and beach access.</p>
        <!-- /wp:paragraph -->
        <!-- wp:paragraph {"className":"text-xl font-bold text-primary-700"} -->
        <p class="text-xl font-bold text-primary-700">$350/night</p>
        <!-- /wp:paragraph -->
        <!-- wp:generateblocks/button {"className":"btn btn-primary btn-sm","text":"View Details"} -->
        <a class="gb-button btn btn-primary btn-sm" href="#">View Details</a>
        <!-- /wp:generateblocks/button -->
      </div>
      <!-- /wp:generateblocks/container -->
    </div>
    <!-- /wp:generateblocks/container -->
    
    <!-- Card 3 -->
    <!-- wp:generateblocks/container {"className":"card"} -->
    <div class="gb-container card">
      <!-- wp:generateblocks/container {"className":"card-content"} -->
      <div class="gb-container card-content">
        <!-- wp:paragraph {"className":"text-primary-600 text-sm uppercase tracking-wider"} -->
        <p class="text-primary-600 text-sm uppercase tracking-wider">City Retreat</p>
        <!-- /wp:paragraph -->
        <!-- wp:heading {"level":3,"className":"card-title"} -->
        <h3 class="card-title">Urban Loft</h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph {"className":"card-description"} -->
        <p class="card-description">Modern downtown loft with rooftop terrace and city views.</p>
        <!-- /wp:paragraph -->
        <!-- wp:paragraph {"className":"text-xl font-bold text-primary-700"} -->
        <p class="text-xl font-bold text-primary-700">$200/night</p>
        <!-- /wp:paragraph -->
        <!-- wp:generateblocks/button {"className":"btn btn-primary btn-sm","text":"View Details"} -->
        <a class="gb-button btn btn-primary btn-sm" href="#">View Details</a>
        <!-- /wp:generateblocks/button -->
      </div>
      <!-- /wp:generateblocks/container -->
    </div>
    <!-- /wp:generateblocks/container -->
    
  </div>
  <!-- /wp:generateblocks/container -->
</div>
<!-- /wp:generateblocks/container -->
```

---

## 📝 How to Use:

1. **Copy** the entire code block (including HTML comments)
2. **Go to WordPress** post/page editor
3. **Click** where you want the pattern
4. **Paste** (Ctrl/Cmd + V)
5. WordPress automatically converts to blocks!

## ✅ What You'll See:

- **Single Card:** Creates one styled property card
- **Grid:** Creates 3 cards in a responsive grid
- All styling comes from your Windpress CSS
- Fully editable in the block editor

## 🎯 Test Order:

1. Try the **Single Card** first
2. Then try the **Grid** pattern
3. Edit text/prices directly in the editor
4. Save as Reusable Block for future use!
