<?php

namespace App\Filament\Resources\MasterData\Products\Tables;

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

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã SP')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('name')
                    ->label('Tên sản phẩm')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('product_type')
                    ->label('Loại')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'finished_good' => 'success',
                        'semi_finished' => 'warning',
                        'raw_material'  => 'info',
                        'packaging'     => 'gray',
                        default         => 'gray',
                    }),
                TextColumn::make('category.name')
                    ->label('Danh mục')
                    ->toggleable(),
                TextColumn::make('base_uom')
                    ->label('ĐVT'),
                TextColumn::make('standard_cost')
                    ->label('Giá thành')
                    ->numeric(decimalPlaces: 0, thousandsSeparator: ',')
                    ->suffix(' ₫')
                    ->sortable(),
                TextColumn::make('list_price')
                    ->label('Giá bán')
                    ->numeric(decimalPlaces: 0, thousandsSeparator: ',')
                    ->suffix(' ₫')
                    ->sortable(),
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
                SelectFilter::make('product_type')
                    ->label('Loại sản phẩm')
                    ->options([
                        'finished_good' => 'Thành phẩm',
                        'semi_finished' => 'Bán thành phẩm',
                        'raw_material'  => 'Nguyên liệu thô',
                        'packaging'     => 'Bao bì',
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
