<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenResource\Pages;
use App\Models\Orden;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class OrdenResource extends Resource
{
    protected static ?string $model = Orden::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Órdenes de Servicio';
    protected static ?string $modelLabel = 'Orden de Servicio';

    public static function getEloquentQuery(): Builder
    {
        // Mantenemos la lógica para que el técnico solo vea sus órdenes.
        if (auth()->user()->hasRole('tecnico')) {
            return parent::getEloquentQuery()->where('technician_id', auth()->id());
        }
        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    Section::make('Información Principal')
                        ->schema([
                            TextInput::make('numero_orden')->label('Número de Orden')->required()->unique(ignoreRecord: true),
                            TextInput::make('numero_expediente')->label('Número de Expediente'),
                            TextInput::make('nombre_cliente')->label('Nombre del Cliente')->required(),
                            DateTimePicker::make('fecha_hora')->label('Fecha y Hora')->required(),
                            TextInput::make('valor_servicio')->label('Valor del Servicio')->numeric()->prefix('$'),
                            TextInput::make('placa')->label('Placa'),
                            TextInput::make('referencia')->label('Referencia'),
                        ])->columnSpan(1),

                    Section::make('Detalles del Servicio')
                        ->schema([
                            TextInput::make('unidad_negocio')->label('Unidad de Negocio'),
                            TextInput::make('movimiento')->label('Movimiento'),
                            TextInput::make('servicio')->label('Servicio'),
                            TextInput::make('modalidad')->label('Modalidad'),
                            TextInput::make('tipo_activo')->label('Tipo de Activo'),
                            TextInput::make('marca')->label('Marca'),
                            Select::make('technician_id')
                                ->label('Técnico Asignado')
                                ->relationship('technician', 'name', modifyQueryUsing: fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'tecnico')))
                                ->searchable()
                                ->preload(),
                        ])->columnSpan(1),
                ]),

                Section::make('Información de Contacto en Origen')
                    ->schema([
                        TextInput::make('nombre_asignado')->label('Nombre del Asignado'),
                        TextInput::make('celular')->label('Celular')->tel(),
                    ]),
                
                Grid::make(2)->schema([
                    Section::make('Origen')
                        ->schema([
                            TextInput::make('ciudad_origen')->label('Ciudad de Origen')->required(),
                            TextInput::make('direccion_origen')->label('Dirección de Origen')->required(),
                            Textarea::make('observaciones_origen')->label('Observaciones de Origen')->rows(3),
                        ])->columnSpan(1),

                    Section::make('Destino')
                        ->schema([
                            TextInput::make('ciudad_destino')->label('Ciudad de Destino')->required(),
                            TextInput::make('direccion_destino')->label('Dirección de Destino')->required(),
                            Textarea::make('observaciones_destino')->label('Observaciones de Destino')->rows(3),
                        ])->columnSpan(1),
                ]),

                Section::make('Información Adicional')
                    ->schema([
                        Textarea::make('observaciones_generales')->label('Observaciones Generales (Información adicional del valor del servicio, etc.)')->rows(4),
                        Toggle::make('es_programada')->label('¿Es una orden programada?'),
                        Select::make('status')
                            ->label('Estado de la Orden')
                            ->options([
                                'abierta' => 'Abierta',
                                'programada' => 'Programada',
                                'en proceso' => 'En Proceso',
                                'cerrada' => 'Cerrada',
                                'fallida' => 'Fallida',
                                'anulada' => 'Anulada',
                            ])
                            ->required()
                            ->default('abierta'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_orden')->label('N° Orden')->searchable()->sortable(),
                TextColumn::make('nombre_cliente')->label('Cliente')->searchable(),
                TextColumn::make('technician.name')->label('Técnico Asignado')->sortable(),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'primary' => 'abierta',
                        'warning' => 'en proceso',
                        'success' => 'cerrada',
                        'danger' => 'fallida',
                        'gray' => 'anulada',
                    ]),
                TextColumn::make('fecha_hora')->label('Fecha')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdens::route('/'),
            'create' => Pages\CreateOrden::route('/create'),
            'edit' => Pages\EditOrden::route('/{record}/edit'),
        ];
    }
}
