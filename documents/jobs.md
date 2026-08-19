# Задачи

### Обновление сертификатов из Google Sheets
```
php artisan certificates:sync
```

ключи
> --now : Запустить синхронизацию минуя очередь

## YClients

### Синхронизация комментариев
```
php artisan yclients:sync-comments
```

ключи
> --date= : Конкретный день в формате YYYY-MM-DD  
> --month= : Полный месяц в формате YYYY-MM  
> --company_id= : Конкретный ID компании из YClients (yclients_id)

### Синхронизация списка записей компании
```
php artisan yclients:sync-records
```

ключи
> --date= : Конкретный день в формате YYYY-MM-DD  
> --month= : Полный месяц в формате YYYY-MM  
> --company_id= : Конкретный ID компании из YClients (yclients_id)

### Синхронизация товарных транзакций компании
```
php artisan yclients:sync-storage-transactions
```

ключи
> --date= : Конкретный день в формате YYYY-MM-DD  
> --month= : Полный месяц в формате YYYY-MM  
> --company_id= : Конкретный ID компании из YClients (yclients_id) 

### Синхронизация транзакций компании
```
php artisan yclients:sync-transactions
```

ключи
> --date= : Конкретный день в формате YYYY-MM-DD  
> --month= : Полный месяц в формате YYYY-MM  
> --company_id= : Конкретный ID компании из YClients (yclients_id)

### Синхронизация сотрудников которые работали
```
php artisan yclients:sync-staff-work-days
```

ключи
> --date= : Конкретный день в формате YYYY-MM-DD  
> --month= : Полный месяц в формате YYYY-MM  
> --company_id= : Конкретный ID компании из YClients (yclients_id)

### Синхронизация сотрудников компании
```
php artisan yclients:sync-company-staff
```

ключи
> --company_id= : Конкретный ID компании из YClients (yclients_id)

### Синхронизация основных показателей компании за месяц
```
php artisan yclients:sync-company-month-stats
```

ключи  
> --month= : Полный месяц в формате YYYY-MM  
> --company_id= : Конкретный ID компании из YClients (yclients_id)

### Синхронизация статистики по сотрудникам за месяц
```
php artisan yclients:sync-staff-month-stats
```

ключи
> --month= : Полный месяц в формате YYYY-MM  
> --company_id= : Конкретный ID компании из YClients (yclients_id)

### Синхронизация черных списков телефонных номеров c Mango API
```
php artisan mango:sync-blacklist
```

ключи
> --now : Запустить синхронизацию минуя очередь  

### Синхронизация списка входящих звонков c Mango API
```
php artisan mango:sync-calls
```

ключи
> --date= : Конкретный день в формате YYYY-MM-DD  
> --silent : Не отправлять уведомления  
> --protected : Не удалять задачи в случае неудачи
