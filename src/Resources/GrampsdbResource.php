<?php

namespace Treii28\Grampsdb\Resources;

use App\Filament\Resources\GrampsdbResource\Pages;
use App\Filament\Resources\GrampsdbResource\RelationManagers;
use App\Models\Grampsdb;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GrampsdbResource extends Resource
{
    protected static ?string $model = Grampsdb::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
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
            'index' => \Treii28\Grampsdb\Resources\GrampsdbResource\Pages\ListGrampsdbs::route('/'),
            'create' => \Treii28\Grampsdb\Resources\GrampsdbResource\Pages\CreateGrampsdb::route('/create'),
            'edit' => \Treii28\Grampsdb\Resources\GrampsdbResource\Pages\EditGrampsdb::route('/{record}/edit'),
        ];
    }
}
