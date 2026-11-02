<?php

namespace App\Filament\Concerns;

use App\Models\HomePage;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;

/**
 * Padanan EditsAboutSection untuk Beranda: form "judul seksi" (teks dari
 * home_pages) di atas tabel daftar sebuah resource — mis. kicker & judul
 * Produk & Solusi di atas tabel kartu produk.
 */
trait EditsHomeSection
{
    /** @var array<string, mixed> */
    public ?array $sectionData = [];

    /**
     * @return array<Component>
     */
    abstract protected function homeSectionFields(): array;

    protected function homeSectionHeading(): string
    {
        return 'Judul seksi';
    }

    protected function homeSectionDescription(): ?string
    {
        return 'Teks seksi ini di halaman /. Daftar item ada di tabel di bawah.';
    }

    public function mountEditsHomeSection(): void
    {
        $this->getSchema('sectionForm')->fill(HomePage::singleton()->attributesToArray());
    }

    public function sectionForm(Schema $schema): Schema
    {
        return $schema
            ->model(HomePage::singleton())
            ->statePath('sectionData')
            ->components([
                Section::make($this->homeSectionHeading())
                    ->description($this->homeSectionDescription())
                    ->collapsible()
                    ->schema([
                        ...$this->homeSectionFields(),
                        Actions::make([
                            Action::make('saveSection')
                                ->label('Simpan judul seksi')
                                ->submit('saveSection'),
                        ]),
                    ]),
            ]);
    }

    public function saveSection(): void
    {
        HomePage::singleton()->updateContent($this->getSchema('sectionForm')->getState());

        Notification::make()
            ->success()
            ->title($this->homeSectionHeading().' tersimpan.')
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('sectionForm')])
                ->id('section-form')
                ->livewireSubmitHandler('saveSection'),
            $this->getTabsContentComponent(),
            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
            EmbeddedTable::make(),
            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
        ]);
    }
}
