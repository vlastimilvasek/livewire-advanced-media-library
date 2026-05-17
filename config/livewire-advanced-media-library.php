<?php

return [
    'disk' => env('ADVANCED_MEDIA_LIBRARY_DISK', 'public'),

    'max_items' => 8,

    'upload_rules' => [
        'image',
        'mimes:jpg,jpeg,png,webp',
        'max:12288',
    ],

    'optimized_original' => [
        'max_width' => 1600,
        'quality' => 82,
        'format' => 'jpg',
    ],

    'custom_property_name' => 'popis',
];
