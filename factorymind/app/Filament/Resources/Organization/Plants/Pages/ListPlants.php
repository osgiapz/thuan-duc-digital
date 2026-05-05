<?php

namespace App\Filament\Resources\Organization\Plants\Pages;

use App\Filament\Resources\Organization\Plants\PlantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlants extends ListRecords
{
    protected static string $resource = PlantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
