<?php 
namespace Ry\Shop\Models\Traits;

use Ry\Shop\Models\Price\Price;

trait SellableTrait
{
    public function prices() {
        return $this->morphMany(Price::class, 'priceable');
    }
}
?>