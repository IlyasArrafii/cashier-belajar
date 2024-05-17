<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockAdjustmentResource\Pages;
//use App\Filament\Resources\StockAdjustmentResource\RelationManagers;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\StockAdjustment;
use App\Traits\HasNavigationBadge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockAdjustmentResource extends Resource
{
    use HasNavigationBadge;

    protected static ?string $model = StockAdjustment::class;
    protected static ?string $navigationGroup = 'Stock';
    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->hiddenOn(RelationManagers\StockAdjustmentsRelationManager::class)
                    ->required(),
                Forms\Components\TextInput::make('quantity_adjusted')
                    ->required()
                    ->placeholder('Please insert a number for the stock adjustment')
                    ->numeric(),
                Forms\Components\Textarea::make('reason')
                    ->required()
                    ->default('Restock.')
                    ->maxLength(65535)
                    ->placeholder('Write a reason for the stock adjustment')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->sortable()
                    ->searchable()
                    ->hiddenOn(RelationManagers\StockAdjustmentsRelationManager::class),
                Tables\Columns\TextColumn::make('quantity_adjusted')
                    ->label('Adjusted')
                    ->numeric()
                    ->suffix(' Quantity')
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->limit(50)
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->hiddenOn(RelationManagers\StockAdjustmentsRelationManager::class)
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->color('gray')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStockAdjustments::route('/'),
        ];
    }
}
