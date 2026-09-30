Миграция Soda:    
[x] Партнеры  
[x] Номера партнеров  
[x] Группы партнеров  
[x] Пользователи  
[x] Настройки доступов  
[x] Отделы  
[x] Файлы   
[x] Заявки  
[x] Сообщение  
[x] Пропущенные звонки  
[x] Статистика новые, повторные, пропущенные  

# Заменяем Id отделов в заявках для связанности с Britva 
``` sql
INSERT INTO departments (id, title, slug)
VALUES
    (10, 'Network Nail', 'network_nail'),
    (11, 'Makeup Artist', 'makeup_artist'),
    (12, 'Stylist', 'stylist');
```

``` sql
UPDATE tickets
SET department_id = CASE department_id
    WHEN 5 THEN 10
    WHEN 6 THEN 11
    WHEN 7 THEN 12
    END
WHERE department_id IN (5, 6, 7);
```


