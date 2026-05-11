# Tickets category (отказываемся от хранения в базе в пользу ENUM)
``` sql
SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;

INSERT INTO `tickets_categories` (
    `id`,
    `title`
)
SELECT
    `id`,
    `title`
FROM
    `_tickets_categories`;

UPDATE tickets_categories SET slug = 'franchise'         WHERE id = 1;
UPDATE tickets_categories SET slug = 'build'             WHERE id = 2;
UPDATE tickets_categories SET slug = 'marketing'         WHERE id = 3;
UPDATE tickets_categories SET slug = 'network_admin'     WHERE id = 4;
UPDATE tickets_categories SET slug = 'network_barbering' WHERE id = 5;
UPDATE tickets_categories SET slug = 'community'         WHERE id = 6;
UPDATE tickets_categories SET slug = 'office_manager'    WHERE id = 7;
UPDATE tickets_categories SET slug = 'it_department'     WHERE id = 8;
UPDATE tickets_categories SET slug = 'accounting'        WHERE id = 9;

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
```

# Оптимизация Tickets
``` sql
-- step_1

SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `tickets` DROP FOREIGN KEY `tickets_category_id_foreign`;
ALTER TABLE `tickets` CHANGE `category_id` `department` VARCHAR(50) NOT NULL;
DROP INDEX `tickets_category_id_foreign` ON `tickets`;

SET FOREIGN_KEY_CHECKS=1;

-- step_2

START TRANSACTION;

UPDATE tickets SET department = 'franchise'         WHERE department = '1';
UPDATE tickets SET department = 'build'             WHERE department = '2';
UPDATE tickets SET department = 'marketing'         WHERE department = '3';
UPDATE tickets SET department = 'network_admin'     WHERE department = '4';
UPDATE tickets SET department = 'network_barbering' WHERE department = '5';
UPDATE tickets SET department = 'community'         WHERE department = '6';
UPDATE tickets SET department = 'office_manager'    WHERE department = '7';
UPDATE tickets SET department = 'it_department'     WHERE department = '8';
UPDATE tickets SET department = 'accounting'        WHERE department = '9';

COMMIT;

DROP TABLE `tickets_categories`;

-- step_3

SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;

UPDATE `tickets_events`
SET `changes` = JSON_SET(
    JSON_REMOVE(`changes`, '$.category_id'),
    '$.department',
    JSON_OBJECT(
        'old', CASE JSON_EXTRACT(`changes`, '$.category_id.old')
            WHEN 1 THEN 'franchise'
            WHEN 2 THEN 'build'
            WHEN 3 THEN 'marketing'
            WHEN 4 THEN 'network_admin'
            WHEN 5 THEN 'network_barbering'
            WHEN 6 THEN 'community'
            WHEN 7 THEN 'office_manager'
            WHEN 8 THEN 'it_department'
            WHEN 9 THEN 'accounting'
            ELSE JSON_UNQUOTE(JSON_EXTRACT(`changes`, '$.category_id.old'))
        END,
        'new', CASE JSON_EXTRACT(`changes`, '$.category_id.new')
            WHEN 1 THEN 'franchise'
            WHEN 2 THEN 'build'
            WHEN 3 THEN 'marketing'
            WHEN 4 THEN 'network_admin'
            WHEN 5 THEN 'network_barbering'
            WHEN 6 THEN 'community'
            WHEN 7 THEN 'office_manager'
            WHEN 8 THEN 'it_department'
            WHEN 9 THEN 'accounting'
            ELSE JSON_UNQUOTE(JSON_EXTRACT(`changes`, '$.category_id.new'))
        END
    )
)

WHERE JSON_CONTAINS_PATH(`changes`, 'one', '$.category_id');

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
```
