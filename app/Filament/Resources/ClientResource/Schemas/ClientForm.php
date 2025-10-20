<?php

namespace App\Filament\Resources\ClientResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClientForm
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
                ->email()
                ->maxLength(255),

            TextInput::make('phone_number')
                ->label('Número de teléfono')
                ->required()
                ->tel()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
        ];
    }
}
