<?php

namespace App\Filament\Resources\MasterData\Suppliers\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin nhà cung cấp')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Mã NCC')
                            ->required()
                            ->maxLength(30),
                        TextInput::make('name')
                            ->label('Tên nhà cung cấp')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('legal_name')
                            ->label('Tên pháp lý')
                            ->maxLength(255),
                        TextInput::make('tax_code')
                            ->label('Mã số thuế')
                            ->maxLength(20),
                        Select::make('supplier_type')
                            ->label('Loại NCC')
                            ->options([
                                'material'    => 'Nguyên vật liệu',
                                'service'     => 'Dịch vụ',
                                'subcontract' => 'Gia công',
                                'equipment'   => 'Thiết bị',
                            ])
                            ->required()
                            ->default('material'),
                        Select::make('status')
                            ->label('Trạng thái')
                            ->options([
                                'active'   => 'Hoạt động',
                                'inactive' => 'Ngừng',
                                'blocked'  => 'Bị chặn',
                            ])
                            ->required()
                            ->default('active'),
                    ]),

                Section::make('Điều khoản')
                    ->columns(2)
                    ->schema([
                        TextInput::make('lead_time_days')
                            ->label('Lead time (ngày)')
                            ->numeric()
                            ->default(7),
                        TextInput::make('payment_days')
                            ->label('Thanh toán (ngày)')
                            ->numeric()
                            ->default(30),
                    ]),
            ]);
    }
}
