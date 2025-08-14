INSERT IGNORE INTO winteright.user_lists (
    id, name, description, user_id, created_at, updated_at
)
SELECT
    ListID, Title, Description, UserId, CreatedAt, UpdatedAt
FROM omdb_old.lists;
