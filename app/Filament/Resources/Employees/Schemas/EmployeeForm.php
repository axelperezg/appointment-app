<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Correo electrónico')
                        ->required()
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('phone_number')
                        ->label('Número de teléfono')
                        ->maxLength(255),

                    TextInput::make('minimum_advance_booking')
                        ->label('Reserva mínima anticipada')
                        ->numeric()
                        ->default(0)
                        ->suffix('minutos')
                        ->helperText('Tiempo mínimo requerido antes de reservar (en minutos)'),
                ]),
        ]);
    }
}
