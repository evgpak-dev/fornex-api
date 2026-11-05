# Fornex API
Fornex API позволяет управлять сервисами (VPS, выделенными серверами, доменами, DNS, хранилищем S3 и биллингом).

## Использование
```php
use App\Services\FornexClient;

$apiKey = 'fornex_api_key_here';
$fornex = new FornexClient($apiKey);

try {
    // 1. Получить данные аккаунта
    $account = $fornex->getAccountInfo();
    echo "Account Name: {$account['name']} ({$account['email']})\n";

    // 2. Получить актуальные заказы (VPS, Dedicated, etc.)
    $orders = $fornex->getOrders();
    print_r($orders);

} catch (RuntimeException $e) {
    echo "API Call Failed: " . $e->getMessage() . "\n";
}
```
