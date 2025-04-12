<?php

namespace App\Filament\Resources;

use App\Enums\MenuCategory;
use App\Enums\VariantBeverage;
use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
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
                            Forms\Components\TextInput::make('name')
                                ->label('Nama')
                                ->required()
                                ->maxLength(100),
                            Forms\Components\Select::make('category')
                                ->label('Kategori')
                                ->options(MenuCategory::select())
                                ->live()
                                ->required(),
                            Forms\Components\Checkbox::make('has_variant')
                                ->label('Memiliki Varian')
                                ->live()
                                ->helperText('Centang jika menu ini memiliki varian (Hot & Cold)')
                                ->visible(
                                    fn(Get $get): bool => $get('category') == MenuCategory::Beverage->value
                                )
                                ->columnSpanFull()
                                ->afterStateHydrated(function (?Menu $record, Set $set, string $operation): void {
                                    if ($operation === 'edit' && $record->prices->count() > 1) {
                                        $set('has_variant', true);
                                    }
                                }),
                            Forms\Components\Textarea::make('description')
                                ->label('Deskripsi')
                                ->hint('Opsional')
                                ->columnSpanFull(),
                        ]),
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\FileUpload::make('image')
                                ->label('Gambar')
                                ->image()
                                ->required()
                                ->columnSpanFull(),
                        ]),
                ])->columnSpan(2),
                Forms\Components\Group::make([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Toggle::make('availability')
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
                                                    'variant_beverage',
                                                    VariantBeverage::Hot
                                                )->price
                                            );

                                            $set(
                                                'price_cold',
                                                $prices->firstWhere(
                                                    'variant_beverage',
                                                    VariantBeverage::Cold
                                                )->price
                                            );
                                        } else {
                                            $set('price', $prices->first()->price);
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
                                    return $get('has_variant') && $get('category') == MenuCategory::Beverage->value;
                                }),
                        ])
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Gambar')
                    ->alignCenter()
                    ->visibleFrom('md'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->description(fn(Menu $record): ?string => $record->description)
                    ->searchable(),
                Tables\Columns\TextColumn::make('prices.price')
                    ->label('Harga')
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        if ($record->prices->count() > 1) {
                            $prices = $record->prices->implode(function ($price) {
                                return 'Rp ' . number_format($price->price, 0, ',', '.') .
                                    ' (' . $price->variant_beverage->name . ')';
                            }, '<br>');

                            return new HtmlString($prices);
                        }

                        return 'Rp ' . number_format($record->prices->first()->price, 0, ',', '.');
                    }),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(MenuCategory $state): string => match ($state->value) {
                        'food' => 'success',
                        'beverage' => 'gray',
                    })
                    ->formatStateUsing(fn(MenuCategory $state): string => $state->label())
                    ->visibleFrom('md'),
                Tables\Columns\IconColumn::make('availability')
                    ->label('Tersedia')
                    ->alignCenter()
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
}
