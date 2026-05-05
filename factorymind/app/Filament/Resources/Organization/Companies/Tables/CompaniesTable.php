<?php

namespace App\Filament\Resources\Organization\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CompaniesTable
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
                    ->label('Tên công ty')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('legal_name')
                    ->label('Tên pháp lý')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('tax_code')
                    ->label('MST')
                    ->searchable(),
                TextColumn::make('company_type')
                    ->label('Loại')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'holding'    => 'primary',
                        'subsidiary' => 'info',
                        'factory'    => 'warning',
                        default      => 'gray',
                    }),
                TextColumn::make('parent.name')
                    ->label('Công ty mẹ')
                    ->toggleable(),
                TextColumn::make('currency_code')
                    ->label('Tiền tệ'),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'inactive' => 'danger',
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
                    ->options(['active' => 'Hoạt động', 'inactive' => 'Ngừng']),
                SelectFilter::make('company_type')
                    ->label('Loại')
                    ->options([
                        'holding'    => 'Tập đoàn',
                        'subsidiary' => 'Công ty con',
                        'branch'     => 'Chi nhánh',
                        'factory'    => 'Nhà máy',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code');
    }
}
