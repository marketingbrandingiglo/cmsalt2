<?php

namespace App\Filament\Concerns;

use App\Models\AboutPage;
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
 * Menampilkan form "judul seksi" (teks dari about_pages) di atas tabel
 * daftar sebuah resource — mis. judul & subjudul Nilai i5 di atas tabel
 * 5 nilai. Disimpan terpisah dari data tabel lewat tombol sendiri.
 */
trait EditsAboutSection
{
    /** @var array<string, mixed> */
    public ?array $sectionData = [];

    /**
     * @return array<Component>
     */
    abstract protected function aboutSectionFields(): array;

    protected function aboutSectionHeading(): string
    {
        return 'Judul seksi';
    }

    protected function aboutSectionDescription(): ?string
    {
        return 'Teks seksi ini di halaman /about. Daftar item ada di tabel di bawah.';
    }

    public function mountEditsAboutSection(): void
    {
        $this->getSchema('sectionForm')->fill(AboutPage::singleton()->attributesToArray());
    }

    public function sectionForm(Schema $schema): Schema
    {
        return $schema
            ->model(AboutPage::singleton())
            ->statePath('sectionData')
            ->components([
                Section::make($this->aboutSectionHeading())
                    ->description($this->aboutSectionDescription())
                    ->collapsible()
                    ->schema([
                        ...$this->aboutSectionFields(),
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
        AboutPage::singleton()->updateContent($this->getSchema('sectionForm')->getState());

        Notification::make()
            ->success()
            ->title($this->aboutSectionHeading().' tersimpan.')
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
