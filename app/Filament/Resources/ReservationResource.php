<?php

namespace App\Filament\Resources;

use App\Enums\VariantBeverage;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Resources\ReservationResource\Pages;
use App\Helpers\Numeric;
use App\Models\Menu;
use App\Models\MenuPrice;
use App\Models\Reservation;
use App\Models\User;
use App\Rules\ReservationCapacity;
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

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $modelLabel = 'Reservasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Reservasi')
                        ->description('Lengkapi data reservasi')
                        ->icon('heroicon-m-clipboard-document-check')
                        ->columns(3)
                        ->schema([
                            Forms\Components\Group::make([
                                Forms\Components\Section::make()
                                    ->columns()
                                    ->schema([
                                        Forms\Components\TextInput::make('customer_name')
                                            ->label('Nama Pelanggan')
                                            ->required()
                                            ->columnSpanFull(),
                                        Forms\Components\Select::make('location_id')
                                            ->label('Lokasi')
                                            ->relationship('location', 'name')
                                            ->required(),
                                        Forms\Components\TextInput::make('number_of_people')
                                            ->label('Jumlah Orang')
                                            ->suffix('Orang')
                                            ->minValue(1)
                                            ->extraInputAttributes(['min' => 1])
                                            ->required()
                                            ->rules([new ReservationCapacity()])
                                            ->numeric(),
                                    ])
                            ])->columnSpan(2),
                            Forms\Components\Group::make([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('datetime')
                                            ->label('Tanggal')
                                            ->minDate(now())
                                            ->required(),
                                    ])
                            ]),
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Textarea::make('notes')
                                        ->label('Catatan')
                                        ->placeholder('Tulis catatan tambahan di sini')
                                ])
                        ]),
                    Forms\Components\Wizard\Step::make('Menu')
                        ->description('Pilih menu yang dipesan')
                        ->icon('heroicon-m-shopping-bag')
                        ->columns()
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Placeholder::make('total')
                                        ->label('Total')
                                        ->content(fn(Get $get): HtmlString => new HtmlString(
                                            '<span class="text-2xl font-semibold">' . OrderResource::calculateTotal($get) . '</span>'
                                        ))
                                ]),
                            Forms\Components\Repeater::make('orderMenus')
                                ->hiddenLabel()
                                ->columns(10)
                                ->columnSpanFull()
                                ->addActionLabel('Tambah Menu')
                                ->schema(self::getOrderMenuSchema()),
                        ]),
                    Forms\Components\Wizard\Step::make('Pembayaran')
                        ->description('Pilih metode pembayaran')
                        ->icon('heroicon-m-credit-card')
                        ->columns(3)
                        ->schema([
                            Forms\Components\Section::make()
                                ->columnSpan(2)
                                ->schema([
                                    Forms\Components\Placeholder::make('customer')
                                        ->label('Pelanggan')
                                        ->content(fn(Get $get): ?string => $get('customer_name')),
                                    Forms\Components\Radio::make('payment_method')
                                        ->label('Metode Pembayaran')
                                        ->required()
                                        ->options(PaymentMethod::select())
                                        ->inline()
                                        ->inlineLabel(false),
                                    Forms\Components\Placeholder::make('note')
                                        ->label('Catatan')
                                        ->content('Jumlah tertera adalah DP 50% dari total harga'),
                                    // Forms\Components\Radio::make('payment_type')
                                    //     ->label('Jenis Pembayaran')
                                    //     ->required()
                                    //     ->options([
                                    //         'dp' => 'DP 50%',
                                    //         'full' => 'Bayar Penuh',
                                    //     ])
                                    //     ->inline()
                                    //     ->live()
                                    //     ->default('full')
                                    //     ->inlineLabel(false)
                                    //     ->afterStateUpdated(function (Get $get): void {
                                    //         OrderResource::calculateTotal($get);
                                    //     })
                                ]),
                            Forms\Components\Section::make()
                                ->columnSpan(1)
                                ->schema([
                                    Forms\Components\Placeholder::make('menus')
                                        ->hiddenLabel()
                                        ->content(function (Get $get): HtmlString {
                                            $orderMenus = $get('orderMenus') ?? [];
                                            $menuIds = collect($orderMenus)
                                                ->pluck('menu_id')
                                                ->unique()
                                                ->all();

                                            $menus = Menu::whereIn('id', $menuIds)->get()->keyBy('id');
                                            $rows = [];

                                            foreach ($orderMenus as $item) {
                                                $menu = $menus[$item['menu_id']] ?? null;
                                                $menuName = $menu?->name ?? 'Unknown Menu';
                                                $variant = $item['variant_beverage'] ? ' (' . VariantBeverage::from($item['variant_beverage'])->name . ')' : '';
                                                $qty = $item['quantity'];
                                                $price = Numeric::rupiah(str_replace('.', '', $item['price']));
                                                $subtotal = Numeric::rupiah($item['subtotal_price']);

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
                                            '<span class="text-2xl font-semibold">' . OrderResource::calculateTotal($get, true) . '</span>'
                                        ))
                                ])
                        ])
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('datetime')
                    ->label('Tanggal')
                    ->dateTime('j M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('number_of_people')
                    ->label('Jumlah Orang')
                    ->suffix(' Orang')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(function (Reservation $record): string {
                        $total = $record->order->orderMenus->sum('subtotal_price');
                        $amount = $record->order->payments
                            ->where('status', PaymentStatus::Paid)
                            ->sum('amount');

                        if ($amount == 0) {
                            return 'Belum Bayar';
                        }

                        return $amount !== $total
                            ? 'DP'
                            : 'Dibayar';
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location_id')
                    ->relationship('location', 'name')
                    ->label('Lokasi'),
                Tables\Filters\Filter::make('datetime')
                    ->label('Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $query->when($data['date'], function (Builder $query) use ($data) {
                            $query->whereDate('datetime', $data['date']);
                        });

                        return $query;
                    }),
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
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'view' => Pages\ViewReservation::route('/{record}'),
        ];
    }

    public static function getOrderMenuSchema(): array
    {
        return [
            Forms\Components\Select::make('menu_id')
                ->options(
                    fn(): Collection => Menu::pluck('name', 'id')
                )
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
