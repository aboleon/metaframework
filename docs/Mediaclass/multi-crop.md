# Multiple Crop Implementation Summary

## Overview
This implementation adds support for multiple named crop configurations to your media manager. Instead of a single crop dimension, you can now define multiple crops with specific names (e.g., banner, thumbnail, mobile) and store each cropped version separately.

## Key Changes

### 1. Database Schema
- Add a `cropped_images` JSON column to the `media` table to store metadata about all cropped versions
- Run the migration to add this column

### 2. Cropable Class Updates
- Support for multiple crop configurations stored as an associative array
- New methods: `links()`, `getCropableSettings()`, `setCurrentCropKey()`, `isCroppedForKey()`
- Backwards compatible with the old single-crop format

### 3. Cropper Controller
- Updated to handle `crop_key` parameter to identify which crop is being processed
- Stores cropped images with their key name (e.g., `banner_abc123.jpg`)
- Updates the media's `cropped_images` metadata

### 4. UI Changes
- Crop actions are now displayed in a horizontal bar below the image info
- Each crop configuration gets its own button with the crop name and dimensions
- Visual indication when a crop has been completed (green background, check icon)
- Modal shows which crop is being edited

### 5. Blade Template Updates
- `stored.blade.php`: Shows the new crop actions bar
- `cropper.blade.php`: Displays crop name and target dimensions in the modal
- `uploadable.blade.php`: Accepts the new cropable format

## Usage

### Define Multiple Crops
```php
// In your model
public function mediaclass(): array
{
    return [
        'images' => [
            'cropable' => [
                'banner' => [1920, 600],
                'thumbnail' => [400, 400],
                'mobile' => [768, 1024]
            ]
        ]
    ];
}

// Or in your component
<x-mediaclass::uploadable
    :model="$model"
    :cropable="json_encode([
        'banner' => [1920, 600],
        'thumbnail' => [400, 400]
    ])"
/>
```

### Access Cropped Images
```php
// Get specific crop URL
$bannerUrl = $media->url('xl', 'banner');

// Check if crop exists
if ($media->isCropped('thumbnail')) {
    // Use thumbnail
}

// Get all cropped versions
$crops = $media->getCroppedImages();
```

## File Storage Structure
```
/media/ModelName/
├── 1920_abc123.jpg      # Original resized versions
├── 1024_abc123.jpg
├── 400_abc123.jpg
├── banner_abc123.jpg    # Cropped versions
├── thumbnail_abc123.jpg
└── mobile_abc123.jpg
```

## Backwards Compatibility
The implementation maintains backwards compatibility:
- Old format: `cropable="1024,768"` still works
- Converted internally to: `['default' => [1024, 768]]`
- Existing code using single crops continues to function

## Next Steps
1. Run the migration to add the `cropped_images` column
2. Add the new methods to your Media model
3. Update your models/components to use the new format
4. Include the new CSS styles in your project
5. Test the implementation with both single and multiple crops