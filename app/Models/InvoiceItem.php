<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceItemFactory> */
    use HasFactory;

    protected $fillable = [
        'faktura_id',
        'produkt_id',
        'ilosc',
        'cena_jednostkowa',
        'wartosc',
        'uwagi',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'faktura_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produkt_id');
    }
}