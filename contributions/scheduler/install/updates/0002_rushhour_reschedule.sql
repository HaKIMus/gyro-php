ALTER TABLE `scheduler` 
 MODIFY COLUMN `reschedule_error` ENUM('TERMINATOR1','TERMINATOR2','TERMINATOR3','DIEHARD1','DIEHARD2','DIEHARD3','24HOURS','RUSHHOUR1','RUSHHOUR2','RUSHHOUR3') NOT NULL DEFAULT 'TERMINATOR1',
 MODIFY COLUMN `reschedule_success` ENUM('TERMINATOR1','TERMINATOR2','TERMINATOR3','DIEHARD1','DIEHARD2','DIEHARD3','24HOURS','RUSHHOUR1','RUSHHOUR2','RUSHHOUR3') NOT NULL DEFAULT 'TERMINATOR1';
