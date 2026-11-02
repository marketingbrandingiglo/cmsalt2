<?php

namespace App\Filament\Pages;

use App\Filament\Support\Bilingual;
use App\Models\HomePage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Menu "Intro" Beranda — teks di atas animasi rumah igloo (IntroScene).
 * Hanya menyimpan field miliknya; bagian lain di home_pages.content
 * (judul seksi Produk & Berita) tidak tersentuh.
 */
class ManageHomeIntro extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Beranda';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Intro';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'home/content';

    protected static ?string $title = 'Intro Beranda';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(HomePage::singleton()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model(HomePage::singleton())
            ->statePath('data')
            ->components([
                Section::make('Intro "Tentang iglo"')
                    ->description('Teks di atas animasi rumah igloo, tepat setelah Hero Slider di halaman /.')
                    ->schema([
                        Bilingual::input('intro.kicker', 'Kicker', 'content'),
                        Bilingual::input('intro.title', 'Judul', 'content', required: true),
                        Bilingual::input('intro.cta', 'Label tombol (menuju /about)', 'content'),
                    ]),
            ]);
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
                ->url(rtrim(config('cms.frontend_url') ?: 'https://iglowebsitealt2.vercel.app', '/').'/')
                ->openUrlInNewTab(),
            Action::make('api')
                ->label('JSON API')
                ->icon(Heroicon::OutlinedCodeBracket)
                ->color('gray')
                ->url(route('api.home'))
                ->openUrlInNewTab(),
        ];
    }

    public function save(): void
    {
        HomePage::singleton()->updateContent($this->form->getState());

        Notification::make()
            ->success()
            ->title('Intro Beranda tersimpan.')
            ->send();
    }
}
