<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use App\Models\Cart;
use Filament\Actions;
use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Clusters\Shop\Resources\SaleResource;
use App\Models\Transaction;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;
    protected static string $view = 'filament.pages.shop.sale.products';
    protected static ?string $title = 'Penjualan';
    public $page = 1;
    public string $search = '';
    public ?int $categoryId = null;
    public ?int $totalOrder = 0;
    public ?int $totalCheckout = 0;

    public function mount(): void
    {
        $this->countOrder();
    }

    public function getProductsProperty()
    {
        return Product::with(['karat', 'category', 'stockTotals'])
            ->when(
                $this->search,
                fn($query) =>
                $query->where('name', 'like', '%' . $this->search . '%')
            )
            ->when(
                $this->categoryId,
                fn($query) =>
                $query->where('category_id', $this->categoryId)
            )
            ->orderBy('name')
            ->paginate(12, ['*'], 'page', $this->page);
    }

    public function updatingPage($value)
    {
        $this->page = $value;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryId()
    {
        $this->resetPage();
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }


    // Add to Cart
    public function addToCart($id)
    {
        $product = Product::with(['karat', 'stockTotals'])->find($id);

        if (!$product || !$product->stockTotals || $product->stockTotals->total < 1) {
            Notification::make()
                ->title("{$product->name} Stok kosong!")
                ->danger()
                ->duration(3000)
                ->send();
            return;
        }

        $existingCart = Cart::where('product_id', $product->id)->first();

        if ($existingCart) {
            $newQuantity = $existingCart->quantity + 1;

            if ($product->stockTotals->total < $newQuantity) {
                Notification::make()
                    ->title("{$product->name} Tidak cukup stok!")
                    ->danger()
                    ->duration(3000)
                    ->send();
                return;
            }

            $existingCart->update([
                'quantity' => $newQuantity,
                'subtotal' => $product->karat->buy_price * $product->weight * $newQuantity,
            ]);
        } else {
            Cart::create([
                'product_id' => $product->id,
                'quantity' => 1,
                'weight' => $product->weight,
                'buy_price' => $product->karat->buy_price * $product->weight,
                'subtotal' => $product->karat->buy_price * $product->weight,
            ]);
        }

        Notification::make()
            ->title("{$product->name} berhasil masuk ke keranjang.")
            ->success()
            ->duration(3000)
            ->send();
        $this->countOrder();
    }

    public function countOrder()
    {
        $this->totalOrder = Cart::count();
        $this->totalCheckout = Transaction::where('status', 'pending')->count();
    }

    public function gotoCart()
    {
        $this->redirect(route('filament.admin.shop.resources.sales.cart'));
    }

    public function gotoOrder()
    {
        $this->redirect(route('filament.admin.shop.resources.sales.orders'));
    }
}
