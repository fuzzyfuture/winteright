INSERT IGNORE INTO winteright.user_lists (
    id, name, description, user_id
)
SELECT
    NULL, Title, Description, UserId
FROM omdb_old.lists
