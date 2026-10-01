<?php

namespace App\Filament\Resources\CompanyValues\Pages;

use App\Filament\Resources\CompanyValues\CompanyValueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCompanyValues extends ManageRecords
{
    protected static string $resource = CompanyValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
