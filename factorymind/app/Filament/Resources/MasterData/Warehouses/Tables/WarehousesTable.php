<?php

namespace App\Filament\Resources\MasterData\Warehouses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class WarehousesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã kho')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('name')
                    ->label('Tên kho')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('plant.name')
                    ->label('Nhà máy')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('warehouse_type')
                    ->label('Loại kho')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'raw_material'   => 'info',
                        'wip'            => 'warning',
                        'finished_goods' => 'success',
                        'packaging'      => 'gray',
                        'spare_parts'    => 'primary',
                        default          => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('warehouse_type')
                    ->label('Loại kho')
                    ->options([
                        'raw_material'   => 'Nguyên vật liệu',
                        'wip'            => 'WIP',
                        'finished_goods' => 'Thành phẩm',
                        'packaging'      => 'Bao bì',
                        'spare_parts'    => 'Phụ tùng',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Đang hoạt động'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('code');
    }
}
