<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $title = 'Dashboard';

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('customer')
                            ->options(fn() => \App\Models\Customer::pluck('name', 'id')->toArray())
                            ->placeholder('All customers')
                            ->searchable()
                            ->preload(),
                        DatePicker::make('start_date')
                            ->maxDate(fn(Get $get) => $get('end_date') ?: now()),
                        DatePicker::make('end_date')
                            ->minDate(fn(Get $get) => $get('start_date') ?: now())
                            ->maxDate(now()),
                    ])
                    ->columns(3)
                    ->visible(auth()->user()->hasRole(['super_admin', 'manager'])),
            ]);
    }
}
