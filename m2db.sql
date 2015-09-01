-- Create Database, Tables, Stored Routines and Jobs
create database IF NOT EXISTS m2db;
use m2db;
CREATE TABLE IF NOT EXISTS status (
  host_name varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '',
  variable_name varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '',
  variable_value varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  timest timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE status
 ADD unique KEY idx01 (host_name,variable_name,timest);

-- Delete old statistics (older than 60 days)
DROP PROCEDURE IF EXISTS collect_daily_stats;
DELIMITER // ;
CREATE PROCEDURE collect_daily_stats()
BEGIN
DECLARE a datetime;
set a=now();
delete from m2db.status where timest < date_sub(now(), INTERVAL 60 DAY);
END //
DELIMITER ; //

-- The event scheduler must also be activated in the my.cnf (event_scheduler=1)
set global event_scheduler=1;
CREATE EVENT collect_daily_stats
    ON SCHEDULE EVERY 1 DAY
    DO call collect_daily_stats();

-- Use a specific user (suggested)
grant all on m2db.* to m2db@'%';
grant process on *.* to m2db@'%';
grant select on performance_schema.* to m2db@'%';
set password for m2db@'%'=password('Ple4seCh4ngeMe!');
