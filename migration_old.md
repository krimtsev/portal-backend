# Tickets category
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
