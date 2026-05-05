<?php

namespace App\Filament\Resources\Organization\Plants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PlantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('name')
                    ->label('Tên nhà máy')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Công ty')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('plant_type')
                    ->label('Loại')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'manufacturing' => 'warning',
                        'assembly'      => 'info',
                        'warehouse'     => 'gray',
                        default         => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'inactive' => 'warning',
                        'shutdown' => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(['active' => 'Hoạt động', 'inactive' => 'Ngừng', 'shutdown' => 'Đóng cửa']),
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
