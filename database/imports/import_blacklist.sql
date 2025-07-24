INSERT INTO winteright.blacklist (user_id, created_at, updated_at)
SELECT UserID, NOW(), NOW()
FROM omdb_old.blacklist;
