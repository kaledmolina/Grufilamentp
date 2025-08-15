<?php

namespace App\Filament\Resources\PreoperationalInspectionResource\Pages;

use App\Filament\Resources\PreoperationalInspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPreoperationalInspections extends ListRecords
{
    protected static string $resource = PreoperationalInspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
