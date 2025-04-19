INSERT IGNORE INTO winteright.ratings (
    user_id, beatmap_id, score, created_at, updated_at
)
SELECT
    u.id, b.id, ROUND(r.Score * 2), r.date, r.date
FROM omdb_old.ratings r
    JOIN winteright.users u ON r.UserID = u.osu_id
    JOIN winteright.beatmaps b ON r.BeatmapID = b.beatmap_id;
