<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DailyHandoff extends Page
{
    protected static ?string $navigationIcon = "heroicon-o-clipboard-document-list";
    protected static ?string $navigationLabel = "Daily Handoff";
    protected static ?string $title = "Daily Handoff";
    protected static string $view = "filament.pages.daily-handoff";

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole("Manager") ?? false;
    }
}
