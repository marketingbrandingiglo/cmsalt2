<?php

namespace App\Filament\Resources\HomeSlides;

use App\Filament\Resources\HomeSlides\Pages\ManageHomeSlides;
use App\Filament\Support\Bilingual;
use App\Filament\Support\MediaUpload;
use App\Models\HomeSlide;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class HomeSlideResource extends Resource
{
    protected static ?string $model = HomeSlide::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Beranda';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Hero Slider';

    protected static ?string $modelLabel = 'slide';

    protected static ?string $pluralModelLabel = 'Hero Slider';

    protected static ?string $slug = 'home/slides';

    protected static ?string $recordTitleAttribute = 'title.id';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                MediaUpload::image('image', 'slides', 'home')
                    ->label('Gambar banner')
                    ->helperText('Rekomendasi 1920×1080 px. Gambar penuh di belakang judul slide.')
                    ->required()
                    ->columnSpanFull(),
                Bilingual::textarea('title', 'Judul', required: true, rows: 2),
                Bilingual::textarea('description', 'Deskripsi', rows: 3),
                TextInput::make('cta_url')
                    ->label('Link tombol "Selengkapnya"')
                    ->placeholder('/news')
                    ->helperText('Path di website (mis. /news) atau URL lengkap. Kosong = /news.')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Tampilkan')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Slide berganti otomatis tiap 8 detik di bagian paling atas Beranda, sesuai urutan tabel.')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')
                    ->label('Banner')
                    ->disk(config('cms.media_disk'))
                    ->imageHeight(48),
                TextColumn::make('title.id')
                    ->label('Judul (ID)')
                    ->weight('bold')
                    ->wrap()
                    ->limit(90),
                TextColumn::make('cta_url')
                    ->label('Link')
                    ->placeholder('/news'),
                ToggleColumn::make('is_active')
                    ->label('Tampil'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageHomeSlides::route('/'),
        ];
    }
}
