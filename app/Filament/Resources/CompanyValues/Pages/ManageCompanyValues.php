<?php

namespace App\Filament\Resources\CompanyValues\Pages;

use App\Filament\Concerns\EditsAboutSection;
use App\Filament\Resources\CompanyValues\CompanyValueResource;
use App\Filament\Support\Bilingual;
use App\Filament\Support\MediaUpload;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCompanyValues extends ManageRecords
{
    use EditsAboutSection;

    protected static string $resource = CompanyValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function aboutSectionHeading(): string
    {
        return 'Judul seksi Nilai i5';
    }

    protected function aboutSectionDescription(): ?string
    {
        return 'Logo i5 dan teks di sekitar lingkaran nilai. Lima nilai beserta penjelasannya ada di tabel di bawah.';
    }

    protected function aboutSectionFields(): array
    {
        return [
            MediaUpload::image('i5_logo', 'i5')
                ->label('Logo i5')
                ->helperText('PNG transparan, rasio 1:1. Logo yang "jatuh" dan berputar di scene Visi & Misi.'),
            Bilingual::input('valuesKicker', 'Subjudul nilai (muncul bersama ikon i5)', 'content'),
            Bilingual::input('valuesTitle', 'Judul nilai', 'content'),
            Bilingual::input('valuesIcon', 'Alt text logo i5', 'content'),
        ];
    }
}
