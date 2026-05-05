<?php

namespace App\Filament\Resources\MasterData\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã KH')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('name')
                    ->label('Tên khách hàng')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tax_code')
                    ->label('MST')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('customer_type')
                    ->label('Loại')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'domestic' => 'info',
                        'export'   => 'success',
                        'fdi'      => 'warning',
                        default    => 'gray',
                    }),
                TextColumn::make('credit_limit')
                    ->label('Hạn mức TD')
                    ->numeric(decimalPlaces: 0, thousandsSeparator: ',')
                    ->suffix(' ₫')
                    ->sortable()
                    ->toggleable(),
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
                SelectFilter::make('customer_type')
                    ->label('Loại KH')
                    ->options(['domestic' => 'Nội địa', 'export' => 'Xuất khẩu', 'fdi' => 'FDI']),
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
