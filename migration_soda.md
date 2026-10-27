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

# Пример миграции со старой базой
``` sql
UPDATE tickets t
    JOIN _tickets ut ON t.id = ut.id
SET t.department_id = 9
WHERE ut.category_id = 11;
```


