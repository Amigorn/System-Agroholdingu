<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            return;
        }

        Invoice::factory(15)
            ->create()
            ->each(function ($invoice) use ($products) {
                // Ustalamy losową liczbę pozycji dla faktury (np. od 1 do 4, ale nie więcej niż liczba dostępnych produktów)
                $itemsCount = min(fake()->numberBetween(1, 4), $products->count());
                
                // Losujemy unikalne produkty dla tej konkretnej faktury (brak duplikatów produktów w obrębie jednej faktury)
                $selectedProducts = $products->random($itemsCount);

                foreach ($selectedProducts as $product) {
                    $quantity = fake()->numberBetween(1, 30);
                    $unitPrice = fake()->randomFloat(2, 50, 1500);
                    $total = round($quantity * $unitPrice, 2);

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $product->id,
                        'unit_id' => $product->unit_id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $total,
                        'notes' => fake()->optional()->sentence(),
                    ]);
                }

                // Przeliczamy i aktualizujemy całkowitą wartość faktury
                $invoiceTotal = $invoice->items()->sum('total');
                $invoice->update(['total' => round($invoiceTotal, 2)]);
            });
    }
}