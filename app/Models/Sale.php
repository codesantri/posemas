<?php

namespace App\Models;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;
    protected $guarded = [''];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }


    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // protected static function booted()
    // {
    //     // static::creating(function ($sale) {
    //     //     if (!$sale->invoice) {
    //     //         $latestId = static::max('id') ?? 0; // jika null, jadi 0
    //     //         $nextId = $latestId + 1;
    //     //         $sale->invoice = 'INV-S' . now()->format('Ymd') . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
    //     //     }
    //     //     // Tambah Transaction baru
    //     // });

    //     // static::created(function ($sale) {
    //     //     Transaction::create([
    //     //         'invoice_transaction' => $sale->invoice,
    //     //         'transaction_type' => 'sale',
    //     //         'payment_method' => 'cash', // Default, bisa disesuaikan
    //     //         'status' => 'pending', // Default, bisa disesuaikan
    //     //         'transaction_date' => now(),
    //     //         'total_amount' => $sale->total ?? 0,
    //     //     ]);
    //     // });

    //     // static::deleting(function ($sale) {
    //     //     // Hapus transaksi yang terkait invoice
    //     //     Transaction::where('invoice_transaction', $sale->invoice)->delete();
    //     //     // Hapus sale details terkait (pastikan relasi saleDetails ada dan cascade belum ada di DB)
    //     //     $sale->saleDetails()->delete();
    //     // });
    // }
}
