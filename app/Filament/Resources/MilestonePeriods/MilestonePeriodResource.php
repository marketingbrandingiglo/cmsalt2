<?php

namespace App\Filament\Resources\MilestonePeriods;

use App\Filament\Resources\MilestonePeriods\Pages\CreateMilestonePeriod;
use App\Filament\Resources\MilestonePeriods\Pages\EditMilestonePeriod;
use App\Filament\Resources\MilestonePeriods\Pages\ListMilestonePeriods;
use App\Filament\Resources\MilestonePeriods\RelationManagers\LogosRelationManager;
use App\Models\MilestonePeriod;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
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

class MilestonePeriodResource extends Resource
{
    protected static ?string $model = MilestonePeriod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static string|UnitEnum|null $navigationGroup = 'Tentang Kami';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Milestone';

    protected static ?string $modelLabel = 'periode milestone';

    protected static ?string $pluralModelLabel = 'Milestone';

    protected static ?string $slug = 'about/milestones';

    protected static ?string $recordTitleAttribute = 'period';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('period')
                    ->label('Periode')
                    ->placeholder('2021 - Present')
                    ->required()
                    ->maxLength(50),
                Toggle::make('is_active')
                    ->label('Tampilkan')
                    ->default(true)
                    ->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Urutan teratas = periode terbaru. Frontend memutarnya dari yang terlama.')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('period')
                    ->label('Periode')
                    ->weight('bold')
                    ->searchable(),
                ImageColumn::make('logos.logo')
                    ->label('Logo')
                    ->disk(config('cms.media_disk'))
                    ->imageHeight(32)
                    ->stacked()
                    ->limit(6)
                    ->limitedRemainingText(),
                TextColumn::make('logos_count')
                    ->label('Jumlah logo')
                    ->counts('logos'),
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
            LogosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMilestonePeriods::route('/'),
            'create' => CreateMilestonePeriod::route('/create'),
            'edit' => EditMilestonePeriod::route('/{record}/edit'),
        ];
    }
}
