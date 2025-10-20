<?php

namespace App\Filament\Resources\ServiceResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ServiceForm
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

            TextInput::make('duration')
                ->label('Duración')
                ->required()
                ->numeric()
                ->suffix('minutos')
                ->helperText('Duración en minutos'),

            TextInput::make('price')
                ->label('Precio')
                ->required()
                ->numeric()
                ->prefix('$')
                ->default(0),

            TextInput::make('color')
                ->label('Color')
                ->required()
                ->maxLength(7)
                ->default('#FF6B6B')
                ->prefix('#')
                ->helperText('Código hex del color (ej: FF6B6B)'),
        ];
    }
}
