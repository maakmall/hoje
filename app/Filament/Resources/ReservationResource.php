<?php

namespace App\Filament\Resources;

use App\Enums\VariantBeverage;
use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Resources\ReservationResource\RelationManagers;
use App\Helpers\Numeric;
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
                        ->columns(3)
                        ->schema([
                            Forms\Components\Group::make([
                                Forms\Components\Section::make()
                                    ->columns()
                                    ->schema([
                                        Forms\Components\Select::make('user_id')
                                            ->label('Pelanggan')
                                            ->relationship('user', 'name')
                                            ->columnSpanFull()
                                            ->getOptionLabelFromRecordUsing(
                                                fn(User $record): string => "{$record->id} - {$record->name}"
                                            )
                                            ->searchable(['name', 'id'])
                                            ->preload(),
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
                                // ->relationship()
                                ->columns(10)
                                ->columnSpanFull()
                                ->addActionLabel('Tambah Menu')
                                ->schema(self::getOrderMenuSchema()),
                        ]),
                    Forms\Components\Wizard\Step::make('Pembayaran')
                        ->icon('heroicon-m-credit-card')
                        ->schema([])
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pelanggan')
                    ->placeholder('Tanpa User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('datetime')
                    ->label('Tanggal')
                    ->dateTime('j M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Lokasi'),
                Tables\Columns\TextColumn::make('number_of_people')
                    ->label('Jumlah Orang')
                    ->suffix(' Orang')
                    ->numeric()
                    ->sortable(),
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }

    public static function getOrderMenuSchema(): array
    {
        return [
            Forms\Components\Select::make('menu_id')
                // ->relationship('menu', 'name')
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
                ->disableOptionWhen(function ($value, $label, Get $get) {
                    $selectedMenuIds = collect($get('../../orderMenus'))
                        ->pluck('menu_id')
                        ->filter()
                        ->toArray();

                    $currentIndex = $get('__index');
                    if ($currentIndex !== null) {
                        $currentMenuId = data_get($get("../../orderMenus.{$currentIndex}"), 'menu_id');
                        $selectedMenuIds = array_filter($selectedMenuIds, fn($id) => $id != $currentMenuId);
                    }

                    return in_array($value, $selectedMenuIds);
                }),
            Forms\Components\Select::make('variant_beverage')
                ->label('Varian')
                ->placeholder('-- Varian --')
                ->options(VariantBeverage::select())
                ->live()
                ->columnSpan(2)
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
}
