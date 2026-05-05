<?php

namespace App\Filament\Resources\MasterData\Customers\Pages;

use App\Filament\Resources\MasterData\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
