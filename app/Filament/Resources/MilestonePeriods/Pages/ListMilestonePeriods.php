<?php

namespace App\Filament\Resources\MilestonePeriods\Pages;

use App\Filament\Resources\MilestonePeriods\MilestonePeriodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMilestonePeriods extends ListRecords
{
    protected static string $resource = MilestonePeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
