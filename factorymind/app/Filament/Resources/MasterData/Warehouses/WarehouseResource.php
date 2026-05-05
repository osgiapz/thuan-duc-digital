<?php

namespace App\Filament\Resources\MasterData\Warehouses;

use App\Filament\Resources\MasterData\Warehouses\Pages\CreateWarehouse;
use App\Filament\Resources\MasterData\Warehouses\Pages\EditWarehouse;
use App\Filament\Resources\MasterData\Warehouses\Pages\ListWarehouses;
use App\Filament\Resources\MasterData\Warehouses\Schemas\WarehouseForm;
use App\Filament\Resources\MasterData\Warehouses\Tables\WarehousesTable;
use App\Models\MasterData\Warehouse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Kho hàng';

    protected static string|\UnitEnum|null $navigationGroup = 'Dữ liệu gốc';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return WarehouseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WarehousesTable::configure($table);
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
            'index' => ListWarehouses::route('/'),
            'create' => CreateWarehouse::route('/create'),
            'edit' => EditWarehouse::route('/{record}/edit'),
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
