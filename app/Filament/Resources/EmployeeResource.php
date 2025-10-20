<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages\ManageEmployees;
use App\Models\Employee;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Empleados';

    protected static ?string $modelLabel = 'empleado';

    protected static ?string $pluralModelLabel = 'empleados';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(),

                TextColumn::make('phone_number')
                    ->label('Número de teléfono')
                    ->searchable(),

                TextColumn::make('minimum_advance_booking')
                    ->label('Reserva mín. anticipada')
                    ->suffix(' min'),
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
            'index' => ManageEmployees::route('/'),
        ];
    }
}
