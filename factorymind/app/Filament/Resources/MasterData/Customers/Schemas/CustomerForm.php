<?php

namespace App\Filament\Resources\MasterData\Customers\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin khách hàng')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Mã KH')
                            ->required()
                            ->maxLength(30),
                        TextInput::make('name')
                            ->label('Tên khách hàng')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('legal_name')
                            ->label('Tên pháp lý')
                            ->maxLength(255),
                        TextInput::make('tax_code')
                            ->label('Mã số thuế')
                            ->maxLength(20),
                        Select::make('customer_type')
                            ->label('Loại khách hàng')
                            ->options([
                                'domestic' => 'Nội địa',
                                'export'   => 'Xuất khẩu',
                                'fdi'      => 'FDI',
                            ])
                            ->required()
                            ->default('domestic'),
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

                Section::make('Điều khoản thanh toán')
                    ->columns(3)
                    ->schema([
                        TextInput::make('credit_limit')
                            ->label('Hạn mức tín dụng')
                            ->numeric()
                            ->default(0)
                            ->prefix('₫'),
                        Select::make('credit_limit_currency')
                            ->label('Tiền tệ')
                            ->options(['VND' => 'VND', 'USD' => 'USD'])
                            ->default('VND'),
                        TextInput::make('payment_days')
                            ->label('Thanh toán (ngày)')
                            ->numeric()
                            ->default(30),
                    ]),
            ]);
    }
}
