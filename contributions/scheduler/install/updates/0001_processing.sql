ALTER TABLE `scheduler` 
	MODIFY COLUMN `status` ENUM('ACTIVE','DISABLED','ERROR','RESCHEDULED','PROCESSING') NOT NULL DEFAULT 'ACTIVE';

