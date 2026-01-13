# DELETE ALL
``` sql
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE cache;
DROP TABLE cache_locks;
DROP TABLE failed_jobs;
DROP TABLE job_batches;
DROP TABLE jobs;
DROP TABLE migrations;
DROP TABLE partner_reports;
DROP TABLE partners;
DROP TABLE password_reset_tokens;
DROP TABLE posts;
DROP TABLE sessions;
DROP TABLE users;
DROP TABLE users_access_rights;
DROP TABLE tickets_categories;
DROP TABLE tickets;
DROP TABLE tickets_files;
DROP TABLE tickets_messages;
SET FOREIGN_KEY_CHECKS=1;
```

# Users
``` sql
SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;

UPDATE `_users` SET email = NULL WHERE email = '';

INSERT INTO `users` (
    `login`,
    `name`,
    `role`,
    `partner_id`,
    `disabled`,
    `password`,
    `remember_token`,
    `last_activity`,
    `email`,
    `email_verified_at`
)
SELECT
    `login`,
    `name`,
    `role_id` as `role`,
    `partner_id`,
    `is_disabled` as `disabled`,
    `password`,
    `remember_token`,
    `last_activity`,
    `email`,
    `email_verified_at`
FROM
    `_users`;

UPDATE users SET role = 'user' WHERE role = '1';
UPDATE users SET role = 'sysadmin' WHERE role = '2';
UPDATE users SET role = 'admin' WHERE role = '3';

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
```

# Partners
``` sql
SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;

INSERT INTO `partners` (
    `id`,
    `organization`,
    `inn`,
    `ogrnip`,
    `name`,
    `contract_number`,
    `email`,
    `yclients_id`,
    `mango_telnum`,
    `address`,
    `start_at`,
    `disabled`
)
SELECT
    `id`,
    `organization`,
    `inn`,
    `ogrnip`,
    `name`,
    `contract_number`,
    `email`,
    `yclients_id`,
    `mango_telnum`,
    `address`,
    `start_at`,
    `disabled`
FROM
    `_partners`;

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
```

# Cloud
``` sql
SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;

TRUNCATE TABLE cloud_files;
TRUNCATE TABLE cloud_folders;

INSERT INTO `cloud_folders` (
    `id`,
    `name`,
    `slug`,
    `folder`,
    `category_id`,
    `created_at`
)
SELECT
    `id`,
    `name`,
    `slug`,
    `folder`,
    `category_id`,
    `created_at`
FROM
    `_upload`;

INSERT INTO `cloud_files` (
    `id`,
    `title`,
    `name`,
    `origin`,
    `path`,
    `type`,
    `ext`,
    `downloads`,
    `cloud_folders_id`,
    `created_at`
)
SELECT
    `id`,
    `title`,
    `name`,
    `origin`,
    `path`,
    `type`,
    `ext`,
    `downloads`,
    `upload_id` as `cloud_folders_id`,
    `created_at`
FROM
    `_upload_files`;

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
```

# Перекладываем номера отдельно в базу партнеров 
``` sql
INSERT INTO partner_telnums (partner_id, name, number, created_at, updated_at)
SELECT
    p.id AS partner_id,
    jt.name,
    jt.number,
    NOW() AS created_at,
    NOW() AS updated_at
FROM _partners p
CROSS JOIN JSON_TABLE(
    p.telnums,
    '$[*]' COLUMNS (
        name   VARCHAR(255) PATH '$.name',
        number VARCHAR(50)  PATH '$.number'
    )
) AS jt
WHERE p.telnums IS NOT NULL
    AND jt.number IS NOT NULL
    AND jt.number <> '';
```

# Partner groups
``` sql
SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;

TRUNCATE TABLE partner_groups;

INSERT INTO partner_groups (title, created_at, updated_at)
SELECT 
    MIN(name) AS group_title, 
    NOW(), 
    NOW()
FROM _partners
WHERE tg_chat_id IS NOT NULL
GROUP BY tg_chat_id
HAVING COUNT(*) > 1;

UPDATE partners p
JOIN _partners src
    ON src.name COLLATE utf8mb4_unicode_ci = p.name COLLATE utf8mb4_unicode_ci
JOIN (
    SELECT tg_chat_id, MIN(name) AS first_name
    FROM _partners
    WHERE tg_chat_id IS NOT NULL
    GROUP BY tg_chat_id
    HAVING COUNT(*) > 1
) g
    ON g.tg_chat_id = src.tg_chat_id
JOIN partner_groups pg
    ON pg.title COLLATE utf8mb4_unicode_ci = g.first_name COLLATE utf8mb4_unicode_ci
SET p.group_id = pg.id
WHERE src.tg_chat_id IN (
    SELECT tg_chat_id
    FROM _partners
    GROUP BY tg_chat_id
    HAVING COUNT(*) > 1
);

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
```

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

# Tickets
``` sql
SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;

TRUNCATE TABLE tickets;
TRUNCATE TABLE tickets_messages;
TRUNCATE TABLE tickets_files;

INSERT INTO `tickets` (
    `id`,
    `title`,
    `type`,
    `category_id`,
    `partner_id`,
    `user_id`,
    `state`,
    `created_at`,
    `updated_at`,
    `deleted_at`
)
SELECT
    `id`,
    `title`,
    'general' AS `type`,
    `category_id`,
    `partner_id`,
    `user_id`,
    `state`,
    `created_at`,
    `updated_at`,
    IFNULL(`deleted_at`, NULL) AS `deleted_at`
FROM
    `_tickets`;

UPDATE tickets
SET state = CASE state
    WHEN 1 THEN 'new'
    WHEN 2 THEN 'in_progress'
    WHEN 3 THEN 'waiting'
    WHEN 4 THEN 'success'
    WHEN 5 THEN 'closed'
    WHEN 6 THEN 'cancel'
    ELSE state
    END
WHERE state IN (1,2,3,4,5,6);

INSERT INTO `tickets_messages` (
    `id`,
    `text`,
    `ticket_id`,
    `user_id`,
    `created_at`,
    `updated_at`,
    `deleted_at`
)
SELECT
    `id`,
    `text`,
    `ticket_id`,
    `user_id`,
    `created_at`,
    `updated_at`,
    `deleted_at`
FROM
    `_tickets_messages`;

INSERT INTO `tickets_files` (
    `id`,
    `title`,
    `name`,
    `origin`,
    `path`,
    `type`,
    `ext`,
    `ticket_message_id`,
    `created_at`,
    `updated_at`
)
SELECT
    `id`,
    `title`,
    `name`,
    `origin`,
    `path`,
    `type`,
    `ext`,
    `ticket_message_id`,
    `created_at`,
    `updated_at`
FROM
    `_tickets_files`;

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
```

# Normalize ticket messages 
``` sql
SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;

UPDATE tickets_messages
SET text = REPLACE(
    REPLACE(text, '<b>', ''),
    '</b>', ''
    )
WHERE text LIKE '%<b>%';

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
```

# Исправляем удаленные
``` sql
SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;

UPDATE tickets t
    JOIN _tickets s ON t.id = s.id
SET t.deleted_at = s.deleted_at
WHERE t.deleted_at <> s.deleted_at OR (t.deleted_at IS NULL AND s.deleted_at IS NOT NULL);

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
```
