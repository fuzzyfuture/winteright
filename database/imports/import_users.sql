INSERT INTO winteright.users (
    id, name, banned, hide_ratings,
    bio, title,
    created_at, updated_at
)
SELECT
    UserID, Username, COALESCE(banned, 0), COALESCE(HideRatings, 0),
    CustomDescription, UserTitle,
    NOW(), NOW()
FROM omdb_old.users;
