<?php
namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Orden;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn; // <-- Importar para columnas editables
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter; // <-- Importar para el nuevo filtro
use Illuminate\Database\Eloquent\Builder;

class FilteredOrdersTable extends BaseWidget
{
    // Esta propiedad recibirá el estado desde la URL
    public ?string $status = '';

    protected function getTableQuery(): Builder
    {
        $query = Orden::query();

        // Si se ha pasado un estado en la URL, se aplica el filtro
        if (!empty($this->status) && $this->status !== 'todas') {
            $query->where('status', $this->status);
        }

        return $query;
    }

    protected function getTableHeading(): string
    {
        if (empty($this->status) || $this->status === 'todas') {
            return 'Todas las Órdenes';
        }
        return 'Órdenes en estado: ' . ucfirst($this->status);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('numero_orden')->label('N° Orden')->searchable(),
                
                // CAMBIO: Columna de Técnico ahora es un selector editable
                SelectColumn::make('technician_id')
                    ->label('Técnico Asignado')
                    ->options(
                        User::whereHas('roles', fn ($query) => $query->where('name', 'tecnico'))
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->placeholder('Sin asignar'),

                TextColumn::make('celular')->label('Número de Contacto'),
                TextColumn::make('ciudad_origen')->label('Ciudad Origen'),
                TextColumn::make('ciudad_destino')->label('Ciudad Destino'),

                // CAMBIO: Columna de Estado ahora es un selector editable
                SelectColumn::make('status')
                    ->label('Estado')
                    ->options([
                        'abierta' => 'Abierta',
                        'programada' => 'Programada',
                        'en proceso' => 'En Proceso',
                        'cerrada' => 'Cerrada',
                        'fallida' => 'Fallida',
                        'anulada' => 'Anulada',
                        'rechazada' => 'Rechazada',
                    ])
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('technician')
                    ->label('Técnico')
                    ->relationship('technician', 'name', modifyQueryUsing: fn ($query) => $query->whereHas('roles', fn($q) => $q->where('name', 'tecnico'))),
                
                // CAMBIO: Nuevo filtro para órdenes sin técnico
                TernaryFilter::make('sin_tecnico')
                    ->label('Con Técnico Asignado')
                    ->nullable()
                    ->attribute('technician_id')
                    ->trueLabel('Sí')
                    ->falseLabel('No')
            ]);
    }
}
