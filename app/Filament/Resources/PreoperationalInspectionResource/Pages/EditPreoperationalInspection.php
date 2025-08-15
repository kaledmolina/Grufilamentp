<?php

namespace App\Filament\Resources\PreoperationalInspectionResource\Pages;

use App\Filament\Resources\PreoperationalInspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPreoperationalInspection extends EditRecord
{
    protected static string $resource = PreoperationalInspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
