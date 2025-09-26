<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PreoperationalInspectionResource\Pages;
use App\Models\PreoperationalInspection;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Set;
use Filament\Forms\Components\Select;
use Barryvdh\DomPDF\Facade\Pdf;



class PreoperationalInspectionResource extends Resource
{
    protected static ?string $model = PreoperationalInspection::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Preoperacionales';
    protected static ?string $modelLabel = 'Inspección Preoperacional';
    protected static ?string $navigationGroup = 'Gestión de Órdenes';

    public static function form(Form $form): Form
    {
        $options = ['B' => 'Bueno', 'M' => 'Malo', 'N/A' => 'No Aplica'];
        return $form
            ->schema([
                Section::make('Información General')
                    ->schema([
                        Select::make('user_id')
                            ->label('Nombre del Conductor')
                            ->relationship(
                                'user', 
                                'name', 
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->whereHas('roles', fn ($q) => $q->where('name', 'tecnico'))
                                    ->where('is_active', true)
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if (blank($state)) {
                                    $set('licencia_conductor', null);
                                    $set('placa_vehiculo', null);
                                    $set('marca_vehiculo', null);
                                    $set('modelo_vehiculo', null);
                                    $set('tarjeta_propiedad', null);
                                    $set('fecha_tecnomecanica', null);
                                    $set('fecha_soat', null);
                                    $set('mantenimiento_preventivo_taller', null);
                                    $set('fecha_mantenimiento', null);
                                    $set('fecha_ultimo_aceite', null);

                                    return;
                                }
                                
                                $user = User::with('vehicle')->find($state);
                                if (!$user) {
                                    return;
                                }
                                
                                $vehicle = $user->vehicle;

                                // Rellena todos los campos con la información encontrada.
                                $set('licencia_conductor', $user->licencia_conduccion);
                                $set('placa_vehiculo', $vehicle?->placa);
                                $set('modelo_vehiculo', $vehicle?->modelo);
                                $set('marca_vehiculo', $vehicle?->marca);
                                $set('tarjeta_propiedad', $vehicle?->tarjeta_propiedad);
                                // CORRECCIÓN: Se formatea la fecha a 'Y-m-d' para el DatePicker.
                                $set('fecha_tecnomecanica', $vehicle?->fecha_tecnomecanica?->format('Y-m-d'));
                                $set('fecha_soat', $vehicle?->fecha_soat?->format('Y-m-d'));
                                $set('mantenimiento_preventivo_taller', $vehicle?->mantenimiento_preventivo_taller);
                                $set('fecha_mantenimiento', $vehicle?->fecha_mantenimiento?->format('Y-m-d'));
                                $set('fecha_ultimo_aceite', $vehicle?->fecha_ultimo_aceite?->format('Y-m-d'));
                            })
                            ->required(),
                        TextInput::make('licencia_conductor')
                            ->label('Número de Licencia'),
                        TextInput::make('placa_vehiculo')
                            ->label('Placa del Vehículo'),
                            TextInput::make('modelo_vehiculo')
                            ->label('Modelo del Vehículo'),
                        TextInput::make('marca_vehiculo')
                            ->label('Marca/Tipo de Vehículo'),
                        TextInput::make('tarjeta_propiedad')
                            ->label('N° Tarjeta de Propiedad'),
                        DatePicker::make('fecha_tecnomecanica')
                            ->label('Vencimiento Tecnomecánica'),
                        DatePicker::make('fecha_soat')
                            ->label('Vencimiento SOAT'),
                        TextInput::make('mantenimiento_preventivo_taller')
                            ->label('Taller Mantenimiento'),
                        DatePicker::make('fecha_mantenimiento')
                            ->label('Próximo Mantenimiento'),
                        DatePicker::make('fecha_ultimo_aceite')
                            ->label('Último Cambio de Aceite'),
                        DatePicker::make('fecha_inspeccion')
                            ->label('Fecha de Inspección')
                            ->default(now())
                            ->required(),
                        TextInput::make('kilometraje_actual')
                            ->numeric()
                            ->required(),
                            
                    ])->columns(3),

                Section::make('Niveles')
                    ->schema([
                        Radio::make('nivel_refrigerante')->label('Líquido Refrigerante')->options($options)->inline()->default('N/A'),
                        Radio::make('nivel_frenos')->label('Líquido de Frenos')->options($options)->inline()->default('N/A'),
                        Radio::make('nivel_aceite_motor')->label('Aceite Motor')->options($options)->inline()->default('N/A'),
                        Radio::make('nivel_hidraulico')->label('Nivel Liq. Hidráulico')->options($options)->inline()->default('N/A'),
                        Radio::make('nivel_limpiavidrios')->label('Agua de Limpiavidrios')->options($options)->inline()->default('N/A'),
                    ])->columns(3),

                Section::make('Luces')
                    ->schema([
                        Radio::make('luces_altas')->options($options)->inline()->default('N/A'),
                        Radio::make('luces_bajas')->options($options)->inline()->default('N/A'),
                        Radio::make('luces_direccionales')->options($options)->inline()->default('N/A'),
                        Radio::make('luces_freno')->options($options)->inline()->default('N/A'),
                        Radio::make('luces_reversa')->options($options)->inline()->default('N/A'),
                        Radio::make('luces_parqueo')->options($options)->inline()->default('N/A'),
                    ])->columns(3),
                
                Section::make('Equipo de Carretera')
                    ->schema([
                        Radio::make('equipo_extintor')->label('Extintor')->options($options)->inline()->default('N/A'),
                        Radio::make('equipo_tacos')->label('Tacos')->options($options)->inline()->default('N/A'),
                        Radio::make('equipo_herramienta')->label('Caja de Herramienta')->options($options)->inline()->default('N/A'),
                        Radio::make('equipo_linterna')->label('Linterna')->options($options)->inline()->default('N/A'),
                        Radio::make('equipo_gato')->label('Gato')->options($options)->inline()->default('N/A'),
                        Radio::make('equipo_botiquin')->label('Botiquín')->options($options)->inline()->default('N/A'),
                    ])->columns(3),

                Section::make('Varios')
                    ->schema([
                        Radio::make('varios_llantas')->label('Llantas')->options($options)->inline()->default('N/A'),
                        Radio::make('varios_bateria')->label('Batería')->options($options)->inline()->default('N/A'),
                        Radio::make('varios_rines')->label('Rines')->options($options)->inline()->default('N/A'),
                        Radio::make('varios_cinturon')->label('Cinturón de Seguridad')->options($options)->inline()->default('N/A'),
                        Radio::make('varios_pito')->label('Pito')->options($options)->inline()->default('N/A'),
                        Radio::make('varios_freno_emergencia')->label('Freno de Emergencia')->options($options)->inline()->default('N/A'),
                        Radio::make('varios_espejos')->label('Espejos')->options($options)->inline()->default('N/A'),
                        Radio::make('varios_plumillas')->label('Plumillas')->options($options)->inline()->default('N/A'),
                        Radio::make('varios_panoramico')->label('Panorámico')->options($options)->inline()->default('N/A'),
                    ])->columns(3),

                Textarea::make('observaciones')
                    ->columnSpanFull(),
            ]);
    }
    
    // Este método se ejecuta antes de que se cree el registro en la base de datos.
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $vehicle = $user->vehicle;

        // Asignamos los IDs y todos los datos "snapshot" al array que se guardará.
        $data['user_id'] = $user->id;
        $data['vehicle_id'] = $vehicle?->id;
        $data['nombre_conductor'] = $user->name;
        $data['licencia_conductor'] = $user->licencia_conduccion;
        $data['placa_vehiculo'] = $vehicle?->placa;
        $data['modelo_vehiculo'] = $vehicle?->modelo;
        $data['tarjeta_propiedad'] = $vehicle?->tarjeta_propiedad;
        $data['fecha_tecnomecanica'] = $vehicle?->fecha_tecnomecanica;
        $data['fecha_soat'] = $vehicle?->fecha_soat;
        $data['mantenimiento_preventivo_taller'] = $vehicle?->mantenimiento_preventivo_taller;
        $data['fecha_mantenimiento'] = $vehicle?->fecha_mantenimiento;
        $data['fecha_ultimo_aceite'] = $vehicle?->fecha_ultimo_aceite;

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_inspeccion')->date()->sortable(),
                TextColumn::make('user.name')->label('Técnico')->searchable(),
                TextColumn::make('vehicle.placa')->label('Placa')->searchable(),
                TextColumn::make('kilometraje_actual')->numeric(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (PreoperationalInspection $record) {
                        $pdf = Pdf::loadView('pdf.preoperational-pdf', ['inspection' => $record]);
                        return response()->streamDownload(
                            fn() => print($pdf->output()), 
                            'inspeccion-'.$record->id.'.pdf'
                        );
                    }),
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
            'index' => Pages\ListPreoperationalInspections::route('/'),
            'create' => Pages\CreatePreoperationalInspection::route('/create'),
            'edit' => Pages\EditPreoperationalInspection::route('/{record}/edit'),
        ];
    }
}
