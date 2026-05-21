<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use App\Models\Customer;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ข้อมูลใบสั่งซื้อ')
                    ->schema([
                        Select::make('customer_id')
                            ->label('ลูกค้า')
                            ->options(fn () => Customer::pluck('name', '_id')->toArray())
                            ->required()
                            ->searchable()
                            ->placeholder('เลือกสมาชิกลูกค้า'),

                        TextInput::make('total_price')
                            ->label('ยอดขายรวม (บาท)')
                            ->numeric()
                            ->prefix('฿')
                            ->required()
                            ->placeholder('0.00'),

                        Select::make('status')
                            ->label('สถานะใบสั่งซื้อ')
                            ->options([
                                'pending' => 'รอดำเนินการ (Pending)',
                                'completed' => 'เสร็จสิ้น (Completed)',
                                'cancelled' => 'ยกเลิก (Cancelled)',
                            ])
                            ->default('pending')
                            ->required(),

                        DateTimePicker::make('created_at')
                            ->label('วันที่สั่งซื้อ')
                            ->default(now())
                            ->required(),
                    ])->columns(2)
            ]);
    }
}
