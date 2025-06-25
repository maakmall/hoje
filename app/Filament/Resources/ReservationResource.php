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
use App\Rules\ReservationCapacity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
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
                                        Forms\Components\TextInput::make('nama_pelanggan')
                                            ->label('Nama Pelanggan')
                                            ->required()
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('email_pelanggan')
                                            ->label('Email Pelanggan')
                                            ->required()
                                            ->email(),
                                        Forms\Components\TextInput::make('telepon_pelanggan')
                                            ->label('Telepon Pelanggan')
                                            ->tel()
                                            ->required(),
                                        Forms\Components\TextInput::make('jumlah_orang')
                                            ->label('Jumlah Orang')
                                            ->suffix('Orang')
                                            ->minValue(1)
                                            ->extraInputAttributes(['min' => 1])
                                            ->required()
                                            ->rules([new ReservationCapacity()])
                                            ->numeric()
                                            ->columnSpanFull(),
                                    ])
                            ])->columnSpan(2),
                            Forms\Components\Group::make([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('waktu')
                                            ->label('Tanggal')
                                            ->minDate(now())
                                            ->required(),
                                        Forms\Components\Select::make('id_lokasi')
                                            ->label('Lokasi')
                                            ->relationship('location', 'nama')
                                            ->required(),
                                    ])
                            ]),
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Textarea::make('catatan')
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
                                        ->content(fn(Get $get): ?string => $get('nama_pelanggan')),
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
            ->recordUrl(
                fn(Reservation $record): string => ReservationResource::getUrl('view', ['record' => $record])
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_pelanggan')
                    ->label('Pelanggan')
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('waktu')
                    ->label('Tanggal')
                    ->dateTime('j M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_orang')
                    ->label('Jumlah Orang')
                    ->suffix(' Orang')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(function (Reservation $record): string {
                        $total = $record->order->orderMenus->sum('subtotal_harga');
                        $amount = $record->order->payments
                            ->where('status', PaymentStatus::Paid)
                            ->sum('jumlah');

                        if ($amount == 0) {
                            return 'Belum Bayar';
                        }

                        return $amount !== $total
                            ? 'DP'
                            : 'Dibayar';
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->visible(function (Reservation $record): bool {
                        return $record->order->payments
                            ->where('status', PaymentStatus::Paid)
                            ->sum('jumlah') === 0;
                    }),
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->visible(function (Reservation $record): bool {
                        $total = $record->order->orderMenus->sum('subtotal_harga');
                        return $record->order->payments
                            ->where('status', PaymentStatus::Paid)
                            ->sum('jumlah') !== $total;
                    })
            ])
            ->filters([
                Tables\Filters\Filter::make('waktu')
                    ->label('Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal'),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (!filled($data['date'])) {
                            return null;
                        }

                        $date = Carbon::parse($data['date'])->translatedFormat('j M Y');

                        return $data['date'] ? 'Tanggal: ' . $date : null;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $query->when($data['date'], function (Builder $query) use ($data) {
                            $query->whereDate('waktu', $data['date']);
                        });

                        return $query;
                    }),
                Tables\Filters\Filter::make('status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'DP' => 'DP',
                                'Dibayar' => 'Dibayar',
                                'Belum Bayar' => 'Belum Bayar',
                            ]),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        return $data['status'] ? 'Status: ' . $data['status'] : null;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (!filled($data['status'])) {
                            return $query;
                        }

                        return $query->whereHas('order', function ($orderQuery) use ($data) {
                            $status = $data['status'];

                            return $orderQuery->whereRaw(match ($status) {
                                'Belum Bayar' => '
                                    (
                                        SELECT COALESCE(SUM(jumlah), 0)
                                        FROM pembayaran
                                        WHERE pembayaran.id_pemesanan = pemesanan.id
                                        AND pembayaran.status = ?
                                    ) = 0
                                ',
                                'DP' => '
                                    (
                                        SELECT COALESCE(SUM(jumlah), 0)
                                        FROM pembayaran
                                        WHERE pembayaran.id_pemesanan = pemesanan.id
                                        AND pembayaran.status = ?
                                    ) < (
                                        SELECT SUM(subtotal_harga)
                                        FROM pemesanan_menu
                                        WHERE pemesanan_menu.id_pemesanan = pemesanan.id
                                    )
                                    AND (
                                        SELECT COALESCE(SUM(jumlah), 0)
                                        FROM pembayaran
                                        WHERE pembayaran.id_pemesanan = pemesanan.id
                                        AND pembayaran.status = ?
                                    ) > 0
                                ',
                                'Dibayar' => '
                                    (
                                        SELECT COALESCE(SUM(jumlah), 0)
                                        FROM pembayaran
                                        WHERE pembayaran.id_pemesanan = pemesanan.id
                                        AND pembayaran.status = ?
                                    ) = (
                                        SELECT SUM(subtotal_harga)
                                        FROM pemesanan_menu
                                        WHERE pemesanan_menu.id_pemesanan = pemesanan.id
                                    )
                                ',
                            }, match ($status) {
                                'DP' => [PaymentStatus::Paid, PaymentStatus::Paid],
                                default => [PaymentStatus::Paid],
                            });
                        });
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
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }

    public static function getOrderMenuSchema(): array
    {
        return [
            Forms\Components\Select::make('id_menu')
                ->options(
                    fn(): Collection => Menu::available()->pluck('nama', 'id')
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
                ->live(debounce: 500)
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
