<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages;
use App\Models\Product;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use BackedEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'จัดการสินค้า';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('ข้อมูลสินค้าหลัก')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('ชื่อสินค้า')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn(string $operation, $state, Set $set) =>
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null
                            ),

                        Forms\Components\TextInput::make('slug')
                            ->label('URL Slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(Product::class, 'slug', ignoreRecord: true),

                        Forms\Components\RichEditor::make('description')
                            ->label('รายละเอียดสินค้า')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('images')
                            ->label('รูปภาพสินค้า')
                            ->multiple()
                            ->image()
                            ->directory('products')
                            ->reorderable()
                            ->columnSpanFull(),

                    ])->columnSpan(2),

                Group::make()
                    ->schema([
                        Section::make('ราคาและคลังสินค้า')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('ราคาสินค้า (บาท)')
                                    ->numeric()
                                    ->prefix('฿')
                                    ->required(),

                                Forms\Components\TextInput::make('stock')
                                    ->label('จำนวนในสต็อก')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                            ]),

                        Section::make('สถานะและการแสดงผล')
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('เปิดใช้งานสินค้า')
                                    ->default(true),

                                View::make('filament.components.product-images-slider')
                                    ->columnSpanFull()
                                    ->visible(fn($record) => $record && !empty($record->images)),
                            ]),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อสินค้า')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('ราคา')
                    ->money('THB')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('images')
                    ->label('รูปภาพ')
                    ->square()
                    ->stacked(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('คงเหลือในสต็อก')
                    ->sortable()
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state <= 5 => 'danger',
                        $state <= 20 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('สถานะ')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('วันที่เพิ่ม')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('สถานะการเปิดใช้งาน'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
