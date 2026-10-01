<?php

namespace App\Filament\Resources\CompanyValues;

use App\Filament\Resources\CompanyValues\Pages\ManageCompanyValues;
use App\Filament\Support\Bilingual;
use App\Filament\Support\MediaUpload;
use App\Models\CompanyValue;
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

class CompanyValueResource extends Resource
{
    protected static ?string $model = CompanyValue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Tentang Kami';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Nilai i5';

    protected static ?string $modelLabel = 'nilai';

    protected static ?string $pluralModelLabel = 'Nilai i5';

    protected static ?string $slug = 'about/values';

    protected static ?string $recordTitleAttribute = 'title';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Nama nilai')
                    ->required()
                    ->maxLength(50)
                    ->helperText('Sama untuk ID & EN. Posisi ikon di lingkaran i5 dipetakan dari nama ini (Integrity, Innovative, Integrated, Impressive, Involved).'),
                MediaUpload::image('icon', 'values')
                    ->label('Ikon (PNG transparan)'),
                Bilingual::textarea('description', 'Deskripsi', required: true, rows: 2),
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
                ImageColumn::make('icon')
                    ->label('Ikon')
                    ->disk(config('cms.media_disk'))
                    ->imageHeight(40),
                TextColumn::make('title')
                    ->label('Nilai')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('description.id')
                    ->label('Deskripsi (ID)')
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
            'index' => ManageCompanyValues::route('/'),
        ];
    }
}
