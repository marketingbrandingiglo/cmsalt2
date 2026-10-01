<?php

namespace App\Filament\Resources\Mascots;

use App\Filament\Resources\Mascots\Pages\ManageMascots;
use App\Filament\Support\MediaUpload;
use App\Models\Mascot;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class MascotResource extends Resource
{
    protected static ?string $model = Mascot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFaceSmile;

    protected static string|UnitEnum|null $navigationGroup = 'Tentang Kami';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Maskot';

    protected static ?string $modelLabel = 'maskot';

    protected static ?string $pluralModelLabel = 'Maskot';

    protected static ?string $slug = 'about/mascots';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        $bio = fn (string $locale, string $language) => Repeater::make("bio.{$locale}")
            ->label("Bio ({$language})")
            ->simple(Textarea::make('paragraph')->rows(4)->autosize()->required())
            ->addActionLabel('Tambah paragraf')
            ->reorderable()
            ->minItems($locale === config('cms.fallback_locale') ? 1 : 0)
            ->defaultItems(1);

        return $schema
            ->components([
                Grid::make(2)->columnSpanFull()->schema([
                    Select::make('key')
                        ->label('Slot maskot')
                        ->options(array_combine(Mascot::KEYS, array_map(Str::ucfirst(...), Mascot::KEYS)))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('Menentukan posisi maskot di scene MascotScene (frontend).'),
                    TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(50),
                ]),
                Section::make('Bio')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema(collect(config('cms.locales'))
                        ->map(fn (string $language, string $locale) => $bio($locale, $language))
                        ->values()
                        ->all()),
                Section::make('Gambar')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        MediaUpload::image('scene_image', 'mascots')
                            ->label('Gambar di scene bersama (opsional)'),
                        MediaUpload::image('profile_image', 'mascots')
                            ->label('Gambar profil (panel bio)'),
                    ]),
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
                ImageColumn::make('profile_image')
                    ->label('Profil')
                    ->disk(config('cms.media_disk'))
                    ->imageHeight(56),
                TextColumn::make('name')
                    ->label('Nama')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('key')
                    ->label('Slot')
                    ->badge(),
                TextColumn::make('bio.id')
                    ->label('Bio (ID)')
                    ->formatStateUsing(fn ($state): string => is_array($state) ? implode(' ', $state) : (string) $state)
                    ->wrap()
                    ->limit(100),
                ToggleColumn::make('is_active')
                    ->label('Tampil'),
            ])
            ->recordActions([
                EditAction::make()->modalWidth('5xl'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMascots::route('/'),
        ];
    }
}
