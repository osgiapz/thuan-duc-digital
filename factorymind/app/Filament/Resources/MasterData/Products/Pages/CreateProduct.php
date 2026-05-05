<?php

namespace App\Filament\Resources\MasterData\Products\Pages;

use App\Filament\Resources\MasterData\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
