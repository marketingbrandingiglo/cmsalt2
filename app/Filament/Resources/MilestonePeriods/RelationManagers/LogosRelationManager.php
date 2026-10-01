<?php

namespace App\Filament\Resources\MilestonePeriods\RelationManagers;

use App\Filament\Support\MediaUpload;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LogosRelationManager extends RelationManager
{
    protected static string $relationship = 'logos';

    protected static ?string $title = 'Logo partner / penghargaan';

    protected static ?string $modelLabel = 'logo';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama (alt text)')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                MediaUpload::image('logo', 'milestones')
                    ->label('Logo')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
            ])
            ->headerActions([
                CreateAction::make(),
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
}
