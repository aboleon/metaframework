# Mediaclass Service Documentation

## Overview

The `Mediaclass` service is a powerful media management system for Laravel applications that provides a fluent interface for retrieving, parsing, and manipulating media associated with your models. It works in conjunction with the Media model and Parser class to provide a comprehensive media handling solution.

## Table of Contents

1. [Basic Usage](#basic-usage)
2. [Service Methods](#service-methods)
3. [Working with Groups and Subgroups](#working-with-groups-and-subgroups)
4. [Parsing and Retrieving Media](#parsing-and-retrieving-media)
5. [Filtering and Collections](#filtering-and-collections)
6. [Utility Methods](#utility-methods)
7. [Complete Examples](#complete-examples)

## Basic Usage

### Using the Facade

```php
use MetaFramework\Mediaclass\Facades\MediaclassFacade as Mediaclass;

// Get all media for a model
$media = Mediaclass::forModel($product)->all();

// Get first media for a specific group
$thumbnail = Mediaclass::forModel($product, 'thumbnails')->first();

// Get media URL directly
$imageUrl = Mediaclass::forModel($product)->first()?->url;
```

### Using Dependency Injection

```php
use MetaFramework\Mediaclass\Mediaclass;

class ProductController
{
    public function show(Product $product, Mediaclass $mediaclass)
    {
        $media = $mediaclass->forModel($product)->all();
        
        return view('product.show', compact('product', 'media'));
    }
}
```

## Service Methods

### Core Methods

#### `on(MediaclassInterface $object): static`
Associates the service with a model that implements MediaclassInterface.

```php
$mediaclass->on($product);
```

#### `group(string $group): static`
Sets a group filter for media retrieval.

```php
$mediaclass->group('gallery');
```

#### `fetch(): static`
Fetches media from the database based on the current model and filters.

```php
$mediaclass->on($product)->group('gallery')->fetch();
```

#### `parse(): static`
Parses the fetched media collection into Parser instances that provide convenient access to URLs and metadata.

```php
$mediaclass->fetch()->parse();
```

### Convenience Methods

#### `forModel(?MediaclassInterface $object, ?string $group = null): static`
A convenience method that combines on(), group(), fetch(), and parse() in one call.

```php
// Get all media for a model
$media = Mediaclass::forModel($product)->all();

// Get media for a specific group
$galleryImages = Mediaclass::forModel($product, 'gallery')->all();
```

#### `single(): static`
Sets the service to return only the first media item when parsing.

```php
$firstImage = Mediaclass::forModel($product)->single()->parse()->first();
```

## Working with Groups and Subgroups

Media can be organized into groups and subgroups for better organization:

```php
// Retrieve media by group
$banners = Mediaclass::forModel($page, 'banners')->all();

// Filter by subgroup after fetching
$mobileBanners = Mediaclass::forModel($page, 'banners')
    ->parsedForSubGroup('mobile');

// Get raw collection filtered by group
$galleryCollection = Mediaclass::forModel($product)
    ->forGroup('gallery');
```

## Parsing and Retrieving Media

### Getting Parsed Media

```php
// Get all parsed media as array of Parser instances
$allMedia = Mediaclass::forModel($product)->all();

// Get first media item
$firstMedia = Mediaclass::forModel($product)->first();

// Access media properties through Parser
foreach ($allMedia as $media) {
    echo $media->url;        // Default URL
    echo $media->urls['sm']; // Small size URL
    echo $media->urls['xl']; // Extra large size URL
    echo $media->description; // Localized description
    echo $media->mime;       // MIME type
    echo $media->position;   // Position (left, right, up, down)
}
```

### Getting Raw Collections

```php
// Get Eloquent collection of Media models
$rawCollection = Mediaclass::forModel($product)->fetch()->get();

// Work with raw Media models
foreach ($rawCollection as $media) {
    echo $media->filename;
    echo $media->created_at;
}
```

## Filtering and Collections

### Filtering by Subgroup

```php
// Get filtered Eloquent collection
$mobileImages = Mediaclass::forModel($product)
    ->fetch()
    ->forSubGroup('mobile');

// Get parsed collection for subgroup
$parsedMobileImages = Mediaclass::forModel($product)
    ->parsedForSubGroup('mobile');
```

### Filtering by Group

```php
// Get all thumbnails
$thumbnails = Mediaclass::forModel($product)
    ->parsedForGroup('thumbnails');

// Chain with other operations
$firstThumbnail = Mediaclass::forModel($product)
    ->forGroup('thumbnails')
    ->first();
```

## Utility Methods

### Checking Media Existence

```php
$mediaclass = Mediaclass::forModel($product);

if ($mediaclass->exists()) {
    // Media exists
}

if ($mediaclass->isEmpty()) {
    // No media found
}

if ($mediaclass->isNotEmpty()) {
    // Media found
}
```

### Counting Media

```php
$count = Mediaclass::forModel($product)->count();
echo "Product has {$count} media items";
```

### Mapping and Filtering

```php
// Map over media items
$urls = Mediaclass::forModel($product)->map(function($parser) {
    return $parser->url;
});

// Filter media items
$images = Mediaclass::forModel($product)->filter(function($parser) {
    return str_contains($parser->mime, 'image');
});
```

## Complete Examples

### Example 1: Product Gallery

```php
// In your controller
public function show(Product $product)
{
    $data = [
        'product' => $product,
        'mainImage' => Mediaclass::forModel($product, 'main')->first(),
        'gallery' => Mediaclass::forModel($product, 'gallery')->all(),
        'thumbnails' => Mediaclass::forModel($product, 'thumbnails')->all(),
    ];
    
    return view('product.show', $data);
}

// In your Blade view
@if($mainImage)
    <img src="{{ $mainImage->urls['xl'] }}" alt="{{ $mainImage->description }}">
@endif

<div class="gallery">
    @foreach($gallery as $image)
        <div class="gallery-item" data-position="{{ $image->position }}">
            <img src="{{ $image->urls['md'] }}" alt="{{ $image->description }}">
        </div>
    @endforeach
</div>
```

### Example 2: Blog Post with Multiple Media Types

```php
// Controller
public function show(BlogPost $post)
{
    $mediaclass = app(Mediaclass::class);
    
    $data = [
        'post' => $post,
        'featuredImage' => $mediaclass->forModel($post, 'featured')->first(),
        'contentImages' => $mediaclass->forModel($post, 'content')->all(),
        'downloads' => $mediaclass->forModel($post, 'downloads')->filter(function($media) {
            return str_contains($media->mime, 'pdf');
        }),
    ];
    
    return view('blog.show', $data);
}
```

### Example 3: Media Management in Admin Panel

```php
// List all media with subgroups
public function mediaIndex(Product $product)
{
    $mediaByGroup = [];
    
    // Get all media
    $allMedia = Mediaclass::forModel($product)->get();
    
    // Group by media group
    $grouped = $allMedia->groupBy('group');
    
    foreach ($grouped as $group => $items) {
        $mediaByGroup[$group] = [
            'items' => $items,
            'parsed' => Mediaclass::forModel($product)->parsedForGroup($group),
        ];
    }
    
    return view('admin.product.media', [
        'product' => $product,
        'mediaByGroup' => $mediaByGroup,
    ]);
}
```

### Example 4: API Response with Media

```php
public function apiShow(Product $product)
{
    $media = Mediaclass::forModel($product)->map(function($parser) {
        return [
            'id' => $parser->id,
            'urls' => $parser->urls,
            'mime' => $parser->mime,
            'description' => $parser->description,
            'position' => $parser->position,
        ];
    });
    
    return response()->json([
        'product' => $product,
        'media' => $media,
    ]);
}
```

### Example 5: Checking Media Before Display

```php
// In Blade component or view
@php
    $productMedia = Mediaclass::forModel($product, 'gallery');
@endphp

@if($productMedia->isNotEmpty())
    <div class="product-gallery">
        @foreach($productMedia->all() as $media)
            <x-mediaclass::printer 
                :model="$media" 
                size="md" 
                class="gallery-image"
            />
        @endforeach
    </div>
@else
    <div class="no-images">
        <p>No images available for this product.</p>
    </div>
@endif
```

## Best Practices

1. **Use Facades for Simple Operations**: For straightforward media retrieval, use the facade for cleaner code.

2. **Cache Results**: For frequently accessed media, consider caching the parsed results:
   ```php
   $media = Cache::remember("product.{$product->id}.media", 3600, function() use ($product) {
       return Mediaclass::forModel($product)->all();
   });
   ```

3. **Type Check Results**: Always check if media exists before using:
   ```php
   $thumbnail = Mediaclass::forModel($product, 'thumbnail')->first();
   if ($thumbnail) {
       // Use thumbnail
   }
   ```

4. **Use Groups Effectively**: Organize your media into logical groups for easier management and retrieval.

5. **Reset When Reusing**: If reusing the same Mediaclass instance, call `reset()` between different models:
   ```php
   $mediaclass = new Mediaclass();
   $product1Media = $mediaclass->forModel($product1)->all();
   $product2Media = $mediaclass->reset()->forModel($product2)->all();
   ```

## Integration with Other Components

The Mediaclass service integrates seamlessly with:

- **Printer Component**: For displaying media in views
- **Uploadable Component**: For managing media uploads
- **Cropable Service**: For handling image cropping
- **Parser Class**: For accessing media properties and URLs

This documentation covers the primary usage patterns of the Mediaclass service. The refactored code provides better organization, clearer method names, and additional utility methods for common operations.
