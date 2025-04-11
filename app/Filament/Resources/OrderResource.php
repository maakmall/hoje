<?php

namespace App\Filament\Resources;

use App\Enums\VariantBeverage;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Helpers\Numeric;
use App\Models\MenuPrice;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $modelLabel = 'Pesanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Pesanan')
                        ->description('Pilih menu yang dipesan')
                        ->icon('heroicon-m-shopping-bag')
                        ->schema([
                            Forms\Components\Select::make('user_id')
                                ->relationship('user', 'name')
                                ->label('Pelanggan')
                                ->searchable()
                                ->preload(),
                            Forms\Components\Repeater::make('orderMenus')
                                ->label('')
                                ->relationship()
                                ->columns(4)
                                ->addActionLabel('Tambah Menu')
                                ->schema([
                                    Forms\Components\Select::make('menu_id')
                                        ->relationship('menu', 'name')
                                        ->label('Menu')
                                        ->searchable()
                                        ->preload()
                                        ->columnSpan(function (?int $state): int {
                                            if ($state) {
                                                $menuPrices = MenuPrice::where('menu_id', $state)->get();
                                                if ($menuPrices->count() > 1) {
                                                    return 2;
                                                }
                                            }

                                            return 3;
                                        })
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, Get $get, ?int $state): void {
                                            if ($state) {
                                                $menuPrices = MenuPrice::where('menu_id', $state)->get();
                                                if ($menuPrices->count() === 1) {
                                                    $price = $menuPrices->first()->price;
                                                    $set('price', Numeric::rupiah($price));
                                                    $set('subtotal_price', Numeric::rupiah($price * ($get('quantity') ?? 1)));
                                                } else {
                                                    $set('price', 0);
                                                    $set('subtotal_price', 0);
                                                }

                                                $set('quantity', 1);
                                            } else {
                                                $set('price', 0);
                                                $set('quantity', null);
                                                $set('subtotal_price', 0);
                                            }
                                        })
                                        ->disableOptionWhen(function ($value, $label, Get $get) {
                                            $selectedMenuIds = collect($get('../../orderMenus'))
                                                ->pluck('menu_id')
                                                ->filter()
                                                ->toArray();
                                    
                                            $currentIndex = $get('__index');
                                            if ($currentIndex !== null) {
                                                $currentMenuId = data_get($get("../../orderMenus.{$currentIndex}"), 'menu_id');
                                                $selectedMenuIds = array_filter($selectedMenuIds, fn ($id) => $id != $currentMenuId);
                                            }
                                    
                                            return in_array($value, $selectedMenuIds);
                                        }),
                                    Forms\Components\Select::make('variant_beverage')
                                        ->label('Varian')
                                        ->options(VariantBeverage::select())
                                        ->live()
                                        ->visible(function (Get $get): bool {
                                            if ($get('menu_id')) {
                                                $prices = MenuPrice::where('menu_id', $get('menu_id'))->get();
                                                if ($prices->count() > 1) {
                                                    return true;
                                                }
                                            }

                                            return false;
                                        })
                                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                            if ($state) {
                                                $menuPrices = MenuPrice::where('menu_id', $get('menu_id'))
                                                    ->where('variant_beverage', $state)
                                                    ->first();

                                                $set('price', Numeric::rupiah($menuPrices->price));
                                                $set('subtotal_price', Numeric::rupiah($menuPrices->price * ($get('quantity') ?? 1)));
                                            } else {
                                                $set('price', 0);
                                                $set('subtotal_price', 0);
                                            }
                                        }),
                                    Forms\Components\TextInput::make('price')
                                        ->label('Harga')
                                        ->disabled()
                                        ->prefix('Rp')
                                        ->default(0)
                                        ->afterStateHydrated(function (Get $get, Set $set, string $operation): void {
                                            if ($operation === 'edit') {
                                                $price = MenuPrice::where('menu_id', $get('menu_id'))
                                                    ->where('variant_beverage', $get('variant_beverage'))
                                                    ->first();
                                                if ($price) {
                                                    $set('price', Numeric::rupiah($price->price));
                                                } else {
                                                    $set('price', 0);
                                                }
                                            }
                                        }),
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->minValue(1)
                                        ->columnSpan(3)
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, Get $get, ?int $state): void {
                                            if ($state) {
                                                $price = str_replace('.', '', $get('price'));
                                                $set('subtotal_price', Numeric::rupiah($price * $state));
                                            } else {
                                                $set('subtotal_price', 0);
                                            }
                                        }),
                                    Forms\Components\TextInput::make('subtotal_price')
                                        ->label('Subtotal')
                                        ->readOnly()
                                        ->prefix('Rp')
                                        ->stripCharacters('.')
                                        ->default(0)
                                        ->afterStateHydrated(function (Set $set, ?int $state): void {
                                            if ($state) {
                                                $set('subtotal_price', Numeric::rupiah($state));
                                            }
                                        })
                                ]),
                            Forms\Components\Section::make('')
                                ->columns(4)
                                ->columnStart(1)
                                ->schema([
                                    Forms\Components\Placeholder::make('total')
                                        ->label('Total')
                                        ->content(fn(Get $get): string => static::calculateTotal($get)),
                                ])
                        ]),
                    Forms\Components\Wizard\Step::make('Pembayaran')
                        ->icon('heroicon-m-credit-card')
                        ->schema([])
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): void {
                $query->withSum('orderMenus', 'subtotal_price');
            })
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pelanggan')
                    ->placeholder('Tanpa User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('datetime')
                    ->label('Tanggal')
                    ->dateTime('j M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_menus_sum_subtotal_price')
                    ->label('Total')
                    ->prefix('Rp ')
                    ->numeric(thousandsSeparator: '.')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    /**
     * Calculate total price from orderMenus
     */
    public static function calculateTotal(Get $get): string
    {
        $total = 0;

        foreach ($get('orderMenus') as $orderMenu) {
            $total += (int) str_replace('.', '', $orderMenu['subtotal_price']);
        }

        return 'Rp ' . Numeric::rupiah($total);
    }
}
