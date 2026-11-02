<?php

namespace App\Filament\Resources\NewsArticles;

use App\Filament\Resources\NewsArticles\Pages\ManageNewsArticles;
use App\Filament\Support\Bilingual;
use App\Filament\Support\MediaUpload;
use App\Models\NewsArticle;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class NewsArticleResource extends Resource
{
    protected static ?string $model = NewsArticle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Beranda';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Berita & Insight';

    protected static ?string $modelLabel = 'berita';

    protected static ?string $pluralModelLabel = 'Berita & Insight';

    protected static ?string $slug = 'home/news';

    protected static ?string $recordTitleAttribute = 'title.id';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tag')
                    ->label('Kategori')
                    ->options(NewsArticle::tagOptions())
                    ->default('insight')
                    ->required(),
                DatePicker::make('published_at')
                    ->label('Tanggal terbit')
                    ->default(now())
                    ->native(false)
                    ->displayFormat('j M Y')
                    ->required(),
                MediaUpload::image('image', 'news', 'home')
                    ->label('Gambar')
                    ->helperText('Opsional — tanpa gambar, kartu memakai latar gradien merek.')
                    ->columnSpanFull(),
                Bilingual::textarea('title', 'Judul', required: true, rows: 2),
                Bilingual::textarea('excerpt', 'Ringkasan', rows: 3),
                Toggle::make('is_active')
                    ->label('Tampilkan')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Urut dari tanggal terbit terbaru. 4 berita teratas yang aktif tampil di mosaik Beranda.')
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('image')
                    ->label('Gambar')
                    ->disk(config('cms.media_disk'))
                    ->imageHeight(40),
                TextColumn::make('published_at')
                    ->label('Tanggal')
                    ->date('j M Y')
                    ->sortable(),
                TextColumn::make('tag')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => NewsArticle::tagOptions()[$state] ?? $state),
                TextColumn::make('title.id')
                    ->label('Judul (ID)')
                    ->weight('bold')
                    ->wrap()
                    ->limit(90),
                ToggleColumn::make('is_active')
                    ->label('Tampil'),
            ])
            ->filters([
                SelectFilter::make('tag')->label('Kategori')->options(NewsArticle::tagOptions()),
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
            'index' => ManageNewsArticles::route('/'),
        ];
    }
}
