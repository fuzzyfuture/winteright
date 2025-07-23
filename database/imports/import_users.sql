INSERT INTO winteright.users (
    id, name, banned, weight, hide_ratings,
    bio, title, last_seen_at, ip_address,
    created_at, updated_at
)
SELECT
    UserID, Username, COALESCE(banned, 0), Weight, COALESCE(HideRatings, 0),
    CustomDescription, UserTitle, NULL, NULL,
    NOW(), NOW()
FROM omdb_old.users;
