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

INSERT INTO `users_access_rights` (
    `user_id`
)
SELECT
    `id` as `user_id`
FROM
    `_users`;

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

INSERT INTO `partner_reports_settings` (
    `id`,
    `partner_id`,
    `tg_active`,
    `tg_chat_id`,
    `pay_end`,
    `lost_client_days`,
    `repeat_client_days`,
    `new_client_days`
)
SELECT
    `id`,
    `id` as `partner_id`,
    `tg_active`,
    `tg_chat_id`,
    `tg_pay_end` as `pay_end`,
    `lost_client_days`,
    `repeat_client_days`,
    `new_client_days`
FROM
    `_partners`;

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
```

# Cloud
``` sql
SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;

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
    `upload_id`,
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
    `upload_id`,
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
FROM partners p
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

INSERT INTO partner_groups (title, created_at, updated_at)
SELECT MIN(name) AS group_title, NOW(), NOW()
FROM _partners
WHERE tg_chat_id IS NOT NULL
GROUP BY tg_chat_id
HAVING COUNT(*) > 1;

UPDATE partners p
JOIN (
    SELECT tg_chat_id, MIN(name) AS first_name
    FROM _partners
    WHERE tg_chat_id IS NOT NULL
    GROUP BY tg_chat_id
    HAVING COUNT(*) > 1
) g
JOIN partner_groups pg 
    ON pg.title = g.first_name COLLATE utf8mb4_unicode_ci
JOIN _partners src
    ON src.name COLLATE utf8mb4_unicode_ci = pg.title
SET p.group_id = pg.id
WHERE p.name COLLATE utf8mb4_unicode_ci = src.name;

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
```

# Tickets
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
