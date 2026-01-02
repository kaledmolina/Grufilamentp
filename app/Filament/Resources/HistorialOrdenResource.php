<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HistorialOrdenResource\Pages;
use App\Models\Orden;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Support\Colors\Color;

class HistorialOrdenResource extends Resource
{
    protected static ?string $model = Orden::class;

    protected static ?string $modelLabel = 'Historial de Orden';
    protected static ?string $slug = 'historial-ordenes';

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Gestión de Órdenes';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('administrador');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_orden')->label('Orden')->searchable()->sortable(),
                TextColumn::make('nombre_cliente')->label('Cliente')->searchable(),
                BadgeColumn::make('status')
                    ->label('Estado Actual')
                    ->colors([
                        'primary' => 'abierta',
                        'warning' => 'en proceso',
                        'success' => 'cerrada',
                        'danger' => 'fallida',
                        'gray' => 'anulada',
                    ]),
            ])
            ->actions([
                Action::make('ver_historial')
                    ->label('Ver Historial')
                    ->icon('heroicon-o-eye')
                    ->color(Color::Blue)
                    ->modalHeading('Historial de Cambios')
                    ->modalContent(fn (Orden $record) => view('filament.resources.historial-orden-resource.modal-content', ['orden' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHistorialOrdens::route('/'),
        ];
    }
}
