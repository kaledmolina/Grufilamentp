<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdenesExport;
use App\Models\User;
use App\Models\Orden;

class Reportes extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return ! auth()->user()->hasRole('operador');
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';
    protected static string $view = 'filament.pages.reportes';
    protected static ?string $navigationLabel = 'Reportes';
    protected static ?string $title = 'Generar Reportes de Órdenes';
    protected static ?string $navigationGroup = 'Herramientas';
    protected static ?int $navigationSort = 4;

    // Propiedad para guardar los datos del formulario (fechas)
    public ?array $data = [];

    // Se ejecuta al cargar la página para inicializar el formulario
    public function mount(): void
    {
        $this->form->fill();
    }

    // Define la estructura del formulario que se mostrará en la página
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('fecha_inicio')
                    ->label('Fecha de Inicio')
                    ->required(),
                DatePicker::make('fecha_fin')
                    ->label('Fecha de Fin')
                    ->required(),
                Select::make('technician_id')
                    ->label('Técnico Asignado')
                    ->options(User::whereHas('roles', fn($q) => $q->where('name', 'tecnico'))->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('Todos los técnicos (Opcional)'),
                Select::make('nombre_cliente')
                    ->label('Cliente')
                    ->options(Orden::select('nombre_cliente')->distinct()->pluck('nombre_cliente', 'nombre_cliente'))
                    ->searchable()
                    ->placeholder('Todos los clientes (Opcional)'),
            ])
            ->statePath('data');
    }

    // Esta es la acción que se ejecutará al presionar el botón de descarga
    public function export()
    {
        $data = $this->form->getState();
        $startDate = $data['fecha_inicio'];
        $endDate = $data['fecha_fin'];
        $technicianId = $data['technician_id'] ?? null;
        $nombreCliente = $data['nombre_cliente'] ?? null;
        
        $fileName = "reporte-ordenes-{$startDate}-a-{$endDate}.xlsx";

        // Llama a la clase de exportación y descarga el archivo
        return Excel::download(new OrdenesExport($startDate, $endDate, $technicianId, $nombreCliente), $fileName);
    }
}
