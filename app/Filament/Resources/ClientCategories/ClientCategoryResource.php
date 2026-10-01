<?php

namespace App\Filament\Resources\ClientCategories;

use App\Filament\Resources\ClientCategories\Pages\CreateClientCategory;
use App\Filament\Resources\ClientCategories\Pages\EditClientCategory;
use App\Filament\Resources\ClientCategories\Pages\ListClientCategories;
use App\Filament\Resources\ClientCategories\RelationManagers\ClientsRelationManager;
use App\Filament\Support\Bilingual;
use App\Models\ClientCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class ClientCategoryResource extends Resource
{
    protected static ?string $model = ClientCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Tentang Kami';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Klien';

    protected static ?string $modelLabel = 'kategori klien';

    protected static ?string $pluralModelLabel = 'Klien Kami';

    protected static ?string $slug = 'about/clients';

    protected static ?string $recordTitleAttribute = 'name.id';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Bilingual::input('name', 'Nama tab', required: true),
                Toggle::make('is_active')
                    ->label('Tampilkan')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Setiap kategori menjadi satu tab di seksi "Klien Kami".')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name.id')
                    ->label('Tab (ID)')
                    ->weight('bold'),
                TextColumn::make('name.en')
                    ->label('Tab (EN)'),
                ImageColumn::make('clients.logo')
                    ->label('Logo')
                    ->disk(config('cms.media_disk'))
                    ->imageHeight(32)
                    ->stacked()
                    ->limit(6)
                    ->limitedRemainingText(),
                TextColumn::make('clients_count')
                    ->label('Jumlah klien')
                    ->counts('clients'),
                ToggleColumn::make('is_active')
                    ->label('Tampil'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ClientsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientCategories::route('/'),
            'create' => CreateClientCategory::route('/create'),
            'edit' => EditClientCategory::route('/{record}/edit'),
        ];
    }
}
