<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthenticationLogResource\Pages;
use App\Filament\Resources\AuthenticationLogResource\RelationManagers;
use App\Models\AuthenticationLog;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AuthenticationLogResource extends Resource
{
    protected static ?string $model = AuthenticationLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('No')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('authenticatable_id')
                    ->label('Nama Pengguna')
                    ->formatStateUsing(fn ($state) => User::find($state)->name ?? '-'),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address'),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($state) => $state),
                Tables\Columns\TextColumn::make('login_at')
                    ->label('Login At')
                    ->dateTime(),
                Tables\Columns\IconColumn::make('login_successful')
                    ->label('Login Successful')
                    ->boolean(),
                Tables\Columns\TextColumn::make('logout_at')
                    ->label('Logout At')
                    ->dateTime(),
                Tables\Columns\IconColumn::make('cleared_by_user')
                    ->label('Cleared By User')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->poll('10s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAuthenticationLogs::route('/'),
        ];
    }
}
