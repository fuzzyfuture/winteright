INSERT IGNORE INTO winteright.user_list_items (
    id, list_id, item_type, item_id, description, `order`, created_at, updated_at
)
SELECT
    NULL, ListID,
    CASE
        WHEN Type = 'person' THEN 'App\\Models\\User'
        WHEN Type = 'beatmap' THEN 'App\\Models\\Beatmap'
        WHEN Type = 'beatmapset' THEN 'App\\Models\\BeatmapSet'
        ELSE Type
    END,
    SubjectID, Description, `order`, now(), now()
FROM omdb_old.list_items;

