<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PreoperationalInspection;

class PdfController extends Controller
{
    public function downloadOrdenPdf(Orden $orden)
    {
        // Carga la vista 'pdf.orden-pdf' y le pasa la variable 'orden'
        $pdf = Pdf::loadView('pdf.orden-pdf', compact('orden'));

        // Descarga el PDF con un nombre de archivo dinámico
        return $pdf->download('orden-'.$orden->numero_orden.'.pdf');
    }
    public function downloadInspectionPdf(PreoperationalInspection $inspection)
    {
        // Carga la vista 'pdf.preoperational-pdf' y le pasa la variable 'inspection'
        $pdf = Pdf::loadView('pdf.preoperational-pdf', compact('inspection'));

        // Descarga el PDF con un nombre de archivo dinámico
        return $pdf->download('inspeccion-preoperacional-'.$inspection->id.'.pdf');
    }
}