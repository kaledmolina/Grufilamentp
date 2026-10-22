<?php

namespace App\Filament\Pages;

use App\Services\DatabaseBackupService;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CopiaSeguridad extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['administrador', 'admin']) ?? false;
    }

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';
    protected static string $view = 'filament.pages.copia-seguridad';
    protected static ?string $navigationLabel = 'Copia de Seguridad';
    protected static ?string $title = 'Copia de Seguridad de la Base de Datos';
    protected static ?string $navigationGroup = 'Herramientas';
    protected static ?int $navigationSort = 5;

    public ?array $data = [];
    public array $dbStats = [];

    public function mount(DatabaseBackupService $backupService): void
    {
        $this->form->fill([
            'compress' => true,
            'exclude_temp' => true,
        ]);

        $this->dbStats = $backupService->getDatabaseStats();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Opciones de Respaldo')
                    ->description('Configura el formato y contenido de la copia de seguridad.')
                    ->schema([
                        Checkbox::make('compress')
                            ->label('Comprimir archivo (.sql.gz)')
                            ->helperText('Recomendado. Reduce drásticamente el tamaño del archivo descargado.')
                            ->default(true),
                        Checkbox::make('exclude_temp')
                            ->label('Excluir tablas de caché y sesiones')
                            ->helperText('Omite datos temporales de las tablas sessions, cache y jobs para un archivo más limpio.')
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }

    public function download()
    {
        $data = $this->form->getState();

        Notification::make()
            ->title('Generando copia de seguridad')
            ->body('La descarga comenzará en unos momentos.')
            ->success()
            ->send();

        return redirect()->route('admin.backup.download', [
            'compress' => $data['compress'] ? '1' : '0',
            'exclude_temp' => $data['exclude_temp'] ? '1' : '0',
        ]);
    }
}
