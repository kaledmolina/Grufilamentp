@php
    // $getKey() nos da la clave única para el item actual en el que estamos dentro del Repeater.
    $repeaterItemKey = $getKey();
    
    // Construimos la ruta completa al estado del campo 'id' para este item específico.
    // 'data' es el array que contiene toda la información del formulario en Livewire.
    $idStatePath = 'data.fotos_existentes.' . $repeaterItemKey . '.id';
    
    // Usamos el componente Livewire para obtener el valor (el ID de la foto) de esa ruta específica.
    $fotoId = $getLivewire()->get($idStatePath);
@endphp

<div>
    {{-- Nos aseguramos de que el ID de la foto exista antes de generar la URL --}}
    @if($fotoId)
        <img src="{{ route('fotos.show', ['ordenFoto' => $fotoId]) }}"
             alt="Foto de la orden"
             class="rounded-lg shadow-md object-cover w-full h-40">
    @endif
</div>