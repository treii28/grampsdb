<?php

namespace Treii28\Grampsdb\Resources\GrampsdbResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Treii28\Grampsdb\Resources\GrampsdbResource;

class ListGrampsdbs extends ListRecords
{
    protected static string $resource = GrampsdbResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
