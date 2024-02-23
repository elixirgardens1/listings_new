DROP TABLE `notes`;

CREATE TABLE `notes`(
    `cat_id` TEXT,
    `group_` TEXT,
    `note` TEXT,
    `source` TEXT,
    `timestamp` INT
);

INSERT INTO `notes` VALUES ('a8','0','Test note for Aggregates/Aggregates - Group 1 (ebay)','e','1708687414');
INSERT INTO `notes` VALUES ('a8','1','Test note for Aggregates/Aggregates - Group 2 (ebay)','e','1708692726');
INSERT INTO `notes` VALUES ('a8','0','Test note for Aggregates/Aggregates - Group 1 (amazon)','a','1708692765');