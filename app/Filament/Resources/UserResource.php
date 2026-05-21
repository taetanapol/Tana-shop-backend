<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Hash;
use BackedEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'จัดการผู้ดูแลระบบ/สมาชิก';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('ข้อมูลทั่วไป')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('ชื่อ-นามสกุล')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('อีเมล')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->label('เบอร์โทรศัพท์'),
                        Forms\Components\Select::make('role')
                            ->label('บทบาท')
                            ->options([
                                'admin' => 'ผู้ดูแลระบบ (Admin)',
                                'member' => 'สมาชิกทั่วไป (Member)',
                            ])
                            ->required()
                            ->default('member'),
                    ])->columns(2),

                Section::make('ความปลอดภัยและที่อยู่')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('รหัสผ่าน')
                            ->password()
                            ->revealable()
                            ->placeholder(fn(string $operation): string => $operation === 'edit' ? '•••••••• (ปล่อยว่างไว้หากไม่ต้องการเปลี่ยน)' : 'กรอกรหัสผ่านเริ่มต้น')
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state)),
                        Forms\Components\Textarea::make('address')
                            ->label('ที่อยู่')
                            ->columnSpanFull(),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('ชื่อ-นามสกุล')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('อีเมล')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('เบอร์โทรศัพท์')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('บทบาท')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'danger',
                        'member' => 'success',
                        default => 'secondary',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('วันที่สมัคร')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('กรองตามบทบาท')
                    ->options([
                        'admin' => 'Admin',
                        'member' => 'Member',
                    ]),
            ])
            ->actions([
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
