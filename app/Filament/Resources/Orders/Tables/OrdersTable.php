<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\Customer;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('ลูกค้า')
                    ->searchable()
                    ->sortable()
                    ->placeholder('ไม่พบข้อมูลลูกค้า'),

                TextColumn::make('total_price')
                    ->label('ยอดขายรวม')
                    ->money('THB')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('วันที่สั่งซื้อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('กรองตามสถานะ')
                    ->options([
                        'completed' => 'เสร็จสิ้น (Completed)',
                        'pending' => 'รอดำเนินการ (Pending)',
                        'cancelled' => 'ยกเลิก (Cancelled)',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
