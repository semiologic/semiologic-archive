
ALTER TABLE packages DROP PRIMARY KEY;
ALTER TABLE packages ADD COLUMN package_id int NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE packages ADD UNIQUE INDEX type_package ( type, package );