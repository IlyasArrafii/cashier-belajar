<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Exports\OrderExporter;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Traits\HasNavigationBadge;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Enum;

class OrderResource extends Resource
{
    use HasNavigationBadge;

    protected static ?string $model = Order::class;
    protected static ?string $navigationGroup = 'Transactions';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')->schema([
                    Forms\Components\TextInput::make('order_number')
                        ->required()
                        ->default(generateSequentialNumber(Order::class))
                        ->readOnly(),
                    Forms\Components\TextInput::make('order_name')
                        ->maxLength(255)
                        ->placeholder('Tulis nama pesanan'),
                    Forms\Components\TextInput::make('total')
                        ->readOnlyOn('create')
                        ->default(0)
                        ->numeric(),
                    Forms\Components\Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->label('Customer (optional)')
                        ->placeholder('Pilih Customer'),

                    Forms\Components\Group::make([
                        Forms\Components\Select::make('payment_method')
                            ->enum(\App\Enums\PaymentMethod::class)
                            ->options(\App\Enums\PaymentMethod::class)
                            ->default(\App\Enums\PaymentMethod::CASH)
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->required()
                            ->enum(\App\Enums\OrderStatus::class)
                            ->options(\App\Enums\OrderStatus::class)
                            ->default(\App\Enums\OrderStatus::PENDING),
                    ])->columnSpan(2)->columns(2),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            /* Columns Get From getTable */
            ->columns(self::getTableColumns())
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(\App\Enums\OrderStatus::class),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->multiple()
                    ->options(\App\Enums\PaymentMethod::class),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->maxDate(fn (Forms\Get $get) => $get('end_date') ?: now())
                            ->native(false),
                        Forms\Components\DatePicker::make('created_until')
                            ->native(false)
                            ->maxDate(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                /* Print PDF Order Action */
                Tables\Actions\Action::make('print')
                    ->button()
                    ->color('gray')
                    ->icon('heroicon-o-printer')
                    ->action(function (Order $record) {
                        $pdf = Pdf::loadView('pdf.print-order', [
                            'order' => $record,
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, 'receipt-' . $record->order_number . '.pdf');
                    }),
                ViewAction::make()->color('gray')->visible(fn (Order $order) => ($order->status === OrderStatus::PENDING || $order->status == OrderStatus::COMPLETED || $order->status == OrderStatus::CANCELLED)),
                Tables\Actions\ActionGroup::make([
                    /* View Details Transaction */
                    EditAction::make()
                        ->color('gray'),
                    /* Edit Transaction Action */
                    Tables\Actions\Action::make('edit-transaction')
                        //Hidden if status completed
                        ->visible(fn (Order $record) => $record->status === OrderStatus::PENDING)
                        ->hidden(fn (Order $order) => ($order->status == OrderStatus::COMPLETED || $order->status == OrderStatus::CANCELLED))
                        ->label('Edit Transaction')
                        ->icon('heroicon-o-pencil')
                        ->url(fn ($record) => "/orders/{$record->order_number}"),
                    /* Mark as Complete Action */
                    Tables\Actions\Action::make('mark-as-complete')
                        ->visible(fn (Order $record) => $record->status === OrderStatus::PENDING)
                        ->hidden(fn (Order $order) => ($order->status == OrderStatus::CANCELLED))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Order $record) => $record->markAsComplete())
                        ->label('Mark as Complete'),
                    Tables\Actions\Action::make('divider')->label('')->disabled(),
                    /* Delete Action */
                    Tables\Actions\DeleteAction::make()
                        ->before(function (Order $order) {
                            $order->orderDetails()->delete();
                            $order->delete();
                        })->hidden(fn (Order $order) => ($order->status == OrderStatus::COMPLETED || $order->status == OrderStatus::CANCELLED)),
                ])->color('gray')->hidden(fn (Order $order) => ($order->status == OrderStatus::COMPLETED || $order->status == OrderStatus::CANCELLED)),
            ])
            ->bulkActions([
                /* Bulk Delete Data Selection Action */
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (\Illuminate\Support\Collection $records) {
                            $records->each(fn (Order $order) => $order->orderDetails()->delete());
                        }),
                ]),
            ])
            ->headerActions([
                /* Export Data from table to xlsx Action */
                ExportAction::make()
                    ->label('Export Excel')
                    ->fileDisk('public')
                    ->color('success')
                    ->icon('heroicon-o-document-text')
                    ->exporter(OrderExporter::class),
            ])->recordUrl(null);
    }

    public static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('order_number')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('order_name')
                ->searchable(),
            Tables\Columns\TextColumn::make('discount')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('total')
                ->numeric()
                ->alignEnd()
                ->sortable()
                ->summarize(
                    Tables\Columns\Summarizers\Sum::make('total')
                        ->money('IDR'),
                ),
            Tables\Columns\TextColumn::make('profit')
                ->numeric()
                ->alignEnd()
                ->summarize(
                    Tables\Columns\Summarizers\Sum::make('profit')
                        ->money('IDR'),
                )
                ->sortable(),
            Tables\Columns\TextColumn::make('payment_method')
                ->badge()
                ->color('gray'),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn ($state) => $state->getColor()),
            Tables\Columns\TextColumn::make('user.name')
                ->numeric()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('customer.name')
                ->numeric()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->formatStateUsing(fn ($state) => $state->format('d M Y H:i')),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->formatStateUsing(fn ($state) => $state->format('d M Y H:i'))
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\OrderResource\RelationManagers\OrderDetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => ViewOrder::route('/{record}/details'),
            'create-transaction' => Pages\CreateTransaction::route('{record}'),
        ];
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist->schema([
            TextEntry::make('order_number')->color('gray'),
            TextEntry::make('customer.name')->placeholder('-'),
            TextEntry::make('discount')->money('IDR')->color('gray'),
            TextEntry::make('total')->money('IDR')->color('gray'),
            TextEntry::make('payment_method')->badge()->color('gray'),
            TextEntry::make('status')->badge()->color(fn ($state) => $state->getColor()),
            TextEntry::make('created_at')->dateTime()->formatStateUsing(fn ($state) => $state->format('d M Y H:i'))->color('gray'),
        ]);
    }

    public static function getWidgets(): array
    {
        return [
            OrderResource\Widgets\OrderStats::class,
        ];
    }
}
