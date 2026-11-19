<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            return;
        }

        // Rozkład liczby faktur dla kolejnych 12 miesięcy (od 11 miesięcy temu do teraz)
        $monthlyInvoiceCounts = [
            14, 18, 22, 19, 25, 29, 34, 31, 38, 42, 36, 44,
        ];

        // Mnożniki skali wartości (np. większe partie w sezonach szczytowych)
        $monthlyValueMultipliers = [
            0.7, 0.85, 0.9, 0.8, 1.0, 1.15, 1.35, 1.45, 1.2, 1.3, 1.15, 1.4,
        ];

        foreach ($monthlyInvoiceCounts as $index => $count) {
            $monthsAgo = 11 - $index;
            $monthStart = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            $monthEnd = $monthsAgo === 0
                ? Carbon::now()
                : Carbon::now()->subMonths($monthsAgo)->endOfMonth();

            $multiplier = $monthlyValueMultipliers[$index] ?? 1.0;

            for ($i = 0; $i < $count; $i++) {
                $randomDate = Carbon::createFromTimestamp(
                    fake()->dateTimeBetween($monthStart, $monthEnd)->getTimestamp()
                );

                $invoice = Invoice::factory()->create();

                // Wymuszamy konkretną datę z danego miesiąca, aby nie nadpisała się bieżącą datą
                $invoice->timestamps = false;
                $invoice->created_at = $randomDate;
                $invoice->updated_at = $randomDate;
                $invoice->save();
                $invoice->timestamps = true;

                $itemsCount = min(fake()->numberBetween(1, 4), $products->count());
                $selectedProducts = $products->random($itemsCount);

                foreach ($selectedProducts as $product) {
                    $quantity = fake()->numberBetween(2, 25);
                    $unitPrice = round(fake()->randomFloat(2, 80, 1400) * $multiplier, 2);
                    $total = round($quantity * $unitPrice, 2);

                    $item = InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $product->id,
                        'unit_id' => $product->unit_id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $total,
                        'notes' => fake()->optional(0.3)->sentence(),
                    ]);

                    $item->timestamps = false;
                    $item->created_at = $randomDate;
                    $item->updated_at = $randomDate;
                    $item->save();
                }

                $invoiceTotal = round($invoice->items()->sum('total'), 2);

                $invoice->timestamps = false;
                $invoice->total = $invoiceTotal;
                $invoice->save();
            }
        }
    }
}