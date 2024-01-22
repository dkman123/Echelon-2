# add the Server Actions permission

INSERT INTO ech_permissions(id,name,description)
SELECT (SELECT max(id) +1 FROM ech_permissions), 'server_action', 'Allows user to perform Server Actions'
WHERE NOT EXISTS (SELECT * FROM ech_permissions WHERE name = 'server_action');

# to see if a column exists
#select if(count(*) > 0, true, false) into _exists from information_schema.COLUMNS c WHERE c.TABLE_SCHEMA = 'echelon' and c.TABLE_NAME = 'ech_permissions' and c.COLUMN_NAME = 'name';
