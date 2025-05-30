# Printer Component Documentation

## Overview

The Printer Blade component provides a convenient way to display media in your Laravel views with automatic handling of cropped versions, responsive images, and fallback defaults. It wraps the functionality of the Parser and Printer classes into an easy-to-use Blade component.

## Table of Contents

1. [Basic Usage](#basic-usage)
2. [Component Attributes](#component-attributes)
3. [Cropped Images Priority](#cropped-images-priority)
4. [Output Types](#output-types)
5. [Responsive Images](#responsive-images)
6. [Default Images](#default-images)
7. [HTML Attributes](#html-attributes)
8. [Advanced Examples](#advanced-examples)

## Basic Usage

### Simple Image Display

```blade
{{-- Display media with automatic crop detection --}}
<x-mediaclass::printer :model="$parser" />

{{-- Specify size --}}
<x-mediaclass::printer :model="$parser" size="lg" />

{{-- Add CSS class --}}
<x-mediaclass::printer :model="$parser" class="img-fluid rounded" />
```

### With Media from Mediaclass

```blade
@php
    $media = Mediaclass::forModel($product, 'gallery')->first();
@endphp

@if($media)
    <x-mediaclass::printer :model="$media" size="md" />
@endif
```

## Component Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `model` | Parser\|null | null | Parser instance containing media data |
| `size` | string | 'sm' | Image size (xs, sm, md, lg, xl, cropped) |
| `type` | string | 'img' | Output type (img, url, picture) |
| `class` | string\|null | null | CSS class for the image |
| `alt` | string\|null | null | Alt text for the image |
| `params` | array | [] | Additional HTML attributes |
| `default` | string\|bool | true | Default image behavior |
| `responsive` | bool | true | Generate responsive srcset/sizes |
| `breakpoints` | array | [] | Custom breakpoints for picture element |
| `loading` | string\|null | null | Loading attribute (lazy, eager, auto) |
| `id` | string\|null | null | HTML id attribute |
| `data` | array | [] | Data attributes (auto-prefixed with data-) |
| `preferCropped` | bool | true | Prefer cropped version if available |
| `cropKey` | string\|null | null | Specific crop variation to use |

## Cropped Images Priority

The component automatically prioritizes cropped versions of images when available:

### Default Behavior (preferCropped=true)

```blade
{{-- If a cropped version exists, it will be used automatically --}}
<x-mediaclass::printer :model="$parser" />

{{-- Disable automatic crop preference --}}
<x-mediaclass::printer :model="$parser" :preferCropped="false" />
```

### Specific Crop Variations

```blade
{{-- Use a specific crop variation if it exists --}}
<x-mediaclass::printer :model="$parser" cropKey="banner" />

{{-- Falls back to requested size if crop doesn't exist --}}
<x-mediaclass::printer :model="$parser" cropKey="thumbnail" size="md" />
```

### Example with Multiple Crops

```blade
<div class="product-images">
    {{-- Main image - uses default crop if available --}}
    <div class="main-image">
        <x-mediaclass::printer :model="$parser" size="xl" />
    </div>
    
    {{-- Thumbnail - uses specific thumbnail crop --}}
    <div class="thumbnail">
        <x-mediaclass::printer :model="$parser" cropKey="thumbnail" />
    </div>
    
    {{-- Mobile banner - uses mobile-specific crop --}}
    <div class="mobile-banner d-block d-md-none">
        <x-mediaclass::printer :model="$parser" cropKey="mobile" />
    </div>
</div>
```

## Output Types

### Image Tag (default)

```blade
{{-- Generates: <img src="..." alt="..." /> --}}
<x-mediaclass::printer :model="$parser" type="img" />
```

### URL Only

```blade
{{-- Returns just the URL string --}}
<img src="<x-mediaclass::printer :model="$parser" type="url" />" 
     alt="Custom alt">

{{-- Use in CSS --}}
<div style="background-image: url('<x-mediaclass::printer :model="$parser" type="url" size="xl" />')">
</div>
```

### Picture Element

```blade
{{-- Generates responsive picture element with smart crop detection --}}
<x-mediaclass::printer :model="$parser" type="picture" />

{{-- With custom breakpoints --}}
<x-mediaclass::printer 
    :model="$parser" 
    type="picture"
    :breakpoints="[
        'max-width: 480px' => 'sm',
        'max-width: 768px' => 'md',
        'min-width: 769px' => 'xl'
    ]"
/>
```

## Responsive Images

### Default Responsive Behavior

```blade
{{-- Generates img with srcset and sizes attributes --}}
<x-mediaclass::printer :model="$parser" />
```

### Disable Responsive

```blade
{{-- Simple img tag without srcset/sizes --}}
<x-mediaclass::printer :model="$parser" :responsive="false" />
```

### Smart Picture Elements

When using `type="picture"` without custom breakpoints, the component generates smart breakpoints that consider available crops:

```blade
{{-- Automatically uses mobile/tablet crops if they exist --}}
<x-mediaclass::printer :model="$parser" type="picture" />

{{-- Generated output might be:
<picture>
    <source media="(max-width: 640px)" srcset="/path/to/mobile_crop.jpg">
    <source media="(max-width: 1024px)" srcset="/path/to/tablet_crop.jpg">
    <source media="(min-width: 1025px)" srcset="/path/to/cropped.jpg">
    <img src="/path/to/xl.jpg" alt="..." loading="lazy" />
</picture>
--}}
```

## Default Images

### Using System Default

```blade
{{-- Shows default image if model is null --}}
<x-mediaclass::printer :model="$maybeNullParser" />

{{-- Disable default image --}}
<x-mediaclass::printer :model="$maybeNullParser" :default="false" />
```

### Custom Default Image

```blade
{{-- Use custom placeholder --}}
<x-mediaclass::printer 
    :model="$parser" 
    default="/assets/custom-placeholder.jpg"
/>
```

## HTML Attributes

### Basic Attributes

```blade
{{-- ID and CSS class --}}
<x-mediaclass::printer 
    :model="$parser"
    id="product-image"
    class="img-fluid shadow"
/>

{{-- Alt text and loading --}}
<x-mediaclass::printer 
    :model="$parser"
    alt="Product main image"
    loading="eager"
/>
```

### Data Attributes

```blade
{{-- Data attributes are auto-prefixed --}}
<x-mediaclass::printer 
    :model="$parser"
    :data="[
        'zoom' => 'true',
        'gallery' => 'product',
        'index' => $loop->index
    ]"
/>
{{-- Generates: data-zoom="true" data-gallery="product" data-index="0" --}}
```

### Custom Attributes

```blade
{{-- Any additional attributes via params --}}
<x-mediaclass::printer 
    :model="$parser"
    :params="[
        'width' => '800',
        'height' => '600',
        'fetchpriority' => 'high',
        'role' => 'img',
        'aria-label' => 'Product showcase'
    ]"
/>
```

## Advanced Examples

### Product Gallery with Lightbox

```blade
<div class="product-gallery">
    @foreach($product->media as $media)
        @php
            $parser = new \MetaFramework\Mediaclass\Parser($media);
        @endphp
        
        <div class="gallery-item">
            <x-mediaclass::printer 
                :model="$parser"
                size="md"
                class="gallery-image"
                :data="[
                    'fancybox' => 'gallery',
                    'caption' => $parser->description,
                    'src' => $parser->url
                ]"
            />
        </div>
    @endforeach
</div>
```

### Hero Section with Art Direction

```blade
<section class="hero">
    <x-mediaclass::printer 
        :model="$heroImage"
        type="picture"
        class="hero-image"
        loading="eager"
        :breakpoints="[
            'max-width: 640px' => 'crop_mobile',
            'max-width: 1024px' => 'crop_tablet', 
            'min-width: 1025px' => 'cropped'
        ]"
        :params="['fetchpriority' => 'high']"
    />
    
    <div class="hero-content">
        <h1>{{ $page->title }}</h1>
    </div>
</section>
```

### Blog Post with Fallback

```blade
<article class="blog-post">
    <header class="post-header">
        {{-- Featured image with fallback --}}
        @php
            $featured = Mediaclass::forModel($post, 'featured')->first();
        @endphp
        
        <x-mediaclass::printer 
            :model="$featured"
            size="xl"
            class="featured-image"
            alt="{{ $post->title }} - Featured Image"
            default="/assets/blog-placeholder.jpg"
            loading="eager"
        />
    </header>
    
    <div class="post-content">
        {{ $post->content }}
    </div>
</article>
```

### E-commerce Product Card

```blade
<div class="product-card">
    {{-- Thumbnail with hover effect --}}
    @php
        $thumbnail = Mediaclass::forModel($product, 'thumbnail')->first();
    @endphp
    
    <div class="product-image-wrapper">
        <x-mediaclass::printer 
            :model="$thumbnail"
            cropKey="square"
            class="product-thumbnail"
            :data="[
                'product-id' => $product->id,
                'hover-zoom' => 'true'
            ]"
            :responsive="false"
        />
        
        @if($product->is_new)
            <span class="badge-new">New</span>
        @endif
    </div>
    
    <div class="product-info">
        <h3>{{ $product->name }}</h3>
        <p class="price">${{ $product->price }}</p>
    </div>
</div>
```

### Social Media Meta Tags

```blade
{{-- In your layout head section --}}
@php
    $ogImage = Mediaclass::forModel($page, 'social')->first();
@endphp

@if($ogImage)
    <meta property="og:image" content="<x-mediaclass::printer :model="$ogImage" type="url" cropKey="social" />">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
@endif
```

### Background Image Section

```blade
@php
    $backgroundImage = Mediaclass::forModel($section, 'background')->first();
@endphp

<section 
    class="hero-section"
    @if($backgroundImage)
        style="background-image: url('<x-mediaclass::printer :model="$backgroundImage" type="url" size="xl" />');"
    @endif
>
    <div class="content">
        {{ $section->content }}
    </div>
</section>
```

## Component Registration

The component is automatically registered as `mediaclass::printer` when the service provider is loaded. You can also create an alias in your app:

```php
// In AppServiceProvider or a custom provider
use Illuminate\Support\Facades\Blade;

public function boot()
{
    Blade::component('image', \MetaFramework\Mediaclass\Components\Printer::class);
}
```

Then use it as:

```blade
<x-image :model="$parser" />
```

## Best Practices

1. **Always Check for Media**: The component handles null models gracefully, but checking existence can improve performance:
   ```blade
   @if($media = Mediaclass::forModel($product)->first())
       <x-mediaclass::printer :model="$media" />
   @endif
   ```

2. **Use Appropriate Sizes**: Choose sizes based on display context to optimize loading:
   ```blade
   {{-- Thumbnails --}}
   <x-mediaclass::printer :model="$media" size="sm" />
   
   {{-- Content images --}}
   <x-mediaclass::printer :model="$media" size="md" />
   
   {{-- Hero images --}}
   <x-mediaclass::printer :model="$media" size="xl" loading="eager" />
   ```

3. **Leverage Crop Detection**: Let the component automatically use cropped versions:
   ```blade
   {{-- Automatically uses cropped version if available --}}
   <x-mediaclass::printer :model="$media" />
   ```

4. **Use Picture Elements for Art Direction**: When you have different crops for different screen sizes:
   ```blade
   <x-mediaclass::printer 
       :model="$media" 
       type="picture"
       :breakpoints="[
           'max-width: 640px' => 'crop_mobile',
           'min-width: 641px' => 'cropped'
       ]"
   />
   ```

5. **Optimize Loading**: Use `loading="eager"` only for above-the-fold images:
   ```blade
   {{-- Hero image --}}
   <x-mediaclass::printer :model="$media" loading="eager" />
   
   {{-- Images further down --}}
   <x-mediaclass::printer :model="$media" /> {{-- lazy by default --}}
   ```

This component provides a powerful and flexible way to display media in your Blade views while automatically handling cropped versions, responsive images, and fallbacks.
