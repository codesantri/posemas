<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

use Filament\Pages\Page;

class Login extends BaseLogin
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.auth.login';

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()->schema([
                    $this->getUsernameFormComponent(),
                    $this->getPasswordFormComponent(),
                ])->statePath('data'),
            ),
        ];
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('username') // <-- ini wajib ada (bukan kosong)
            ->label('Username')
            ->required()
            ->autofocus()
            ->autocomplete('username');
    }

    public function getHeading(): string | Htmlable
    {
        return new HtmlString($this->formatTitle());
    }

    public function formatTitle(): string
    {
        return <<<HTML
        <div class="text-center space-y-2 mb-6">
            <div class="text-sm flex justify-center text-gray-600 text-center">
                <img src="/logo-cetak.png" alt="" srcset="" width="100px" height="100px" />
            </div>
            <!-- <h2 class="text-lg font-semibold">
                Toko Emas Logam Mulia Bangko
            </h2> -->
        </div>
        HTML;
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }
}
