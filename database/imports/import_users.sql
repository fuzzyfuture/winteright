INSERT INTO winteright.users (
    osu_id, name, banned, weight, hide_ratings,
    bio, title, last_seen_at, ip_address,
    created_at, updated_at
)
SELECT
    UserID, Username, COALESCE(banned, 0), Weight, COALESCE(HideRatings, 0),
    CustomDescription, UserTitle, NULL, NULL,
    NOW(), NOW()
FROM omdb_old.users;

INSERT INTO winteright.rating_labels (
    user_id,
    rating_0_0, rating_0_5, rating_1_0, rating_1_5, rating_2_0,
    rating_2_5, rating_3_0, rating_3_5, rating_4_0, rating_4_5, rating_5_0,
    created_at, updated_at
)
SELECT
    u.id,
    o.Custom00Rating, o.Custom05Rating, o.Custom10Rating, o.Custom15Rating, o.Custom20Rating,
    o.Custom25Rating, o.Custom30Rating, o.Custom35Rating, o.Custom40Rating, o.Custom45Rating, o.Custom50Rating,
    NOW(), NOW()
FROM omdb_old.users o
         JOIN winteright.users u ON o.UserID = u.osu_id;
