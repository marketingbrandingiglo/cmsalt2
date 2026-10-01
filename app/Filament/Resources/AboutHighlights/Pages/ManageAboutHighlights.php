<?php

namespace App\Filament\Resources\AboutHighlights\Pages;

use App\Filament\Resources\AboutHighlights\AboutHighlightResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAboutHighlights extends ManageRecords
{
    protected static string $resource = AboutHighlightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
