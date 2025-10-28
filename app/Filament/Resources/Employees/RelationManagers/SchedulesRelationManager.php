<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    protected static ?string $title = 'Horarios';

    protected static ?string $modelLabel = 'horario';

    protected static ?string $pluralModelLabel = 'horarios';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('start_time')
                            ->label('Hora de inicio')
                            ->options(self::getTimeOptions())
                            ->nullable()
                            ->searchable()
                            ->placeholder('Selecciona una hora')
                            ->helperText('Deja vacío si el empleado no trabaja este día'),

                        Select::make('end_time')
                            ->label('Hora de fin')
                            ->options(self::getTimeOptions())
                            ->nullable()
                            ->searchable()
                            ->placeholder('Selecciona una hora')
                            ->helperText('Deja vacío si el empleado no trabaja este día'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('day')
            ->columns([
                TextColumn::make('day')
                    ->label('Día')
                    ->formatStateUsing(fn ($state) => self::getDayName($state))
                    ->badge()
                    ->color('info'),

                TextColumn::make('start_time')
                    ->label('Hora de inicio')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('H:i') : 'No configurado')
                    ->placeholder('No configurado'),

                TextColumn::make('end_time')
                    ->label('Hora de fin')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('H:i') : 'No configurado')
                    ->placeholder('No configurado'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // No create action - records are auto-created
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->modalHeading(fn ($record) => 'Editar horario - ' . self::getDayName($record->day))
                    ->modalSubmitActionLabel('Guardar'),
                
                Action::make('clear')
                    ->label('Limpiar horario')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => 'Limpiar horario - ' . self::getDayName($record->day))
                    ->modalDescription('¿Estás seguro de que deseas eliminar el horario de este día? El empleado no trabajará este día.')
                    ->modalSubmitActionLabel('Sí, limpiar horario')
                    ->modalCancelActionLabel('Cancelar')
                    ->action(function ($record) {
                        $record->update([
                            'start_time' => null,
                            'end_time' => null,
                        ]);
                        
                        Notification::make()
                            ->title('Horario limpiado')
                            ->success()
                            ->body('El horario para ' . self::getDayName($record->day) . ' ha sido eliminado.')
                            ->send();
                    })
                    ->visible(fn ($record) => $record->hasSchedule()),
            ])
            ->defaultSort('day', 'asc');
    }

    protected static function getDayName(int $day): string
    {
        return match ($day) {
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
            default => 'Desconocido',
        };
    }

    protected static function getTimeOptions(): array
    {
        $options = [];
        
        // Generate time slots from 6:00 AM to 11:00 PM in 30-minute intervals
        for ($hour = 6; $hour <= 23; $hour++) {
            foreach ([0, 30] as $minute) {
                $time = sprintf('%02d:%02d:00', $hour, $minute);
                $displayTime = sprintf('%02d:%02d', $hour, $minute);
                $options[$time] = $displayTime;
            }
        }
        
        return $options;
    }
}
