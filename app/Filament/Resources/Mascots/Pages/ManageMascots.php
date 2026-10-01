<?php

namespace App\Filament\Resources\Mascots\Pages;

use App\Filament\Resources\Mascots\MascotResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMascots extends ManageRecords
{
    protected static string $resource = MascotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
