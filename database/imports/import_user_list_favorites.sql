INSERT IGNORE user_list_favorites (id, list_id, user_id, created_at, updated_at)
SELECT HeartID, ListID, UserID, now(), now()
FROM omdb_old.list_hearts;
