INSERT INTO winteright.blacklist (user_id)
SELECT UserID
FROM omdb_old.blacklist;
