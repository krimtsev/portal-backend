# Partners
``` sql
SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;

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
