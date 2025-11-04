<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AppointmentCalendarWidget;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class Calendar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $navigationLabel = 'Calendario';

    protected static ?string $title = 'Calendario';

    protected string $view = 'filament.pages.calendar';

    public ?string $prefilledDate = null;
    public ?string $prefilledStartTime = null;
    public ?int $prefilledEmployeeId = null;

    protected $listeners = [
        'open-create-modal' => 'openCreateModal',
    ];

    public function openCreateModal(array $data): void
    {
        $this->prefilledDate = $data['date'] ?? null;
        $this->prefilledStartTime = $data['start_time'] ?? null;
        
        // Open the create action modal
        $this->mountAction('create');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AppointmentCalendarWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Crear cita')
                ->icon('heroicon-o-plus')
                ->modalHeading('Crear nueva reservación')
                ->modalSubmitActionLabel('Crear reservación')
                ->modalCancelActionLabel('Cerrar')
                ->closeModalByClickingAway(false)
                ->schema(function (Schema $schema): Schema {
                    return $schema->components([
                        Section::make()
                            ->columnSpanFull()
                            ->columns(2)
                            ->schema([
                                Select::make('service_id')
                                    ->label('Servicio')
                                    ->options(Service::has('employees')->get()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->placeholder('Escoje un servicio...')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        // Reset employee_id when service changes
                                        $set('employee_id', null);
                                        
                                        // Recalculate end_time if start_time is already set
                                        if ($state && $get('start_time')) {
                                            $service = Service::find($state);
                                            if ($service && $service->duration) {
                                                $startTime = Carbon::parse($get('start_time'));
                                                $endTime = $startTime->copy()->addMinutes($service->duration);
                                                $set('end_time', $endTime->format('H:i:00'));
                                            }
                                        } else {
                                            $set('end_time', null);
                                        }
                                    }),

                                Select::make('employee_id')
                                    ->label('Profesional')
                                    ->options(function (callable $get) {
                                        $serviceId = $get('service_id');
                                        if (!$serviceId) {
                                            return [];
                                        }
                                        
                                        $service = Service::find($serviceId);
                                        if (!$service) {
                                            return [];
                                        }
                                        
                                        return $service->employees->pluck('name', 'id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->placeholder('Selecciona un profesional')
                                    ->default(fn () => $this->prefilledEmployeeId)
                                    ->disabled(fn (callable $get) => !$get('service_id'))
                                    ->reactive(),

                                DatePicker::make('date')
                                    ->columnSpanFull()
                                    ->label('Fecha')
                                    ->required()
                                    ->default(fn () => $this->prefilledDate ?? now()->format('Y-m-d'))
                                    ->native(false)
                                    ->displayFormat('Y-m-d'),

                                Select::make('start_time')
                                    ->label('Hora Inicio')
                                    ->options(self::getTimeOptions())
                                    ->required()
                                    ->searchable()
                                    ->placeholder('Selecciona una hora')
                                    ->default(fn () => $this->prefilledStartTime)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        if ($state && $get('service_id')) {
                                            $service = Service::find($get('service_id'));
                                            if ($service && $service->duration) {
                                                $startTime = Carbon::parse($state);
                                                $endTime = $startTime->copy()->addMinutes($service->duration);
                                                $set('end_time', $endTime->format('H:i:00'));
                                            }
                                        }
                                    }),

                                Select::make('end_time')
                                    ->label('Hora Fin')
                                    ->options(self::getTimeOptions())
                                    ->required()
                                    ->searchable()
                                    ->placeholder('Se calculará automáticamente')
                                    ->reactive(),

                                Textarea::make('note')
                                    ->label('Nota')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Select::make('client_id')
                                    ->columnSpanFull()
                                    ->label('Cliente')
                                    ->options(Client::query()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Selecciona un cliente')
                                    ->createOptionForm([
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
                                                    ->email()
                                                    ->maxLength(255),

                                                TextInput::make('phone_number')
                                                    ->label('Número de teléfono')
                                                    ->required()
                                                    ->tel()
                                                    ->maxLength(255),
                                            ]),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        return Client::create($data)->id;
                                    }),
                            ]),
                    ]);
                })
                ->action(function (array $data): void {
                    $date = Carbon::parse($data['date']);
                    $startTime = Carbon::parse($data['start_time']);
                    $endTime = Carbon::parse($data['end_time']);

                    $startsAt = $date->copy()->setTimeFromTimeString($startTime->format('H:i:s'));
                    $endsAt = $date->copy()->setTimeFromTimeString($endTime->format('H:i:s'));

                    // Validate time overlap with existing appointments for the same employee
                    $overlappingAppointment = Appointment::query()
                        ->where('employee_id', $data['employee_id'])
                        ->overlapping($startsAt, $endsAt)
                        ->first();

                    if ($overlappingAppointment) {
                        Notification::make()
                            ->title('Error al crear reservación')
                            ->danger()
                            ->body('La cita se superpone con otra o la hora ya está ocupada. Por favor, selecciona un horario diferente.')
                            ->send();
                        
                        // Halt the action to prevent modal from closing
                        $this->halt();
                    }

                    Appointment::create([
                        'client_id' => $data['client_id'],
                        'service_id' => $data['service_id'],
                        'employee_id' => $data['employee_id'],
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                        'note' => $data['note'] ?? null,
                    ]);

                    Notification::make()
                        ->title('Reservación creada')
                        ->success()
                        ->body('La reservación se ha creado exitosamente.')
                        ->send();

                    $this->dispatch('refreshCalendar');
                    
                    // Reset prefilled data after successful creation
                    $this->prefilledDate = null;
                    $this->prefilledStartTime = null;
                    $this->prefilledEmployeeId = null;
                }),
        ];
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
