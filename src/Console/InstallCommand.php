<?php

namespace VlastimilVasek\LivewireAdvancedMediaLibrary\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'livewire-advanced-media-library:install
        {--force : Overwrite existing published files}';

    protected $description = 'Publish Livewire Advanced Media Library config, views, and translations.';

    public function handle(): int
    {
        foreach ($this->publishTags() as $tag) {
            $this->call('vendor:publish', [
                '--tag' => $tag,
                '--force' => $this->option('force'),
            ]);
        }

        $this->components->info('Livewire Advanced Media Library resources published.');

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function publishTags(): array
    {
        return [
            'livewire-advanced-media-library-config',
            'livewire-advanced-media-library-views',
            'livewire-advanced-media-library-translations',
        ];
    }
}
