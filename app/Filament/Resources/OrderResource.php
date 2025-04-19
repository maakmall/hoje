<?php

namespace App\Filament\Resources;

use App\Enums\VariantBeverage;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Helpers\Numeric;
use App\Models\MenuPrice;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $modelLabel = 'Pesanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Pesanan')
                        ->description('Pilih menu yang dipesan')
                        ->icon('heroicon-m-shopping-bag')
                        ->columns(3)
                        ->schema([
                            Forms\Components\Group::make([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Select::make('user_id')
                                            ->relationship('user', 'name', function (Builder $query): void {
                                                $query->orderBy('name');
                                            })
                                            ->label('Pelanggan')
                                            ->getOptionLabelFromRecordUsing(
                                                fn(User $record): string => "{$record->id} - {$record->name}"
                                            )
                                            ->searchable(['name', 'id'])
                                            ->preload(),
                                    ]),
                            ])->columnSpan(2),
                            Forms\Components\Group::make([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('total')
                                            ->label('Total')
                                            ->content(fn(Get $get): HtmlString => new HtmlString(
                                                '<span class="text-2xl font-semibold">' . self::calculateTotal($get) . '</span>'
                                            )),
                                    ])
                            ]),
                            Forms\Components\Repeater::make('orderMenus')
                                ->hiddenLabel()
                                ->relationship()
                                ->columns(10)
                                ->columnSpanFull()
                                ->addActionLabel('Tambah Menu')
                                ->schema(static::getOrderMenuSchema()),
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
                    ->placeholder('-')
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
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    /**
     * Calculate total price from orderMenus
     */
    public static function calculateTotal(Get $get): string
    {
        $total = collect($get('orderMenus'))->sum('subtotal_price');

        return Numeric::rupiah($total, true);
    }

    public static function getOrderMenuSchema(): array
    {
        return [
            Forms\Components\Select::make('menu_id')
                ->relationship('menu', 'name')
                ->label('Menu')
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->columnSpan(function (?int $state): int {
                    if ($state) {
                        if (static::getMenuPrices($state)->count() > 1) {
                            return 5;
                        }
                    }

                    return 7;
                })
                ->afterStateUpdated(function (Set $set, Get $get, ?int $state): void {
                    if ($state) {
                        $menuPrices = static::getMenuPrices($state);

                        if ($menuPrices->count() === 1) {
                            $price = $menuPrices->first()->price;
                            $set('price', Numeric::rupiah($price));
                            $set('subtotal_price', $price * ($get('quantity') ?? 1));
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
                ->disableOptionWhen(function (int $value, Get $get): bool {
                    $currentPath = $get('__component.path');
                    $allItems = collect($get('../../orderMenus'))->filter();
                    $otherItems = $allItems->filter(fn($item, $key) => $key !== $currentPath);

                    $usedVariants = $otherItems
                        ->where('menu_id', $value)
                        ->pluck('variant_beverage')
                        ->filter()
                        ->map(fn($v) => is_object($v) ? $v->value : $v)
                        ->unique()
                        ->values();

                    $menuPrices = static::getMenuPrices($value);

                    if ($menuPrices->count() <= 1) {
                        return $otherItems->pluck('menu_id')->contains($value);
                    }

                    $menuVariants = $menuPrices
                        ->pluck('variant_beverage')
                        ->map(fn($v) => is_object($v) ? $v->value : $v)
                        ->unique();

                    return $menuVariants->diff($usedVariants)->isEmpty();
                }),
            Forms\Components\Select::make('variant_beverage')
                ->label('Varian')
                ->placeholder('-- Varian --')
                ->options(VariantBeverage::select())
                ->live()
                ->columnSpan(2)
                ->disableOptionWhen(function (string $value, Get $get): bool {
                    $currentPath = $get('__component.path');
                    $menuId = $get('menu_id');

                    if (!$menuId) return false;

                    $allItems = collect($get('../../orderMenus'))->filter();

                    return $allItems
                        ->filter(fn($item, $key): bool => $key !== $currentPath && $item['menu_id'] === $menuId)
                        ->contains('variant_beverage', $value);
                })
                ->visible(function (Get $get): bool {
                    if ($get('menu_id')) {
                        if (static::getMenuPrices($get('menu_id'))->count() > 1) {
                            return true;
                        }
                    }

                    return false;
                })
                ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                    if ($state) {
                        $menuPrices = static::getMenuPrices($get('menu_id'))
                            ->where('variant_beverage', $state)
                            ->first();

                        $set('price', Numeric::rupiah($menuPrices->price));
                        $set('subtotal_price', $menuPrices->price * ($get('quantity') ?? 1));
                    } else {
                        $set('price', 0);
                        $set('subtotal_price', 0);
                    }
                }),
            Forms\Components\TextInput::make('quantity')
                ->label('Jumlah')
                ->numeric()
                ->minValue(1)
                ->extraInputAttributes(['min' => 1])
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, Get $get, ?int $state): void {
                    if ($state) {
                        $price = str_replace('.', '', $get('price'));
                        $set('subtotal_price', $price * $state);
                    } else {
                        $set('subtotal_price', 0);
                    }
                }),
            Forms\Components\TextInput::make('price')
                ->label('Harga')
                ->disabled()
                ->prefix('Rp')
                ->default(0)
                ->columnSpan(2)
                ->afterStateHydrated(function (Get $get, Set $set, string $operation): void {
                    if ($operation === 'edit') {
                        $price = static::getMenuPrices($get('menu_id'))
                            ->where('variant_beverage', $get('variant_beverage'))
                            ->first();

                        if ($price) {
                            $set('price', Numeric::rupiah($price->price));
                        } else {
                            $set('price', 0);
                        }
                    }
                }),
            Forms\Components\Hidden::make('subtotal_price')
                ->stripCharacters('.')
                ->default(0)
                ->afterStateHydrated(function (Set $set, ?int $state): void {
                    if ($state) {
                        $set('subtotal_price', $state);
                    }
                })
        ];
    }

    public static function getMenuPrices(?int $menuId): Collection
    {
        return once(function () use ($menuId): Collection {
            return MenuPrice::where('menu_id', $menuId)->get();
        });
    }
}
