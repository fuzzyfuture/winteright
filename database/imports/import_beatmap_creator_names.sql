INSERT INTO winteright.beatmap_creator_names (id, name)
SELECT UserID, Username
FROM omdb_old.mappernames
WHERE UserID NOT IN (SELECT id FROM winteright.users)
AND UserID > 0
AND Username IS NOT NULL;
