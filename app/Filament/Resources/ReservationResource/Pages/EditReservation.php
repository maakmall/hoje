<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Rules\ReservationCapacity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    public function form(Form $form): Form
    {
        return $form
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
                ])->columnSpan(1),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->placeholder('Tulis catatan tambahan di sini')
                    ])
            ]);
    }
}
