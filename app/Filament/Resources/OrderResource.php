<?php

namespace App\Filament\Resources;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\VariantBeverage;
use App\Filament\Resources\OrderResource\Pages;
use App\Helpers\Numeric;
use App\Models\Menu;
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
                                        Forms\Components\TextInput::make('id')
                                            ->label('ID Pesanan')
                                            ->disabled()
                                            ->dehydrated()
                                            ->afterStateHydrated(function (Set $set): void {
                                                $set('id', Numeric::generateId('orders'));
                                            }),
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
                                ->columns(10)
                                ->columnSpanFull()
                                ->addActionLabel('Tambah Menu')
                                ->schema(static::getOrderMenuSchema()),
                        ]),
                    Forms\Components\Wizard\Step::make('Pembayaran')
                        ->description('Pilih metode pembayaran')
                        ->icon('heroicon-m-credit-card')
                        ->columns(3)
                        ->schema([
                            Forms\Components\Section::make()
                                ->columnSpan(2)
                                ->schema([
                                    Forms\Components\Placeholder::make('id_placeholder')
                                        ->label('ID Pesanan')
                                        ->content(fn(Get $get): ?string => $get('id')),
                                    Forms\Components\Radio::make('payment_method')
                                        ->label('Metode Pembayaran')
                                        ->required()
                                        ->options(PaymentMethod::select())
                                        ->inline()
                                        ->inlineLabel(false)
                                ]),
                            Forms\Components\Section::make()
                                ->columnSpan(1)
                                ->schema([
                                    Forms\Components\Placeholder::make('menus')
                                        ->hiddenLabel()
                                        ->content(function (Get $get): HtmlString {
                                            $orderMenus = $get('orderMenus') ?? [];
                                            $menuIds = collect($orderMenus)
                                                ->pluck('id_menu')
                                                ->unique()
                                                ->all();

                                            $menus = Menu::whereIn('id', $menuIds)->get()->keyBy('id');
                                            $rows = [];

                                            foreach ($orderMenus as $item) {
                                                $menu = $menus[$item['id_menu']] ?? null;
                                                $menuName = $menu?->nama ?? 'Unknown Menu';
                                                $variant = $item['variasi_minuman'] ? ' (' . VariantBeverage::from($item['variasi_minuman'])->name . ')' : '';
                                                $qty = $item['jumlah'];
                                                $price = Numeric::rupiah(str_replace('.', '', $item['price']));
                                                $subtotal = Numeric::rupiah($item['subtotal_harga']);

                                                $rows[] = "
                                                    {$menuName}{$variant}
                                                    <div class='flex justify-between'>
                                                        <div>{$qty} @{$price}</div>
                                                        <div>{$subtotal}</div>
                                                    </div>
                                                ";
                                            }

                                            return new HtmlString(implode('<br>', $rows));
                                        }),
                                    Forms\Components\Placeholder::make('total')
                                        ->label('Total')
                                        ->content(fn(Get $get): HtmlString => new HtmlString(
                                            '<span class="text-2xl font-semibold">' . self::calculateTotal($get) . '</span>'
                                        ))
                                ])
                        ])
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): void {
                $query->withSum('orderMenus', 'subtotal_harga');
            })
            ->defaultSort('waktu', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('waktu')
                    ->label('Tanggal')
                    ->dateTime('j M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Dibayar' => 'success',
                        'Pending' => 'warning',
                        'Belum Bayar' => 'danger',
                    })
                    ->getStateUsing(function (Order $record): string {
                        $total = $record->orderMenus->sum('subtotal_harga');
                        $payment = $record->payments;
                        $amount = $payment->where('status', PaymentStatus::Paid)->sum('jumlah');

                        if ($amount == 0) {
                            return 'Belum Bayar';
                        }

                        return $amount !== $total
                            ? 'Pending'
                            : 'Dibayar';
                    }),
                Tables\Columns\TextColumn::make('order_menus_sum_subtotal_harga')
                    ->label('Total')
                    ->prefix('Rp ')
                    ->numeric(thousandsSeparator: '.')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Dibayar' => 'Dibayar',
                                'Pending' => 'Pending',
                                'Gagal' => 'Gagal'
                            ])
                            ->placeholder('Semua')
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        return $data['status'] ? 'Status: ' . $data['status'] : null;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['status']) {
                            return $query;
                        }

                        $paidStatus = PaymentStatus::Paid;

                        return $query->where(function (Builder $q) use ($data, $paidStatus) {
                            if ($data['status'] === 'Gagal') {
                                // Order yang tidak punya payment sama sekali
                                $q->whereDoesntHave('payments');
                            }

                            if ($data['status'] === 'Pending') {
                                $q->whereHas('payments') // Harus punya payment
                                    ->whereRaw('
                                    (
                                        SELECT COALESCE(SUM(jumlah), 0)
                                        FROM pembayaran p
                                        WHERE p.id_pemesanan = pemesanan.id AND p.status = ?
                                    ) <
                                    (
                                        SELECT SUM(subtotal_harga)
                                        FROM pemesanan_menu om
                                        WHERE om.id_pemesanan = pemesanan.id
                                    )
                                    ', [PaymentStatus::Paid]);
                            }

                            if ($data['status'] === 'Dibayar') {
                                $q->whereHas('payments', function ($p) use ($paidStatus) {
                                    $p->selectRaw('id_pemesanan, SUM(jumlah) as paid_amount')
                                        ->where('status', $paidStatus)
                                        ->groupBy('id_pemesanan');
                                })->whereRaw('
                                    (SELECT SUM(CASE WHEN status = ? THEN jumlah ELSE 0 END)
                                    FROM payments p WHERE p.id_pemesanan = pemesanan.id)
                                    = 
                                    (SELECT SUM(subtotal_harga)
                                    FROM pemesanan_menu om WHERE om.id_pemesanan = pemesanan.id)
                                ', [$paidStatus]);
                            }
                        });
                    })
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'payment' => Pages\ViewOrderPayment::route('/{record}/payment')
        ];
    }

    /**
     * Calculate total price from orderMenus
     */
    public static function calculateTotal(Get $get, bool $isDownPayment = false): string
    {
        $total = collect($get('orderMenus'))->sum('subtotal_harga');

        // if ($get('payment_type') == 'dp') {
        //     $total -= $total * 50 / 100;
        // }
        if ($isDownPayment) {
            $total -= $total * 50 / 100;
        }

        return Numeric::rupiah($total, true);
    }

    public static function getOrderMenuSchema(): array
    {
        return [
            Forms\Components\Select::make('id_menu')
                ->options(fn(): Collection => Menu::available()->pluck('nama', 'id'))
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
                            $price = $menuPrices->first()->harga;
                            $set('price', Numeric::rupiah($price));
                            $set('subtotal_harga', $price * ($get('jumlah') ?? 1));
                        } else {
                            $set('price', 0);
                            $set('subtotal_harga', 0);
                        }

                        $set('jumlah', 1);
                    } else {
                        $set('price', 0);
                        $set('jumlah', null);
                        $set('subtotal_harga', 0);
                    }
                })
                ->disableOptionWhen(function (int $value, Get $get): bool {
                    $currentPath = $get('__component.path');
                    $allItems = collect($get('../../orderMenus'))->filter();
                    $otherItems = $allItems->filter(fn($item, $key) => $key !== $currentPath);

                    $usedVariants = $otherItems
                        ->where('id_menu', $value)
                        ->pluck('variasi_minuman')
                        ->filter()
                        ->map(fn($v) => is_object($v) ? $v->value : $v)
                        ->unique()
                        ->values();

                    $menuPrices = static::getMenuPrices($value);

                    if ($menuPrices->count() <= 1) {
                        return $otherItems->pluck('id_menu')->contains($value);
                    }

                    $menuVariants = $menuPrices
                        ->pluck('variasi_minuman')
                        ->map(fn($v) => is_object($v) ? $v->value : $v)
                        ->unique();

                    return $menuVariants->diff($usedVariants)->isEmpty();
                }),
            Forms\Components\Select::make('variasi_minuman')
                ->label('Varian')
                ->placeholder('-- Varian --')
                ->options(VariantBeverage::select())
                ->live()
                ->columnSpan(2)
                ->disableOptionWhen(function (string $value, Get $get): bool {
                    $currentPath = $get('__component.path');
                    $menuId = $get('id_menu');

                    if (!$menuId) return false;

                    $allItems = collect($get('../../orderMenus'))->filter();

                    return $allItems
                        ->filter(fn($item, $key): bool => $key !== $currentPath && $item['id_menu'] === $menuId)
                        ->contains('variasi_minuman', $value);
                })
                ->visible(function (Get $get): bool {
                    if ($get('id_menu')) {
                        if (static::getMenuPrices($get('id_menu'))->count() > 1) {
                            return true;
                        }
                    }

                    return false;
                })
                ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                    if ($state) {
                        $menuPrices = static::getMenuPrices($get('id_menu'))
                            ->where('variasi_minuman', $state)
                            ->first();

                        $set('price', Numeric::rupiah($menuPrices->harga));
                        $set('subtotal_harga', $menuPrices->harga * ($get('jumlah') ?? 1));
                    } else {
                        $set('price', 0);
                        $set('subtotal_harga', 0);
                    }
                }),
            Forms\Components\TextInput::make('jumlah')
                ->label('Jumlah')
                ->numeric()
                ->minValue(1)
                ->extraInputAttributes(['min' => 1])
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, Get $get, ?int $state): void {
                    if ($state) {
                        $price = str_replace('.', '', $get('price'));
                        $set('subtotal_harga', $price * $state);
                    } else {
                        $set('subtotal_harga', 0);
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
                        $price = static::getMenuPrices($get('id_menu'))
                            ->where('variasi_minuman', $get('variasi_minuman'))
                            ->first();

                        if ($price) {
                            $set('price', Numeric::rupiah($price->harga));
                        } else {
                            $set('price', 0);
                        }
                    }
                }),
            Forms\Components\Hidden::make('subtotal_harga')
                ->stripCharacters('.')
                ->default(0)
                ->afterStateHydrated(function (Set $set, ?int $state): void {
                    if ($state) {
                        $set('subtotal_harga', $state);
                    }
                })
        ];
    }

    public static function getMenuPrices(?int $menuId): Collection
    {
        return once(function () use ($menuId): Collection {
            return MenuPrice::where('id_menu', $menuId)->get();
        });
    }
}
