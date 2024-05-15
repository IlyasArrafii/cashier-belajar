<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make([
                    FileUpload::make('image')
                        ->image()
                        ->disk('public')
                        ->maxSize(1024)
                        ->directory('images/products')
                        ->imageCropAspectRatio('1:1'),
                ])->columns(2)
                    ->columnSpan([
                        'lg' => 2
                    ]),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->live(500)
                    ->maxLength(255)
                    ->afterStateUpdated(fn (Set $set, $state) => $set('sku', str($state . '-' . str()->random())->slug())),
                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->maxLength(255)
                    ->suffixAction(function (Forms\Set $set, Forms\Get $get) {
                        return Forms\Components\Actions\Action::make('generateSku')
                            ->icon('heroicon-o-arrow-path')
                            ->hidden(!$get('name'))
                            ->action(fn () => $set('sku', str($get('name') . '-' . str()->random())->slug()));
                    }),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\MarkdownEditor::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Group::make([
                    Forms\Components\TextInput::make('stock_quantity')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('cost_price')
                        ->mask(RawJs::make('$money($input)'))
                        ->required()
                        ->prefix('Rp'),
                    Forms\Components\TextInput::make('price')
                        ->mask(RawJs::make('$money($input)'))
                        ->required()
                        ->prefix('Rp')
                        ->live(500),

                ])->columns(3)->columnSpanFull(),

            ]);
        dd($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image')->circular()->disk('public'),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Qty')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_price')
                    ->numeric()
                    ->prefix('Rp ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->numeric()
                    ->prefix('Rp ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(500)
                    ->color('gray')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function (Product $record) {
                            if ($record->image) {
                                foreach ($record->image as $ph) Storage::disk('public')->delete($ph);
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StockAdjustmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
