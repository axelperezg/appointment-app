<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages\ManageServices;
use App\Models\Service;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Servicios';

    protected static ?string $modelLabel = 'servicio';

    protected static ?string $pluralModelLabel = 'servicios';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
            ]);
    }

    public static function table(Table $table): Table
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
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageServices::route('/'),
        ];
    }
}
