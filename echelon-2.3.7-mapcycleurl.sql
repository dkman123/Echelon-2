# add mapcycleurl column to servers table

# req's a version higher than what i'm running (maria 10.219 vs mysql 8.0.39)
#ALTER TABLE ech_servers ADD COLUMN IF NOT EXISTS mapcycleurl VARCHAR(255);

SELECT count(*)
INTO @exist
FROM information_schema.columns
WHERE table_schema = 'echelon'
and COLUMN_NAME = 'mapcycleurl'
AND table_name = 'ech_servers' LIMIT 1;

set @query = IF(@exist <= 0, 'ALTER TABLE echelon.`ech_servers`  ADD COLUMN `mapcycleurl` MEDIUMTEXT NULL',
'select \'Column Exists\' status');

prepare stmt from @query;

EXECUTE stmt;
