<div class="space-y-4" x-data="{ dragIndex: null }">
    @once
        <style>
            .aml-media-tile .aml-media-actions {
                background: rgb(0 0 0 / 0%);
                opacity: 0;
                transition: background-color 150ms ease, opacity 150ms ease;
                z-index: 20;
            }

            .aml-media-tile:hover .aml-media-actions,
            .aml-media-tile:focus-within .aml-media-actions {
                background: rgb(0 0 0 / 55%);
                opacity: 1;
            }

            .aml-media-action {
                position: relative;
                z-index: 30;
            }

            .aml-media-grid {
                display: grid;
                gap: 0.75rem;
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }

            @media (min-width: 640px) {
                .aml-media-grid {
                    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
                }
            }

            @media (min-width: 1024px) {
                .aml-media-grid {
                    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                }
            }
        </style>
    @endonce

    <div class="aml-media-grid">
        @foreach ($items as $index => $item)
            <article
                wire:key="advanced-media-library-item-{{ $item['id'] }}"
                draggable="true"
                x-on:dragstart="$data.dragIndex = {{ $index }}"
                x-on:dragover.prevent
                x-on:drop="$wire.move($data.dragIndex, {{ $index }}); $data.dragIndex = null"
                class="aml-media-tile overflow-hidden rounded-md border border-gray-300 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900"
            >
                <div class="relative aspect-square bg-gray-100 dark:bg-gray-950">
                    <img src="{{ $item['preview_url'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">

                    <div class="aml-media-actions absolute inset-0 flex items-start justify-end p-2">
                        <button type="button" wire:click="removeExisting({{ $item['id'] }})" class="aml-media-action inline-flex h-9 w-9 items-center justify-center rounded-md border border-white/40 bg-white text-red-700 shadow-sm transition hover:bg-red-50" title="{{ __('livewire-advanced-media-library::messages.remove_photo') }}" aria-label="{{ __('livewire-advanced-media-library::messages.remove_photo') }}">
                            <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h4m-7 4h10m-1 0-.7 10.2A2 2 0 0 1 13.3 20h-2.6a2 2 0 0 1-2-1.8L8 8m2.5 3v5m3-5v5" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-2 p-3">
                    <div class="truncate text-xs font-medium text-gray-700 dark:text-gray-200">{{ $item['file_name'] }}</div>
                    <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-200" for="advanced-media-library-caption-{{ $item['id'] }}">
                        {{ __('livewire-advanced-media-library::messages.caption') }}
                    </label>
                    <textarea id="advanced-media-library-caption-{{ $item['id'] }}" rows="2" wire:model="captions.{{ $item['id'] }}" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100"></textarea>
                </div>
            </article>
        @endforeach

        @foreach ($uploads as $index => $upload)
            <article wire:key="advanced-media-library-upload-{{ $index }}" class="aml-media-tile overflow-hidden rounded-md border border-dashed border-gray-300 bg-gray-50 shadow-sm dark:border-gray-700 dark:bg-gray-950">
                <div class="relative aspect-square bg-gray-100 dark:bg-gray-900">
                    @if ($upload)
                        <img src="{{ $upload->temporaryUrl() }}" alt="{{ $upload->getClientOriginalName() }}" class="h-full w-full object-cover">
                    @endif

                    <div class="aml-media-actions absolute inset-0 flex items-start justify-end p-2">
                        <button type="button" wire:click="removeUpload({{ $index }})" class="aml-media-action inline-flex h-9 w-9 items-center justify-center rounded-md border border-white/40 bg-white text-red-700 shadow-sm transition hover:bg-red-50" title="{{ __('livewire-advanced-media-library::messages.remove_photo') }}" aria-label="{{ __('livewire-advanced-media-library::messages.remove_photo') }}">
                            <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h4m-7 4h10m-1 0-.7 10.2A2 2 0 0 1 13.3 20h-2.6a2 2 0 0 1-2-1.8L8 8m2.5 3v5m3-5v5" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="space-y-2 p-3">
                    <div class="truncate text-xs font-medium text-gray-700 dark:text-gray-200">{{ $upload?->getClientOriginalName() }}</div>
                    <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-200" for="advanced-media-library-upload-caption-{{ $index }}">
                        {{ __('livewire-advanced-media-library::messages.caption') }}
                    </label>
                    <textarea id="advanced-media-library-upload-caption-{{ $index }}" rows="2" wire:model="pendingCaptions.{{ $index }}" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100"></textarea>
                </div>
            </article>
        @endforeach
    </div>

    <div class="rounded-md border border-dashed border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-950">
        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100" for="advanced-media-library-uploads">
            {{ __('livewire-advanced-media-library::messages.add_photos') }}
        </label>
        <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
            <label for="advanced-media-library-uploads" class="inline-flex cursor-pointer items-center justify-center rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800">
                {{ __('livewire-advanced-media-library::messages.choose_photos') }}
            </label>
            <span class="text-sm text-gray-700 dark:text-gray-200">
                @if (count($uploads) > 0)
                    {{ trans_choice('livewire-advanced-media-library::messages.selected_files', count($uploads), ['count' => count($uploads)]) }}
                @endif
            </span>
        </div>
        <input
            id="advanced-media-library-uploads"
            type="file"
            multiple
            accept="image/jpeg,image/png,image/webp"
            wire:model="uploads"
            class="hidden"
            tabindex="-1"
            aria-hidden="true"
        >

        @error('uploads')
            <p class="mt-2 text-sm text-red-700 dark:text-red-300">{{ $message }}</p>
        @enderror
        @error('uploads.*')
            <p class="mt-2 text-sm text-red-700 dark:text-red-300">{{ $message }}</p>
        @enderror
    </div>

    @if ($showSaveButton)
        <div class="flex justify-center">
            <button type="button" wire:click="save" wire:loading.attr="disabled" class="inline-flex items-center rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold uppercase text-white shadow-sm transition hover:bg-blue-800 disabled:opacity-50">
                {{ __('livewire-advanced-media-library::messages.save_photos') }}
            </button>
        </div>
    @endif
</div>
