<?php

namespace App\Filament\Resources\AboutHighlights;

use App\Filament\Pages\ManageAboutPage;
use App\Filament\Resources\AboutHighlights\Pages\ManageAboutHighlights;
use App\Filament\Support\Bilingual;
use App\Models\AboutHighlight;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class AboutHighlightResource extends Resource
{
    protected static ?string $model = AboutHighlight::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Tentang Kami';

    protected static ?string $navigationParentItem = ManageAboutPage::NAVIGATION_LABEL;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Statistik & Keunggulan';

    protected static ?string $modelLabel = 'poin perusahaan';

    protected static ?string $pluralModelLabel = 'Statistik & Keunggulan';

    protected static ?string $slug = 'about/highlights';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Jenis')
                    ->options(AboutHighlight::TYPES)
                    ->default('stat')
                    ->required()
                    ->live()
                    ->helperText('Statistik tampil sebagai "Lebih Dari {angka}", keunggulan hanya berupa teks.'),
                Select::make('icon')
                    ->label('Ikon')
                    ->options(AboutHighlight::ICONS)
                    ->required(),
                TextInput::make('value')
                    ->label('Angka')
                    ->placeholder('1100')
                    ->maxLength(30)
                    ->required(fn (Get $get): bool => $get('type') === 'stat')
                    ->visible(fn (Get $get): bool => $get('type') === 'stat'),
                Bilingual::textarea('text', 'Teks', required: true, rows: 2),
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
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => AboutHighlight::TYPES[$state] ?? $state),
                TextColumn::make('icon')
                    ->label('Ikon')
                    ->formatStateUsing(fn (string $state): string => AboutHighlight::ICONS[$state] ?? $state),
                TextColumn::make('value')
                    ->label('Angka')
                    ->placeholder('—'),
                TextColumn::make('text.id')
                    ->label('Teks (ID)')
                    ->wrap()
                    ->limit(80),
                ToggleColumn::make('is_active')
                    ->label('Tampil'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Jenis')->options(AboutHighlight::TYPES),
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
            'index' => ManageAboutHighlights::route('/'),
        ];
    }
}
