# Parser and Printer Documentation

## Overview

The Parser and Printer classes work together to provide a powerful system for handling media display in your Laravel application:

- **Parser**: Transforms Media models into convenient data objects with URLs for different sizes
- **Printer**: Provides a fluent interface for generating HTML elements (img tags, picture elements) with responsive image support

## Table of Contents

1. [Parser Class](#parser-class)
   - [Basic Usage](#parser-basic-usage)
   - [Properties](#parser-properties)
   - [Methods](#parser-methods)
   - [Working with Crops](#working-with-crops)
2. [Printer Class](#printer-class)
   - [Basic Usage](#printer-basic-usage)
   - [Generating Images](#generating-images)
   - [Responsive Images](#responsive-images)
   - [HTML Attributes](#html-attributes)
3. [Integration Examples](#integration-examples)
4. [Advanced Usage](#advanced-usage)

## Parser Class

The Parser class converts Media model instances into easy-to-use data objects with all necessary URLs and metadata.

### Parser Basic Usage

```php
use MetaFramework\Mediaclass\Parser;
use MetaFramework\Mediaclass\Models\Media;

// Create a parser instance
$media = Media::find(1);
$parser = new Parser($media);

// Access properties
echo $parser->url;         // Default URL (largest size)
echo $parser->urls['sm'];  // Small size URL
echo $parser->description; // Localized description
echo $parser->mime;        // MIME type
```

### Parser Properties

All properties are readonly for data integrity:

```php
// Basic properties
$parser->id;          // Media ID
$parser->group;       // Media group (e.g., 'gallery', 'thumbnails')
$parser->position;    // Position relative to content (left, right, up, down)
$parser->mime;        // MIME type (e.g., 'image/jpeg')
$parser->filename;    // Original filename
$parser->extension;   // File extension

// Localized description
$parser->description; // Description in current locale

// URLs for different sizes
$parser->urls;        // Array of all available URLs
$parser->url;         // Default URL (typically largest size)

// Type checks
$parser->isImage;     // true if media is an image
$parser->isFile;      // true if media is not an image
```

### Parser Methods

```php
// Get URL for specific size
$mediumUrl = $parser->getUrl('md');
$largeUrl = $parser->getUrl('lg');

// Check if size exists
if ($parser->hasSize('xl')) {
    echo $parser->getUrl('xl');
}

// Get all available sizes
$sizes = $parser->getAvailableSizes();
// Returns: ['sm', 'md', 'lg', 'xl', 'cropped']

// Check if media has been cropped
if ($parser->isCropped()) {
    echo "This image has been cropped";
}

// Check specific crop variation
if ($parser->isCropped('thumbnail')) {
    echo $parser->getUrl('crop_thumbnail');
}

// Get original Media model
$mediaModel = $parser->getMedia();

// Convert to array
$data = $parser->toArray();
```

### Working with Crops

The Parser automatically detects and includes cropped variations:

```php
// Standard crop (uses 'cropped' key)
if ($parser->isCropped()) {
    $croppedUrl = $parser->urls['cropped'];
}

// Custom crop variations (e.g., banner, thumbnail)
if ($parser->isCropped('banner')) {
    $bannerUrl = $parser->urls['cropped_banner'];
}

// List all crops
foreach ($parser->urls as $key => $url) {
    if (str_starts_with($key, 'cropped_')) {
        $cropName = substr($key, 8); // 8 is the length of 'cropped_'
        echo "Crop '{$cropName}': {$url}\n";
    }
}
```

## Printer Class

The Printer class provides a fluent interface for rendering media as HTML elements with support for responsive images and custom attributes.

### Printer Basic Usage

```php
use MetaFramework\Mediaclass\Printer;

// Create printer with parsed media
$printer = new Printer($parser);

// Generate simple img tag
echo $printer->img();
// Output: <img src="/path/to/image.jpg" alt="Description" loading="lazy" />

// Get URL only
$url = $printer->url();

// Specify size
echo $printer->img('lg');
echo $printer->setSize('xl')->img();
```

### Generating Images

#### Simple Image Tags

```php
// Basic image
echo $printer->img();

// With specific size
echo $printer->setSize('lg')->img();

// With custom attributes
echo $printer
    ->setClass('img-fluid rounded')
    ->setAlt('Product photo')
    ->img();

// Disable lazy loading
echo $printer
    ->setLoading('eager')
    ->img();
```

#### Picture Elements

Generate picture elements with different sources for different breakpoints:

```php
// Using default breakpoints
echo $printer->picture();
/* Output:
<picture>
    <source media="(max-width: 640px)" srcset="/path/to/sm.jpg">
    <source media="(max-width: 768px)" srcset="/path/to/md.jpg">
    <source media="(max-width: 1024px)" srcset="/path/to/lg.jpg">
    <source media="(min-width: 1025px)" srcset="/path/to/xl.jpg">
    <img src="/path/to/image.jpg" alt="Description" loading="lazy" />
</picture>
*/

// Custom breakpoints
echo $printer->picture([
    'max-width: 480px' => 'sm',
    'max-width: 1024px' => 'md',
    'min-width: 1025px' => 'xl',
]);
```

### Responsive Images

The Printer automatically generates srcset and sizes attributes for responsive images:

```php
// Enable responsive (default)
echo $printer->img();
/* Output:
<img 
    src="/path/to/xl.jpg" 
    srcset="/path/to/sm.jpg 400w, /path/to/md.jpg 700w, /path/to/lg.jpg 1400w, /path/to/xl.jpg 1920w"
    sizes="(max-width: 400px) 400px, (max-width: 700px) 700px, (max-width: 1400px) 1400px, 1920px"
    alt="Description" 
    loading="lazy" 
/>
*/

// Disable responsive
echo $printer
    ->disableResponsive()
    ->img();
```

### HTML Attributes

#### Setting Attributes

```php
// Single attribute
$printer->setAttributes('id', 'main-image');
$printer->setAttributes('data-zoom', 'true');

// Multiple attributes
$printer->setAttributes([
    'class' => 'img-fluid',
    'id' => 'product-image',
    'data-category' => 'electronics',
]);

// Chain methods
echo $printer
    ->setClass('gallery-image')
    ->setAlt('Product showcase')
    ->setAttributes('data-index', '1')
    ->img();
```

#### CSS Classes

```php
// Set class (replaces existing)
$printer->setClass('img-thumbnail');

// Add class (preserves existing)
$printer
    ->setClass('img-fluid')
    ->addClass('border')
    ->addClass('shadow-sm');
```

#### Common Attributes

```php
// Alt text
$printer->setAlt('Detailed product view');

// Loading strategy
$printer->setLoading('lazy');    // Default
$printer->setLoading('eager');   // Load immediately
$printer->setLoading('auto');    // Browser decides

// Custom attributes
$printer->setAttributes([
    'width' => '800',
    'height' => '600',
    'decoding' => 'async',
    'fetchpriority' => 'high',
]);
```

### Default Image Handling

```php
// Disable default image
$printer->noDefault();
// Returns empty string if no media

// Re-enable default
$printer->withDefault();

// Set custom default URL
$printer->setDefaultUrl('/assets/custom-placeholder.jpg');
```

## Integration Examples

### Example 1: Product Gallery with Responsive Images

```php
// In your Blade view
@foreach($product->media as $mediaModel)
    @php
        $parser = new \MetaFramework\Mediaclass\Parser($mediaModel);
        $printer = new \MetaFramework\Mediaclass\Printer($parser);
    @endphp
    
    <div class="gallery-item">
        {!! $printer
            ->setClass('img-fluid gallery-image')
            ->setAttributes('data-index', $loop->index)
            ->img() !!}
    </div>
@endforeach
```

### Example 2: Hero Banner with Picture Element

```php
@if($hero = Mediaclass::forModel($page, 'hero')->first())
    @php
        $printer = new Printer($hero);
    @endphp
    
    <div class="hero-banner">
        {!! $printer
            ->setClass('hero-image')
            ->setLoading('eager')
            ->picture([
                'max-width: 640px' => 'md',
                'max-width: 1024px' => 'lg',
                'min-width: 1025px' => 'xl',
            ]) !!}
    </div>
@endif
```

### Example 3: Thumbnail Grid with Fallbacks

```php
<div class="thumbnail-grid">
    @foreach($products as $product)
        @php
            $thumbnail = Mediaclass::forModel($product, 'thumbnail')->first();
            $printer = $thumbnail 
                ? new Printer($thumbnail) 
                : null;
        @endphp
        
        <div class="thumbnail-item">
            @if($printer && $printer->hasMedia())
                {!! $printer
                    ->setSize('sm')
                    ->setClass('thumbnail')
                    ->disableResponsive()
                    ->img() !!}
            @else
                <img src="/assets/no-image.png" alt="No image" class="thumbnail">
            @endif
            
            <h3>{{ $product->name }}</h3>
        </div>
    @endforeach
</div>
```

### Example 4: Blog Post with Multiple Image Sizes

```php
// Controller
public function show(Post $post)
{
    $featuredImage = Mediaclass::forModel($post, 'featured')->first();
    
    return view('blog.show', [
        'post' => $post,
        'featuredParser' => $featuredImage,
    ]);
}

// View
@if($featuredParser)
    @php $printer = new Printer($featuredParser); @endphp
    
    {{-- Hero image - full size --}}
    <div class="post-hero">
        {!! $printer
            ->setSize('xl')
            ->setClass('hero-image')
            ->setLoading('eager')
            ->img() !!}
    </div>
    
    {{-- Social media preview - medium size --}}
    <meta property="og:image" content="{{ $printer->url('md') }}">
    
    {{-- Mobile thumbnail - small size --}}
    <div class="mobile-thumbnail d-block d-md-none">
        {!! $printer
            ->setSize('sm')
            ->setClass('img-fluid')
            ->img() !!}
    </div>
@endif
```

### Example 5: Working with Cropped Images

```php
@php
    $parser = new Parser($media);
    $printer = new Printer($parser);
@endphp

{{-- Display original image --}}
<div class="original">
    {!! $printer->img('xl') !!}
</div>

{{-- Display cropped versions if available --}}
@if($parser->isCropped())
    <div class="cropped-default">
        {!! $printer->setSize('cropped')->img() !!}
    </div>
@endif

@if($parser->isCropped('banner'))
    <div class="cropped-banner">
        <img src="{{ $parser->getUrl('cropped_banner') }}" alt="Banner crop">
    </div>
@endif
```

## Advanced Usage

### Custom Parser with Specific Sizes

```php
// Define custom sizes for this parser instance
$customSizes = [
    'thumb' => ['width' => 150, 'height' => 150],
    'preview' => ['width' => 600, 'height' => 400],
];

$parser = new Parser($media, $customSizes);
```

### Creating a Printer Factory Pattern (Example)

If you find yourself repeatedly using the same Printer configurations, you could create your own factory class:

```php
// Example: Create your own factory class in app/Services/MediaPrinterFactory.php
namespace App\Services;

use MetaFramework\Mediaclass\Parser;
use MetaFramework\Mediaclass\Printer;

class MediaPrinterFactory
{
    public static function create(Parser $parser, string $preset = 'default'): Printer
    {
        $printer = new Printer($parser);
        
        switch ($preset) {
            case 'thumbnail':
                return $printer
                    ->setSize('sm')
                    ->setClass('img-thumbnail')
                    ->disableResponsive();
                    
            case 'hero':
                return $printer
                    ->setSize('xl')
                    ->setClass('hero-image img-fluid')
                    ->setLoading('eager');
                    
            case 'gallery':
                return $printer
                    ->setSize('md')
                    ->setClass('gallery-item')
                    ->setAttributes(['data-fancybox' => 'gallery']);
                    
            default:
                return $printer->setClass('img-fluid');
        }
    }
}

// Usage in your application
use App\Services\MediaPrinterFactory;

$printer = MediaPrinterFactory::create($parser, 'hero');
echo $printer->img();
```

This is just an example of how you could extend the functionality - the factory class is not included in the package.

### Blade Component Integration

```php
// Create a Blade component that uses Parser and Printer
// app/View/Components/MediaImage.php
namespace App\View\Components;

use Illuminate\View\Component;
use MetaFramework\Mediaclass\Parser;
use MetaFramework\Mediaclass\Printer;

class MediaImage extends Component
{
    public Printer $printer;
    
    public function __construct(
        Parser $media,
        string $size = 'sm',
        string $class = '',
        bool $responsive = true,
        array $attributes = []
    ) {
        $this->printer = new Printer($media, $size);
        
        if ($class) {
            $this->printer->setClass($class);
        }
        
        if (!$responsive) {
            $this->printer->disableResponsive();
        }
        
        if ($attributes) {
            $this->printer->setAttributes($attributes);
        }
    }
    
    public function render()
    {
        return $this->printer->img();
    }
}
```

```blade
{{-- Usage in Blade --}}
<x-media-image 
    :media="$parser" 
    size="lg" 
    class="product-image" 
    :responsive="true"
    :attributes="['data-zoom' => 'true']"
/>
```

### JSON API Response

```php
// API Controller
public function show(Product $product)
{
    $media = Mediaclass::forModel($product)->map(function($parser) {
        return [
            'id' => $parser->id,
            'urls' => $parser->urls,
            'defaultUrl' => $parser->url,
            'description' => $parser->description,
            'mime' => $parser->mime,
            'isImage' => $parser->isImage,
            'position' => $parser->position,
            'sizes' => $parser->getAvailableSizes(),
            'crops' => array_filter($parser->urls, function($key) {
                return str_starts_with($key, 'crop_');
            }, ARRAY_FILTER_USE_KEY),
        ];
    });
    
    return response()->json([
        'product' => $product,
        'media' => $media,
    ]);
}
```

## Best Practices

1. **Always Check Media Existence**: Before using Parser or Printer, verify media exists
   ```php
   if ($parser = Mediaclass::forModel($model)->first()) {
       $printer = new Printer($parser);
       echo $printer->img();
   }
   ```

2. **Use Appropriate Sizes**: Select image sizes based on display context
   ```php
   // Thumbnails
   $printer->setSize('sm');
   
   // Content images
   $printer->setSize('md');
   
   // Hero/banner images
   $printer->setSize('xl');
   ```

3. **Optimize Loading**: Use eager loading for above-the-fold images
   ```php
   // Hero images
   $printer->setLoading('eager');
   
   // Images further down the page
   $printer->setLoading('lazy'); // Default
   ```

4. **Leverage Responsive Images**: Keep responsive enabled for better performance on different devices

5. **Cache Parser Results**: For frequently accessed media, consider caching parsed data
   ```php
   $parsed = Cache::remember("media.{$id}.parsed", 3600, function() use ($media) {
       return (new Parser($media))->toArray();
   });
   ```

This documentation provides comprehensive coverage of both Parser and Printer classes with practical examples and best practices for implementation.
