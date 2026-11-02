<?php

namespace App\Filament\Resources\HomeSlides\Pages;

use App\Filament\Resources\HomeSlides\HomeSlideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHomeSlides extends ManageRecords
{
    protected static string $resource = HomeSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
