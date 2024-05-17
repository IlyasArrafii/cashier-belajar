<?php

namespace App\Traits;

trait redirectUrl
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
