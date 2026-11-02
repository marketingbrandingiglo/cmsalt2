<?php

namespace App\Filament\Resources\HomeProducts;

use App\Filament\Resources\HomeProducts\Pages\ManageHomeProducts;
use App\Filament\Support\Bilingual;
use App\Filament\Support\MediaUpload;
use App\Models\HomeProduct;
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

class HomeProductResource extends Resource
{
    protected static ?string $model = HomeProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Beranda';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Produk & Solusi';

    protected static ?string $modelLabel = 'produk';

    protected static ?string $pluralModelLabel = 'Produk & Solusi';

    protected static ?string $slug = 'home/products';

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama produk')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Sama untuk ID & EN.'),
                TextInput::make('slug')
                    ->label('Slug halaman produk')
                    ->required()
                    ->maxLength(255)
                    ->alphaDash()
                    ->helperText('Tombol "Pelajari Lebih Lanjut" menuju /products/{slug} di website — harus sama dengan halaman produk yang ada.'),
                MediaUpload::image('image', 'products', 'home')
                    ->label('Gambar kartu')
                    ->helperText('Rekomendasi rasio 4:3.')
                    ->columnSpanFull(),
                Bilingual::textarea('short', 'Deskripsi singkat (kartu mobile)', required: true, rows: 2),
                Bilingual::textarea('pitch', 'Deskripsi kartu desktop', rows: 3),
                Bilingual::textarea('long', 'Deskripsi panjang (cadangan bila deskripsi desktop kosong)', rows: 3),
                Toggle::make('is_active')
                    ->label('Tampilkan')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')
                    ->label('Gambar')
                    ->disk(config('cms.media_disk'))
                    ->imageHeight(40),
                TextColumn::make('name')
                    ->label('Produk')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('short.id')
                    ->label('Deskripsi singkat (ID)')
                    ->wrap()
                    ->limit(90),
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
            'index' => ManageHomeProducts::route('/'),
        ];
    }
}
