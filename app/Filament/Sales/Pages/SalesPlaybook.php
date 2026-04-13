<?php

namespace App\Filament\Sales\Pages;

use Filament\Pages\Page;

class SalesPlaybook extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Guía de Venta';

    protected static ?string $title = 'Guía de Venta';

    protected static ?string $slug = 'guia-venta';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.sales.pages.sales-playbook';
}
