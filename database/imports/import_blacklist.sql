INSERT INTO winteright.blacklist (osu_id)
SELECT UserID
FROM omdb_old.blacklist;
