<?php

namespace App\Filament\Clusters\Shop\Resources\PawningResource\Pages;

use Filament\Forms\Form;
use App\Models\Transaction;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Card;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Actions;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;
use App\Filament\Clusters\Shop\Resources\PawningResource;
use Filament\Forms\Components\Actions\Action as FormAction;

class ConfirmationPage extends Page implements HasForms
{
    protected static string $resource = PawningResource::class;

    protected static string $view = 'filament.pages.shop.pawning.confirmation-page';
    protected static ?string $title = 'Konfirmasi Penggadaian';
    protected static ?string $breadcrumb = 'Konfirmasi';

    public Transaction $record;
    public ?array $data = [];

    public function mount(string $inv): void
    {
        if (!$inv) {
            $this->redirect(route('filament.admin.shop.resources.pawnings.index'));
            return;
        }

        $this->loadPage($inv);
        $this->form->fill();
    }

    public function loadPage(string $inv): void
    {
        $this->record = Transaction::where('invoice', $inv)
            ->where('transaction_type', 'pawning')
            ->firstOrFail();
        $this->record->status = $this->record->status ?? 'pending';
    }

    public function redirectBack(Transaction $record): void
    {
        if ($record->status === 'success') {
            redirect()->route('filament.admin.shop.resources.pawnings.index');
        }
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
            Card::make([
                Placeholder::make('orders')
                    ->label('')
                    ->content(new HtmlString(
                        view('filament.pages.shop.pawning.detail', [
                            'record' => $this->record,
                        ])->render()
                    )),
                Actions::make([
                    FormAction::make('submit')
                        ->label('Konfirmasi Penggadaian')
                        ->button()
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Penggadaian')
                        ->modalSubheading('Apakah Anda yakin ingin melanjutkan penggadaian?')
                        ->modalButton('Konfirmasi')
                        ->action(fn() => $this->pawningConfirmation())
                        ->visible(fn() => $this->record->status !== 'success'),
                ])->fullWidth(),
            ]),
        ];
    }

    public function pawningConfirmation(): mixed
    {
        try {
            $this->record->pawning->update([
                'status' => 'active',
            ]);
            Notification::make()
                ->title('Penggadaian berhasil')
                ->body("Transaksi #{$this->record->invoice} telah diproses.")
                ->success()
                ->send();

            return redirect()->route('filament.admin.shop.resources.pawnings.index');
        } catch (\Throwable $e) {
            Log::error('Gagal memproses penggadaian', [
                'invoice' => $this->record->invoice ?? null,
                'error' => $e->getMessage(),
            ]);
            Notification::make()
                ->title('Terjadi kesalahan')
                ->body('Gagal memproses penggadaian.')
                ->danger()
                ->persistent()
                ->send();

            return null;
        }
    }
}
