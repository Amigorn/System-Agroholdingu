<?php

namespace App\Filament\Resources\ProductPrices\Pages;

use App\Filament\Resources\ProductPrices\ProductPriceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductPrice extends CreateRecord
{
    protected static string $resource = ProductPriceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Jeśli pole jest puste lub pusta tablica/string, wymuszamy null
        if (empty($data['valid_until'])) {
            $data['valid_until'] = null;
        }

        return $data;
    }
}