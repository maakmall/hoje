<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Resources\ReservationResource\RelationManagers;
use App\Models\Reservation;
use App\Rules\ReservationCapacity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $modelLabel = 'Reservasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Pelanggan')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\DateTimePicker::make('datetime')
                    ->label('Tanggal dan Waktu')
                    ->required(),
                Forms\Components\Select::make('location_id')
                    ->label('Lokasi')
                    ->relationship('location', 'name')
                    ->required(),
                Forms\Components\TextInput::make('number_of_people')
                    ->label('Jumlah Orang')
                    ->suffix('Orang')
                    ->minValue(1)
                    ->required()
                    ->rules([new ReservationCapacity()])
                    ->numeric(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->placeholder('Tulis catatan tambahan di sini')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('datetime')
                    ->label('Tanggal dan Waktu')
                    ->dateTime('d F Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->numeric(),
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
}
