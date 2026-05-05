<?php

namespace App\Filament\Resources\Organization\Companies;

use App\Filament\Resources\Organization\Companies\Pages\CreateCompany;
use App\Filament\Resources\Organization\Companies\Pages\EditCompany;
use App\Filament\Resources\Organization\Companies\Pages\ListCompanies;
use App\Filament\Resources\Organization\Companies\Schemas\CompanyForm;
use App\Filament\Resources\Organization\Companies\Tables\CompaniesTable;
use App\Models\Organization\Company;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Công ty';

    protected static string|\UnitEnum|null $navigationGroup = 'Tổ chức';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return CompanyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompaniesTable::configure($table);
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
            'index' => ListCompanies::route('/'),
            'create' => CreateCompany::route('/create'),
            'edit' => EditCompany::route('/{record}/edit'),
        ];
    }
}
