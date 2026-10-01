<?php

namespace App\Filament\Resources\Partners;

use App\Filament\Resources\Partners\Pages\ManagePartners;
use App\Filament\Support\MediaUpload;
use App\Models\Partner;
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

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = 'Tentang Kami';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Mitra';

    protected static ?string $modelLabel = 'mitra';

    protected static ?string $pluralModelLabel = 'Mitra Kami';

    protected static ?string $slug = 'about/partners';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama mitra (alt text)')
                    ->required()
                    ->maxLength(255),
                TextInput::make('url')
                    ->label('Website (opsional)')
                    ->url()
                    ->maxLength(255),
                MediaUpload::image('logo', 'partners')
                    ->label('Logo')
                    ->required()
                    ->columnSpanFull(),
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
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk(config('cms.media_disk'))
                    ->imageHeight(40),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('url')
                    ->label('Website')
                    ->placeholder('—')
                    ->url(fn (Partner $record): ?string => $record->url, shouldOpenInNewTab: true),
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
            'index' => ManagePartners::route('/'),
        ];
    }
}
