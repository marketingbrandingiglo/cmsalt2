<?php

namespace App\Filament\Resources\MilestonePeriods\Pages;

use App\Filament\Resources\MilestonePeriods\MilestonePeriodResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMilestonePeriod extends EditRecord
{
    protected static string $resource = MilestonePeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
