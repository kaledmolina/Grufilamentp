<div>
    {{-- La variable $id ahora es pasada directamente desde el OrdenResource a través de viewData() --}}
    @if(isset($id) && $id)
        <img src="{{ route('show', ['ordenFoto' => $id]) }}"
             alt="Foto de la orden"
             class="rounded-lg shadow-md object-cover w-full h-40">
    @endif
</div>