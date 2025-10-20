<?php

namespace App\Filament\Resources\EmployeeResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::getComponents());
    }

    public static function getComponents(): array
    {
        return [
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
                ->tel()
                ->maxLength(255),

            TextInput::make('minimum_advance_booking')
                ->label('Reserva mínima anticipada')
                ->numeric()
                ->default(0)
                ->suffix('minutos')
                ->helperText('Tiempo mínimo requerido antes de reservar (en minutos)'),
        ];
    }
}
