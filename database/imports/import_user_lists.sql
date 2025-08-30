INSERT IGNORE INTO winteright.user_lists (
    id, name, description, user_id, is_public, created_at, updated_at
)
SELECT
    ListID, Title, Description, UserId, true, CreatedAt, UpdatedAt
FROM omdb_old.lists;
