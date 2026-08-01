# Методы 
https://core.telegram.org/bots/api

> php artisan tinker

## getMe
```php
Http::get('https://api.telegram.org/bot' . config('telegram.bots.main.token') . '/getMe')->json();
```

```php
[
    "ok" => true,
    "result" => [
      "id" => 1234567890,
      "is_bot" => true,
      "first_name" => "Бот по пропущенным BRITVA",
      "username" => "britva_missedcall_bot",
      "can_join_groups" => true,
      "can_read_all_group_messages" => false,
      "supports_inline_queries" => false,
      "supports_guest_queries" => false,
      "can_connect_to_business" => false,
      "has_main_web_app" => false,
      "has_topics_enabled" => false,
      "allows_users_to_create_topics" => false,
      "can_manage_bots" => false,
      "supports_join_request_queries" => false,
    ],
  ]
```

## getWebhookInfo

```php
Http::get('https://api.telegram.org/bot' . config('telegram.bots.main.token') . '/getWebhookInfo')->json();
```
```php
 [
    "ok" => true,
    "result" => [
      "url" => "https://site.com/index.php",
      "has_custom_certificate" => false,
      "pending_update_count" => 64,
      "last_error_date" => 1785569418,
      "last_error_message" => "Connection timed out",
      "max_connections" => 40,
      "ip_address" => "1.1.1.1",
    ],
  ]
```

## getUpdates

```php
Http::get('https://api.telegram.org/bot' . config('telegram.bots.main.token') . '/getUpdates')->json();
```

```php
[
    "ok" => false,
    "error_code" => 409,
    "description" => "Conflict: can't use getUpdates method while webhook is active; use deleteWebhook to delete the webhook first",
]
```
