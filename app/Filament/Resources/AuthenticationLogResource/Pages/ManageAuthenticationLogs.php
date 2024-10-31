<?php

namespace App\Filament\Resources\AuthenticationLogResource\Pages;

use App\Filament\Resources\AuthenticationLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAuthenticationLogs extends ManageRecords
{
    protected static string $resource = AuthenticationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
