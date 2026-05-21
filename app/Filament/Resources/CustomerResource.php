<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Hash;
use BackedEnum;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'จัดการสมาชิก';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('ข้อมูลลูกค้า')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('ชื่อลูกค้า')
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label('อีเมล')
                            ->email()
                            ->required(),

                        Forms\Components\TextInput::make('phone')
                            ->label('เบอร์โทรศัพท์'),

                        Forms\Components\TextInput::make('password')
                            ->label('รหัสผ่านใหม่')
                            ->placeholder(fn(string $operation): string => $operation === 'edit' ? '•••••••• (ปล่อยว่างไว้หากไม่ต้องการเปลี่ยน)' : 'กรอกรหัสผ่านเริ่มต้น')
                            ->password()
                            ->revealable()
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, $state) {
                                $component->state('');
                            })
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->dehydrateStateUsing(fn(string $state): string => \Illuminate\Support\Facades\Hash::make($state)),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('คะแนนสะสม (พ้อยท์)')
                    ->schema([
                        Forms\Components\TextInput::make('points_available')
                            ->label('แต้มคงเหลือ')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('points_used')
                            ->label('ใช้ไปแล้ว')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('points_total_earned')
                            ->label('สะสมทั้งหมด')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // 📊 1. โซนกางคอลัมน์แสดงผลแต้ม 3 สถานะหน้าตาราง
            ->columns([
                TextColumn::make('name')
                    ->label('ชื่อลูกค้า')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('อีเมล')
                    ->searchable(),

                TextColumn::make('points_available')
                    ->label('แต้มคงเหลือ')
                    ->numeric()
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('points_used')
                    ->label('ใช้ไปแล้ว')
                    ->numeric()
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('points_total_earned')
                    ->label('สะสมทั้งหมด')
                    ->numeric()
                    ->color('info')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            // 🔥 2. โซนปุ่มกดเติมแต้ม/หักแต้ม แปะท้ายแถวของลูกค้าแต่ละคน
            ->actions([

                // ➕ ปุ่มที่ 1: เติมแต้ม (Add Points)
                Actions\Action::make('add_points')
                    ->label('เติมแต้ม')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('จำนวนแต้มที่ต้องการเติม')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('reason')
                            ->label('เหตุผล/หมายเหตุ')
                            ->default('แอดมินเติมให้ด้วยตนเอง'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->points_available = ($record->points_available ?? 0) + $data['amount'];
                        $record->points_total_earned = ($record->points_total_earned ?? 0) + $data['amount'];

                        $history = $record->point_history ?? [];
                        $history[] = [
                            'type' => 'earn',
                            'amount' => (int) $data['amount'],
                            'description' => $data['reason'],
                            'created_at' => now()->toIso8601String(),
                        ];
                        $record->point_history = $history;
                        $record->save();

                        \Filament\Notifications\Notification::make()
                            ->title('เติมแต้มสำเร็จแล้วครับพี่!')
                            ->success()
                            ->send();
                    }),

                // ➖ ปุ่มที่ 2: หักแต้ม (Deduct Points)
                Actions\Action::make('deduct_points')
                    ->label('หักแต้ม')
                    ->icon('heroicon-o-minus-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('จำนวนแต้มที่ต้องการหัก')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('reason')
                            ->label('เหตุผล (เช่น แลกของรางวัล)')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        if (($record->points_available ?? 0) < $data['amount']) {
                            \Filament\Notifications\Notification::make()
                                ->title('แต้มไม่พอหักครับพี่!')
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->points_available -= $data['amount'];
                        $record->points_used = ($record->points_used ?? 0) + $data['amount'];

                        $history = $record->point_history ?? [];
                        $history[] = [
                            'type' => 'redeem',
                            'amount' => (int) $data['amount'],
                            'description' => $data['reason'],
                            'created_at' => now()->toIso8601String(),
                        ];
                        $record->point_history = $history;
                        $record->save();

                        \Filament\Notifications\Notification::make()
                            ->title('หักแต้มสำเร็จเรียบร้อยครับ!')
                            ->success()
                            ->send();
                    }),

                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
