<?php

namespace App\Filament\Resources\MasterData\Suppliers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã NCC')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('name')
                    ->label('Tên nhà cung cấp')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tax_code')
                    ->label('MST')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('supplier_type')
                    ->label('Loại')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'material'    => 'info',
                        'service'     => 'gray',
                        'subcontract' => 'warning',
                        'equipment'   => 'primary',
                        default       => 'gray',
                    }),
                TextColumn::make('lead_time_days')
                    ->label('Lead time')
                    ->numeric()
                    ->suffix(' ngày')
                    ->sortable(),
                TextColumn::make('payment_days')
                    ->label('TT (ngày)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'inactive' => 'warning',
                        'blocked'  => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('supplier_type')
                    ->label('Loại NCC')
                    ->options([
                        'material'    => 'Nguyên vật liệu',
                        'service'     => 'Dịch vụ',
                        'subcontract' => 'Gia công',
                        'equipment'   => 'Thiết bị',
                    ]),
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(['active' => 'Hoạt động', 'inactive' => 'Ngừng', 'blocked' => 'Bị chặn']),
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
