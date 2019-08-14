<?php 
namespace Ry\Shop\Models\Traits;

use Ry\Shop\Models\Bank\BankAccount;

trait HasBankAccounts
{
    public function bankAccounts() {
        return $this->morphMany(BankAccount::class, 'bankable');
    }
}
?>