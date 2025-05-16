<?php

namespace App\Filament\Clusters\MenuTransactions\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SubNavigationPosition;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Wizard\Step;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Clusters\MenuTransactions;
use Filament\Forms\Concerns\InteractsWithForms;

class Sell extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster = MenuTransactions::class;
    protected static string $view = 'filament.clusters.menu-transactions.pages.sell';

    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    // protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }


    public function getFormSchema(): array
    {
        return [
            Wizard::make([
                Step::make('Orders')
                    ->icon('heroicon-m-shopping-bag')
                    ->schema([
                        Placeholder::make('')
                            ->content(
                                new HtmlString(
                                    Blade::render(<<<'BLADE'
                                    @foreach(range(1, 3) as $index)
                                        <div class="border-b py-2 text-sm flex justify-between items-center">
                                            <span>Emas {{ $index }} gr</span>
                                            <span class="font-bold text-green-600">Rp {{ number_format(10_000_000, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                BLADE)
                                )
                            ),
                    ]),

                Step::make('Customer')
                    ->icon('heroicon-m-user')
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),
                    ]),

                Step::make('Billing')
                    ->icon('heroicon-m-credit-card')
                    ->schema([
                        TextInput::make('cash')->label('Pembayaran'),
                        TextInput::make('cash')->label('Kembalian'),
                        Radio::make('payment_method')
                            ->label('Metode Pemabayaran')
                            ->options([
                                'cash' => 'Tunai',
                                'online' => 'Tranfers'
                            ])->default('cash')
                    ]),
            ])
                ->submitAction(
                    new HtmlString(
                        Blade::render(<<<'BLADE'
                    <x-filament::button type="submit" color="danger">
                        Pembayaran
                    </x-filament::button>
                BLADE)
                    )
                ),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Add your save logic here
        // Example: process the sale transaction
        // $this->processSale($data);

        // For now, just dump the data
        dd($data);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Jual Emas';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Jual Emas';
    }
}
