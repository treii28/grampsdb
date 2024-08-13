<?php

namespace Treii28\Grampsdb\Resources\GrampsdbResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Treii28\Grampsdb\Resources\GrampsdbResource;

class EditGrampsdb extends EditRecord
{
    protected static string $resource = GrampsdbResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
