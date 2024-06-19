<?php

namespace App\Filament\Resources\EquipementResource\Widgets;

use Filament\Widgets\Widget;

class PeriodiciteWidget extends Widget
{
    protected static string $view = 'filament.resources.equipement-resource.widgets.periodicite-widget';

    protected int | string | array $columnSpan = "full";
}
