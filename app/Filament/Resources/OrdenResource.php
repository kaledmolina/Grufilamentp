<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenResource\Pages;
use App\Filament\Resources\OrdenResource\RelationManagers;
use App\Models\Orden;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;



class OrdenResource extends Resource
{
    protected static ?string $model = Orden::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {  
        if (auth()->user()->hasRole('tecnico')) {
            return parent::getEloquentQuery()->where('technician_id', auth()->id());
        }
        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('technician_id') // Técnico
                    ->label('Técnico')
                    ->options(User::whereHas('roles', fn ($query) => $query->where('name', 'tecnico'))->pluck('name', 'id'))
                    ->required(),
                TextInput::make('address')->required()->label('Dirección'),
                Select::make('status')
                    ->options([
                        'en proceso' => 'En Proceso',
                        'cerrada' => 'Cerrada',
                        'fallida' => 'Fallida',
                    ])
                    ->disabled(fn ($record) => !auth()->user()->hasRole('tecnico') || !in_array($record->status, ['abierta', 'programada'])),
                Textarea::make('comments')
                    ->label('Comentarios del Técnico')
                    ->disabled(!auth()->user()->hasRole('tecnico')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
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
