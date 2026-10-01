<?php

namespace App\Filament\Pages;

use App\Filament\Support\Bilingual;
use App\Filament\Support\MediaUpload;
use App\Models\AboutPage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Teks & media utama halaman Tentang Kami (singleton).
 * Daftar berulang (statistik, nilai i5, maskot, milestone, mitra, klien)
 * dikelola lewat resource masing-masing di grup navigasi yang sama.
 */
class ManageAboutPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Tentang Kami';

    protected static ?string $navigationLabel = 'Konten Halaman';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'about/content';

    protected static ?string $title = 'Konten Halaman Tentang Kami';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getRecord()->attributesToArray());
    }

    public function getRecord(): AboutPage
    {
        return AboutPage::singleton();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model($this->getRecord())
            ->statePath('data')
            ->components([
                Tabs::make('about')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Hero')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                MediaUpload::image('hero_image', 'hero')
                                    ->label('Gambar banner hero')
                                    ->helperText('Rekomendasi 1920×607 px. Dipakai PageHeroBanner di bagian paling atas.'),
                                Bilingual::input('hero.kicker', 'Kicker', 'content'),
                                Bilingual::input('hero.title', 'Judul', 'content', required: true),
                                Bilingual::textarea('hero.subtitle', 'Subjudul', 'content'),
                            ]),

                        Tab::make('Siapa Kami')
                            ->icon(Heroicon::OutlinedBuildingOffice)
                            ->schema([
                                Bilingual::input('company.name', 'Nama perusahaan', 'content', required: true),
                                Bilingual::input('whoWeAreKicker', 'Label "Siapa Kami"', 'content'),
                                Bilingual::textarea('company.p1', 'Paragraf 1', 'content'),
                                Bilingual::textarea('company.p2', 'Paragraf 2', 'content'),
                            ]),

                        Tab::make('Visi & Misi')
                            ->icon(Heroicon::OutlinedFlag)
                            ->schema([
                                Bilingual::input('visionTitle', 'Judul visi', 'content'),
                                Bilingual::textarea('vision', 'Visi', 'content', rows: 3),
                                Bilingual::input('missionTitle', 'Judul misi', 'content'),
                                Bilingual::textarea('mission', 'Misi', 'content', rows: 3),
                            ]),

                        Tab::make('Nilai i5')
                            ->icon(Heroicon::OutlinedSparkles)
                            ->schema([
                                MediaUpload::image('i5_logo', 'i5')
                                    ->label('Logo i5')
                                    ->helperText('PNG transparan, rasio 1:1. Logo yang "jatuh" dan berputar di scene Visi & Misi.'),
                                Bilingual::input('valuesKicker', 'Subjudul nilai (muncul bersama ikon i5)', 'content'),
                                Bilingual::input('valuesTitle', 'Judul nilai', 'content'),
                                Bilingual::input('valuesIcon', 'Alt text logo i5', 'content'),
                            ]),

                        Tab::make('Maskot & Milestone')
                            ->icon(Heroicon::OutlinedTrophy)
                            ->schema([
                                Section::make('Maskot')
                                    ->description('Bio & gambar tiap maskot diatur di menu "Maskot".')
                                    ->schema([
                                        Bilingual::input('mascotTitle', 'Judul seksi maskot', 'content'),
                                    ]),
                                Section::make('Milestone')
                                    ->description('Periode & logo milestone diatur di menu "Milestone".')
                                    ->schema([
                                        Bilingual::input('milestone.title', 'Judul milestone', 'content'),
                                        Bilingual::textarea('milestone.narrative', 'Narasi', 'content'),
                                    ]),
                            ]),

                        Tab::make('Video')
                            ->icon(Heroicon::OutlinedFilm)
                            ->schema([
                                MediaUpload::video('video_file', 'video')
                                    ->label('File video perusahaan (MP4/WebM, maks. 100 MB)'),
                                MediaUpload::image('video_poster', 'video')
                                    ->label('Poster / thumbnail video (opsional)'),
                                Bilingual::input('video.title', 'Judul', 'content'),
                                Bilingual::textarea('video.text', 'Deskripsi', 'content'),
                            ]),

                        Tab::make('Mitra & Klien')
                            ->icon(Heroicon::OutlinedUserGroup)
                            ->schema([
                                Bilingual::input('partner.title', 'Judul seksi mitra', 'content'),
                                Bilingual::input('client.title', 'Judul seksi klien', 'content'),
                            ]),
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
        $data = $this->form->getState();

        $this->getRecord()->update($data);

        Notification::make()
            ->success()
            ->title('Konten halaman Tentang Kami tersimpan.')
            ->send();
    }
}
