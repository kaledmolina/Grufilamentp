<x-filament-panels::page>

    <x-filament-widgets::widgets 
        :widgets="$this->getWidgets()"
        :data="['status' => $this->status]"
    />

</x-filament-panels::page>