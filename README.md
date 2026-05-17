# Livewire Advanced Media Library

A reusable Livewire image gallery manager for Laravel applications using Spatie Media Library.

The component is inspired by `ebess/advanced-nova-media-library`, but targets Blade and Livewire views outside Nova.

## Current Scope

- image tiles with previews
- multiple uploads
- captions stored in media custom properties
- delete existing images
- reorder existing images with drag and drop
- store uploaded files as optimized image files instead of keeping large raw uploads
- use Spatie Media Library conversions for previews and public display

Crop support is intentionally not part of the first version.

## Installation

```bash
composer require vlastimilvasek/livewire-advanced-media-library
```

Publish the config when needed:

```bash
php artisan vendor:publish --tag=livewire-advanced-media-library-config
```

Publish the Blade views when you want to customize the field markup and styling:

```bash
php artisan vendor:publish --tag=livewire-advanced-media-library-views
```

Publish translations when you need to customize labels:

```bash
php artisan vendor:publish --tag=livewire-advanced-media-library-translations
```

Or publish all customizable resources at once:

```bash
php artisan livewire-advanced-media-library:install
```

Use `--force` to overwrite previously published files:

```bash
php artisan livewire-advanced-media-library:install --force
```

## Usage

The model must implement `Spatie\MediaLibrary\HasMedia`.

```blade
<livewire:advanced-media-library
    :model="$zapis"
    collection="zapisy"
    preview-conversion="preview"
    custom-property-name="popis"
    :max-items="8"
/>
```

Uploaded files are converted to an optimized main media file before being added to the media collection. That optimized file becomes the Media Library "original", so the large raw browser upload is not retained.

## Model Example

```php
use Spatie\MediaLibrary\MediaCollections\Models\Media;

public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('preview')
        ->width(300)
        ->height(300)
        ->nonQueued();

    $this->addMediaConversion('basic')
        ->width(1600)
        ->height(1600)
        ->nonQueued();
}
```

## Events

The component dispatches `advanced-media-library-saved` after saving.
It also listens for `advanced-media-library-save`, so a parent Livewire component can hide the internal save button and save media from its own workflow.

## Translations

The package ships with English (`en`) and Czech (`cs`) translations. Additional languages can be added by publishing translations and creating another locale directory, for example `lang/vendor/livewire-advanced-media-library/de/messages.php`.

## Customizing Views

Published views are copied to `resources/views/vendor/livewire-advanced-media-library`. Laravel will automatically use those files instead of the package defaults, so each project can adjust Tailwind classes, markup, icons, and layout without editing files in `vendor`.
