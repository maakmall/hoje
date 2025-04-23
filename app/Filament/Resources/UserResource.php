<?php

namespace App\Filament\Resources;

use App\Enums\Role;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn(string $operation): bool => $operation == 'create')
                    ->confirmed()
                    ->maxLength(255)
                    ->dehydrated(fn($state): bool => filled($state)),
                Forms\Components\TextInput::make('password_confirmation')
                    ->label('Konfirmasi Password')
                    ->password()
                    ->required(fn(string $operation): bool => $operation == 'create')
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('No. Telepon')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->required()
                    ->options(Role::select())
                    ->enum(Role::class),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->tooltip('Salin')
                    ->copyable()
                    ->copyMessage('Alamat email disalin')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('phone')
                    ->label('No. Telepon')
                    ->searchable()
                    ->tooltip('Salin')
                    ->copyable()
                    ->copyMessage('No. Telepon disalin')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn(Role $state): string => $state->name),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options(Role::select())
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
