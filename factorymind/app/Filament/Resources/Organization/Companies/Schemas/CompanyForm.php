<?php

namespace App\Filament\Resources\Organization\Companies\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin công ty')
                    ->columns(2)
                    ->schema([
                        Select::make('parent_id')
                            ->label('Công ty mẹ')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('company_type')
                            ->label('Loại công ty')
                            ->options([
                                'holding'     => 'Tập đoàn',
                                'subsidiary'  => 'Công ty con',
                                'branch'      => 'Chi nhánh',
                                'factory'     => 'Nhà máy',
                            ])
                            ->required()
                            ->default('subsidiary'),
                        TextInput::make('code')
                            ->label('Mã')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('name')
                            ->label('Tên công ty')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('legal_name')
                            ->label('Tên pháp lý')
                            ->maxLength(255),
                        TextInput::make('tax_code')
                            ->label('Mã số thuế')
                            ->maxLength(20),
                        Select::make('currency_code')
                            ->label('Đơn vị tiền tệ')
                            ->options(['VND' => 'VND', 'USD' => 'USD', 'EUR' => 'EUR'])
                            ->required()
                            ->default('VND'),
                        Select::make('status')
                            ->label('Trạng thái')
                            ->options([
                                'active'   => 'Hoạt động',
                                'inactive' => 'Ngừng hoạt động',
                            ])
                            ->required()
                            ->default('active'),
                    ]),
            ]);
    }
}
