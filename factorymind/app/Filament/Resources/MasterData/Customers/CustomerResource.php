<?php

namespace App\Filament\Resources\MasterData\Customers;

use App\Filament\Resources\MasterData\Customers\Pages\CreateCustomer;
use App\Filament\Resources\MasterData\Customers\Pages\EditCustomer;
use App\Filament\Resources\MasterData\Customers\Pages\ListCustomers;
use App\Filament\Resources\MasterData\Customers\Schemas\CustomerForm;
use App\Filament\Resources\MasterData\Customers\Tables\CustomersTable;
use App\Models\MasterData\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Khách hàng';

    protected static string|\UnitEnum|null $navigationGroup = 'Dữ liệu gốc';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
