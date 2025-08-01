<?php

namespace App\Filament\Resources;

use App\Enums\MenuCategory;
use App\Enums\VariantBeverage;
use App\Filament\Resources\MenuResource\Pages;
use App\Filament\Resources\MenuResource\Widgets\MenuOverview;
use App\Helpers\Numeric;
use App\Models\Menu;
use App\Models\MenuPrice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Data';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\Section::make()
                        ->columns()
                        ->schema([
                            Forms\Components\TextInput::make('nama')
                                ->label('Nama')
                                ->required()
                                ->maxLength(100),
                            Forms\Components\Select::make('kategori')
                                ->label('Kategori')
                                ->options(MenuCategory::select())
                                ->live()
                                ->required(),
                            Forms\Components\Checkbox::make('has_variant')
                                ->label('Memiliki Varian')
                                ->live()
                                ->helperText('Centang jika menu ini memiliki varian (Hot & Cold)')
                                ->visible(
                                    fn(Get $get): bool => $get('kategori') == MenuCategory::Beverage->value
                                )
                                ->columnSpanFull()
                                ->afterStateHydrated(function (?Menu $record, Set $set, string $operation): void {
                                    if ($operation === 'edit' && $record->prices->count() > 1) {
                                        $set('has_variant', true);
                                    }
                                }),
                            Forms\Components\Textarea::make('deskripsi')
                                ->label('Deskripsi')
                                ->hint('Opsional')
                                ->columnSpanFull(),
                        ]),
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\FileUpload::make('gambar')
                                ->label('Gambar')
                                ->image()
                                ->disk('r2')
                                ->required()
                                ->columnSpanFull(),
                        ]),
                ])->columnSpan(2),
                Forms\Components\Group::make([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Toggle::make('tersedia')
                                ->label('Tersedia')
                                ->required()
                                ->inline(false),
                        ]),
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('price')
                                ->label(function (Get $get): string {
                                    $isBeverage = $get('category') == MenuCategory::Beverage->value;
                                    return $get('has_variant') && $isBeverage
                                        ? 'Harga (Hot)'
                                        : 'Harga';
                                })
                                ->required()
                                ->numeric()
                                ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                                ->stripCharacters('.')
                                ->prefix('Rp')
                                ->afterStateHydrated(function (Set $set, ?Menu $record, string $operation): void {
                                    if ($operation === 'edit') {
                                        $prices = $record->prices;
                                        if ($record->prices->count() > 1) {
                                            $set(
                                                'price',
                                                $prices->firstWhere(
                                                    'variasi_minuman',
                                                    VariantBeverage::Hot
                                                )->harga
                                            );

                                            $set(
                                                'price_cold',
                                                $prices->firstWhere(
                                                    'variasi_minuman',
                                                    VariantBeverage::Cold
                                                )->harga
                                            );
                                        } else {
                                            $set('price', $prices->first()->harga);
                                        }
                                    }
                                }),
                            Forms\Components\TextInput::make('price_cold')
                                ->label('Harga (Cold)')
                                ->required()
                                ->numeric()
                                ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                                ->stripCharacters('.')
                                ->prefix('Rp')
                                ->visible(function (Get $get): bool {
                                    return $get('has_variant') && $get('kategori') == MenuCategory::Beverage->value;
                                }),
                        ])
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ImageColumn::make('gambar')
                        ->label('Gambar')
                        ->disk('r2')
                        ->defaultImageUrl(url('/images/logo-text.png'))
                        ->height('100%')
                        ->width('100%')
                        ->alignCenter(),
                    Tables\Columns\TextColumn::make('nama')
                        ->label('Nama')
                        ->weight(FontWeight::Medium)
                        ->description(fn(Menu $record): string => $record->deskripsi ?? '-')
                        ->wrap()
                        ->searchable(),
                    Tables\Columns\TextColumn::make('prices.harga')
                        ->label('Harga')
                        ->formatStateUsing(function (Menu $record): string|HtmlString {
                            if ($record->prices->count() > 1) {
                                $prices = $record->prices->implode(function (MenuPrice $price) {
                                    return Numeric::rupiah($price->harga, true) .
                                        ' (' . $price->variasi_minuman->name . ')';
                                }, '<br>');

                                return new HtmlString($prices);
                            }

                            return Numeric::rupiah($record->prices->first()->harga, true);
                        }),
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('kategori')
                            ->label('Kategori')
                            ->badge()
                            ->color(fn(MenuCategory $state): string => match ($state->value) {
                                'food' => 'success',
                                'beverage' => 'gray',
                            })
                            ->formatStateUsing(fn(MenuCategory $state): string => $state->label()),
                        Tables\Columns\IconColumn::make('tersedia')
                            ->label('Tersedia')
                            ->alignCenter()
                            ->tooltip(
                                fn(Menu $record): string => $record->tersedia ? 'Tersedia' : 'Tidak Tersedia'
                            )
                            ->boolean(),
                    ])
                ])->space(3),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('tersedia')
                    ->label('Tersedia')
                    ->placeholder('Semua')
                    ->trueLabel('Tersedia')
                    ->falseLabel('Tidak Tersedia'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            MenuOverview::class,
        ];
    }
}
