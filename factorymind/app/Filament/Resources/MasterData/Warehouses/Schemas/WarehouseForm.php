<?php

namespace App\Filament\Resources\MasterData\Warehouses\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin kho')
                    ->columns(2)
                    ->schema([
                        Select::make('plant_id')
                            ->label('Nhà máy')
                            ->relationship('plant', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('warehouse_type')
                            ->label('Loại kho')
                            ->options([
                                'raw_material'   => 'Nguyên vật liệu',
                                'wip'            => 'WIP (Bán thành phẩm)',
                                'finished_goods' => 'Thành phẩm',
                                'packaging'      => 'Bao bì',
                                'spare_parts'    => 'Phụ tùng',
                            ])
                            ->required()
                            ->default('raw_material'),
                        TextInput::make('code')
                            ->label('Mã kho')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('name')
                            ->label('Tên kho')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Đang hoạt động')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
