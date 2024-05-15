<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Filament\Resources\StockAdjustmentResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockAdjustmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockAdjustments';

    public function form(Form $form): Form
    {
        return StockAdjustmentResource::form($form);
    }

    public function table(Table $table): Table
    {
        return StockAdjustmentResource::table($table);
    }
}
