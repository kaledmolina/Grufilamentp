<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Orden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\User;

class FilteredOrdersTable extends BaseWidget
{
    // Esta propiedad recibirá el estado desde la URL
    public ?string $status = '';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
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

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('numero_orden')->label('N° Orden')->searchable(),
            TextColumn::make('technician.name')->label('Nombre del Técnico'),
            TextColumn::make('celular')->label('Número de Contacto'),
            TextColumn::make('ciudad_origen')->label('Ciudad Origen'),
            TextColumn::make('ciudad_destino')->label('Ciudad Destino'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('technician')
                ->label('Técnico')
                ->relationship('technician', 'name', modifyQueryUsing: fn ($query) => $query->whereHas('roles', fn($q) => $q->where('name', 'tecnico'))),
            
            // Puedes añadir más filtros aquí, por ejemplo, por fecha
        ];
    }
}