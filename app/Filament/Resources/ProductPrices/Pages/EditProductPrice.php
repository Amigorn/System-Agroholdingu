<?php

namespace App\Filament\Resources\ProductPrices\Pages;

use App\Filament\Resources\ProductPrices\ProductPriceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductPrice extends EditRecord
{
    protected static string $resource = ProductPriceResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['valid_until'])) {
            $data['valid_until'] = null;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}