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
                ])->columnSpan(1),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->placeholder('Tulis catatan tambahan di sini')
                    ])
            ]);
    }
}
