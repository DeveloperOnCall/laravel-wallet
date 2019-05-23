# Купить бесплатно

Иногда возникают ситуации, когда необходимо подарить товар.
Для этих случаев существует этот метод.

---

- [Пользователь](#user-model)
- [Товар](#item-model)
- [Покупка](#pay-free)

<a name="user-model"></a>
## Пользователь

Добавим `CanPay` trait и `Customer` interface в модель User.

```php
use Bavix\Wallet\Traits\CanPay;
use Bavix\Wallet\Interfaces\Customer;

class User extends Model implements Customer
{
    use CanPay;
}
```

<a name="item-model"></a>
## Товар

Добавим `HasWallet` trait и `Product` interface в модель Item.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Interfaces\Customer;

class Item extends Model implements Product
{
    use HasWallet;

    public function canBuy(Customer $customer, bool $force = false): bool
    {
        /**
         * Если покупку можно совершить всего 1 раз, то
         *  return !$customer->paid($this);
         */
        return true; 
    }

    public function getAmountProduct(): int
    {
        return 100;
    }

    public function getMetaProduct(): ?array
    {
        return [
            'title' => $this->title, 
            'description' => 'Purchase of Product #' . $this->id, 
            'price' => $this->getAmountProduct(),
        ];
    }
}
```

<a name="pay-free"></a>
## Покупка

Найдем пользователя и проверим его баланс.

```php
$user = User::first();
$user->balance; // int(100)
```

Найдем товар, проверим стоимость и баланс.

```php
$item = Item::first();
$item->getAmountProduct(); // int(100)
$item->balance; // int(0)
```

Переходим к покупке.

```php
$user->payFree($item);
(bool)$user->paid($item); // bool(true)
$user->balance; // int(100)
$item->balance; // int(0)
```

Баланс пользователя и товара остался прежним.

Просто работает!