<?php

namespace App\Filament\Resources\Organization\Plants;

use App\Filament\Resources\Organization\Plants\Pages\CreatePlant;
use App\Filament\Resources\Organization\Plants\Pages\EditPlant;
use App\Filament\Resources\Organization\Plants\Pages\ListPlants;
use App\Filament\Resources\Organization\Plants\Schemas\PlantForm;
use App\Filament\Resources\Organization\Plants\Tables\PlantsTable;
use App\Models\Organization\Plant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlantResource extends Resource
{
    protected static ?string $model = Plant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $navigationLabel = 'Nhà máy';

    protected static string|\UnitEnum|null $navigationGroup = 'Tổ chức';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PlantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlantsTable::configure($table);
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
            'index' => ListPlants::route('/'),
            'create' => CreatePlant::route('/create'),
            'edit' => EditPlant::route('/{record}/edit'),
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
