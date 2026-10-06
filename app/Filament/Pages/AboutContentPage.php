<?php

namespace App\Filament\Pages;

use App\Models\AboutPage;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Dasar halaman "Konten Halaman" (Hero), sub-menu Siapa Kami &
 * Visi & Misi, serta menu Video. Tiap halaman hanya menyimpan
 * field miliknya — bagian lain di about_pages.content tidak tersentuh.
 */
abstract class AboutContentPage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Tentang Kami';

    /** @var array<string, mixed> */
    public ?array $data = [];

    /**
     * @return array<Component>
     */
    abstract protected function fields(): array;

    protected function successMessage(): string
    {
        return static::getNavigationLabel().' tersimpan.';
    }

    public function mount(): void
    {
        $this->form->fill(AboutPage::singleton()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model(AboutPage::singleton())
            ->statePath('data')
            ->components($this->fields());
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Simpan perubahan')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ])->sticky(),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Lihat halaman')
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->url(rtrim(config('cms.frontend_url') ?: 'https://iglowebsitealt2.vercel.app', '/').'/about')
                ->openUrlInNewTab(),
            Action::make('api')
                ->label('JSON API')
                ->icon(Heroicon::OutlinedCodeBracket)
                ->color('gray')
                ->url(route('api.about'))
                ->openUrlInNewTab(),
        ];
    }

    public function save(): void
    {
        AboutPage::singleton()->updateContent($this->form->getState());

        Notification::make()
            ->success()
            ->title($this->successMessage())
            ->send();
    }
}
