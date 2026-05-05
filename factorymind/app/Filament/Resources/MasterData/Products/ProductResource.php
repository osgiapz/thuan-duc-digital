<?php

namespace App\Filament\Resources\MasterData\Products;

use App\Filament\Resources\MasterData\Products\Pages\CreateProduct;
use App\Filament\Resources\MasterData\Products\Pages\EditProduct;
use App\Filament\Resources\MasterData\Products\Pages\ListProducts;
use App\Filament\Resources\MasterData\Products\Schemas\ProductForm;
use App\Filament\Resources\MasterData\Products\Tables\ProductsTable;
use App\Models\MasterData\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $navigationLabel = 'Sản phẩm';

    protected static string|\UnitEnum|null $navigationGroup = 'Dữ liệu gốc';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
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
