<?php

namespace App\Filament\Resources\MasterData\Suppliers;

use App\Filament\Resources\MasterData\Suppliers\Pages\CreateSupplier;
use App\Filament\Resources\MasterData\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\MasterData\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\MasterData\Suppliers\Schemas\SupplierForm;
use App\Filament\Resources\MasterData\Suppliers\Tables\SuppliersTable;
use App\Models\MasterData\Supplier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Nhà cung cấp';

    protected static string|\UnitEnum|null $navigationGroup = 'Dữ liệu gốc';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return SupplierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuppliersTable::configure($table);
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
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'edit' => EditSupplier::route('/{record}/edit'),
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
