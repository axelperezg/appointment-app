<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                TextColumn::make('duration')
                    ->label('Duración')
                    ->suffix(' min'),

                TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD'),

                ColorColumn::make('color')
                    ->label('Color'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
