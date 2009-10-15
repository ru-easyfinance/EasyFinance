ALTER TABLE `accounts` 
    ADD INDEX `user_idx`(`user_id`);
ALTER TABLE `calendar` 
    ADD INDEX `del_per_idx`(`user_id`, `chain`, `near_date`);
ALTER TABLE `registration` 
    ADD INDEX `user_idx`(`user_id`),
    ADD INDEX `reg_idx`(`reg_id`),
    ADD INDEX `date_idx`(`date`);
