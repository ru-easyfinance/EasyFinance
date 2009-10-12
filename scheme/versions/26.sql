ALTER TABLE `target_bill`
    ADD INDEX `bill_idx`(`bill_id`),
    ADD INDEX `target_idx`(`user_id`, `date`, `bill_id`),
    ADD INDEX `target_sidx`(`target_id`);
ALTER TABLE `budget`
    ADD INDEX `start_idx` (`date_start`,`date_end`,`user_id`) USING BTREE,
    ADD INDEX `subs_idx` (`date_start`,`category`) USING BTREE;
ALTER TABLE `calendar`
    ADD INDEX `cal_idx`(`close`, `near_date`, `user_id`);
ALTER TABLE `system_categories`
    ADD INDEX `name_idx`(`name`);

