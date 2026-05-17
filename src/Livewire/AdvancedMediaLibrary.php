<?php

namespace VlastimilVasek\LivewireAdvancedMediaLibrary\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use VlastimilVasek\LivewireAdvancedMediaLibrary\Support\OptimizesUploadedImage;

class AdvancedMediaLibrary extends Component
{
    use WithFileUploads;

    protected $listeners = [
        'advanced-media-library-save' => 'save',
    ];

    public HasMedia $model;

    public string $collection = 'default';

    public string $disk;

    public string $previewConversion = 'preview';

    public string $customPropertyName;

    public int $maxItems;

    public int $optimizedMaxWidth;

    public int $optimizedQuality;

    public string $optimizedFormat;

    public array $uploadRules;

    public bool $showSaveButton = true;

    public array $items = [];

    public array $captions = [];

    public array $uploads = [];

    public array $pendingCaptions = [];

    public array $removedMediaIds = [];

    public function mount(
        HasMedia $model,
        string $collection = 'default',
        ?string $disk = null,
        string $previewConversion = 'preview',
        ?string $customPropertyName = null,
        ?int $maxItems = null,
        ?int $optimizedMaxWidth = null,
        ?int $optimizedQuality = null,
        ?string $optimizedFormat = null,
        ?array $uploadRules = null,
        bool $showSaveButton = true,
    ): void {
        $this->model = $model;
        $this->collection = $collection;
        $this->disk = $disk ?? config('livewire-advanced-media-library.disk');
        $this->previewConversion = $previewConversion;
        $this->customPropertyName = $customPropertyName ?? config('livewire-advanced-media-library.custom_property_name');
        $this->maxItems = $maxItems ?? config('livewire-advanced-media-library.max_items');
        $this->optimizedMaxWidth = $optimizedMaxWidth ?? config('livewire-advanced-media-library.optimized_original.max_width');
        $this->optimizedQuality = $optimizedQuality ?? config('livewire-advanced-media-library.optimized_original.quality');
        $this->optimizedFormat = $optimizedFormat ?? config('livewire-advanced-media-library.optimized_original.format');
        $this->uploadRules = $uploadRules ?? config('livewire-advanced-media-library.upload_rules');
        $this->showSaveButton = $showSaveButton;

        $this->loadItems();
    }

    public function save(OptimizesUploadedImage $optimizer): void
    {
        $this->validate($this->rules());

        foreach ($this->removedMediaIds as $mediaId) {
            $this->model->media()->whereKey($mediaId)->first()?->delete();
        }

        foreach ($this->items as $position => $item) {
            $media = $this->model->media()->whereKey($item['id'])->first();

            if (! $media instanceof Media) {
                continue;
            }

            $media->setCustomProperty($this->customPropertyName, $this->captions[$item['id']] ?? null);
            $media->order_column = $position + 1;
            $media->save();
        }

        foreach ($this->uploads as $index => $upload) {
            if (! $upload instanceof TemporaryUploadedFile) {
                continue;
            }

            $optimizedPath = $optimizer->handle(
                $upload,
                $this->optimizedMaxWidth,
                $this->optimizedQuality,
                $this->optimizedFormat
            );

            $media = $this->model
                ->addMedia($optimizedPath)
                ->usingName(pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME))
                ->usingFileName($this->fileName($upload))
                ->withCustomProperties([
                    $this->customPropertyName => $this->pendingCaptions[$index] ?? null,
                ])
                ->toMediaCollection($this->collection, $this->disk);

            $media->order_column = count($this->items) + $index + 1;
            $media->save();

            File::delete($optimizedPath);
        }

        $this->uploads = [];
        $this->pendingCaptions = [];
        $this->removedMediaIds = [];
        $this->model->load('media');
        $this->loadItems();

        $this->dispatch('advanced-media-library-saved');
    }

    public function removeExisting(int $mediaId): void
    {
        $this->removedMediaIds[] = $mediaId;
        $this->items = array_values(array_filter(
            $this->items,
            fn (array $item): bool => (int) $item['id'] !== $mediaId
        ));

        unset($this->captions[$mediaId]);
    }

    public function removeUpload(int $index): void
    {
        unset($this->uploads[$index], $this->pendingCaptions[$index]);

        $this->uploads = array_values($this->uploads);
        $this->pendingCaptions = array_values($this->pendingCaptions);
    }

    public function move(int $from, int $to): void
    {
        if (! isset($this->items[$from], $this->items[$to])) {
            return;
        }

        $item = $this->items[$from];
        array_splice($this->items, $from, 1);
        array_splice($this->items, $to, 0, [$item]);
    }

    public function reorder(array $orderedIds): void
    {
        $itemsById = collect($this->items)->keyBy('id');

        $this->items = collect($orderedIds)
            ->map(fn (mixed $id): ?array => $itemsById->get((int) $id))
            ->filter()
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire-advanced-media-library::livewire.advanced-media-library');
    }

    protected function rules(): array
    {
        return [
            'uploads' => ['array', 'max:'.max(0, $this->maxItems - count($this->items))],
            'uploads.*' => $this->uploadRules,
            'captions.*' => ['nullable', 'string', 'max:1000'],
            'pendingCaptions.*' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function loadItems(): void
    {
        $mediaItems = $this->model
            ->getMedia($this->collection)
            ->sortBy('order_column')
            ->values();

        $this->items = $mediaItems
            ->map(fn (Media $media): array => [
                'id' => $media->id,
                'uuid' => $media->uuid,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'preview_url' => $media->hasGeneratedConversion($this->previewConversion)
                    ? $media->getUrl($this->previewConversion)
                    : $media->getUrl(),
            ])
            ->values()
            ->all();

        $this->captions = $mediaItems
            ->mapWithKeys(fn (Media $media): array => [
                $media->id => $media->getCustomProperty($this->customPropertyName),
            ])
            ->all();
    }

    protected function fileName(TemporaryUploadedFile $upload): string
    {
        $name = pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($name) ?: 'image';

        return $slug.'-'.Str::lower(Str::random(8)).'.'.$this->optimizedFormat;
    }
}
