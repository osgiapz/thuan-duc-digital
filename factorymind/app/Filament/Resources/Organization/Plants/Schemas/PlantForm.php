<?php

namespace App\Filament\Resources\Organization\Plants\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PlantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin nhà máy')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Công ty')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('plant_type')
                            ->label('Loại nhà máy')
                            ->options([
                                'manufacturing' => 'Sản xuất',
                                'assembly'      => 'Lắp ráp',
                                'warehouse'     => 'Kho',
                                'r_and_d'       => 'R&D',
                            ])
                            ->nullable(),
                        TextInput::make('code')
                            ->label('Mã nhà máy')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('name')
                            ->label('Tên nhà máy')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Trạng thái')
                            ->options([
                                'active'   => 'Hoạt động',
                                'inactive' => 'Ngừng',
                                'shutdown' => 'Đóng cửa',
                            ])
                            ->required()
                            ->default('active'),
                    ]),
            ]);
    }
}
