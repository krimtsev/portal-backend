# DELETE ALL
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
SET FOREIGN_KEY_CHECKS=1;

# Users
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

# Partners
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
    `telnums`,
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
    `telnums`,
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


# Cloud
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
