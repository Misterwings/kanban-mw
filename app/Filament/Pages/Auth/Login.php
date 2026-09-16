<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Support\Enums\Width;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    protected Width|string|null $maxWidth = Width::SevenExtraLarge;

    public function hasLogo(): bool
    {
        return false;
    }
}
