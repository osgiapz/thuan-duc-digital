<?php

namespace App\Filament\Resources\MasterData\Products\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin sản phẩm')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Mã sản phẩm')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('name')
                            ->label('Tên sản phẩm')
                            ->required()
                            ->maxLength(255),
                        Select::make('product_type')
                            ->label('Loại')
                            ->options([
                                'finished_good' => 'Thành phẩm',
                                'semi_finished' => 'Bán thành phẩm',
                                'raw_material'  => 'Nguyên liệu thô',
                                'packaging'     => 'Bao bì',
                                'service'       => 'Dịch vụ',
                            ])
                            ->required()
                            ->default('finished_good'),
                        Select::make('category_id')
                            ->label('Danh mục')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('base_uom')
                            ->label('Đơn vị tính')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('weight_kg')
                            ->label('Khối lượng (kg)')
                            ->numeric()
                            ->nullable(),
                        Textarea::make('description')
                            ->label('Mô tả')
                            ->columnSpanFull()
                            ->rows(2),
                    ]),

                Section::make('Giá & Tồn kho')
                    ->columns(3)
                    ->schema([
                        TextInput::make('standard_cost')
                            ->label('Giá thành chuẩn')
                            ->numeric()
                            ->default(0)
                            ->prefix('₫'),
                        TextInput::make('list_price')
                            ->label('Giá niêm yết')
                            ->numeric()
                            ->default(0)
                            ->prefix('₫'),
                        Select::make('currency_code')
                            ->label('Tiền tệ')
                            ->options(['VND' => 'VND', 'USD' => 'USD'])
                            ->default('VND'),
                        TextInput::make('lead_time_days')
                            ->label('Lead time (ngày)')
                            ->numeric()
                            ->default(0),
                        TextInput::make('reorder_point')
                            ->label('Điểm đặt hàng lại')
                            ->numeric()
                            ->default(0),
                        TextInput::make('safety_stock')
                            ->label('Tồn kho an toàn')
                            ->numeric()
                            ->default(0),
                    ]),

                Section::make('Trạng thái')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Đang hoạt động')
                            ->default(true),
                    ]),
            ]);
    }
}
