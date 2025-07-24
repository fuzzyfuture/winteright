INSERT INTO winteright.beatmap_sets (
    id, creator_id, date_ranked, genre, lang,
    artist, title, has_storyboard, has_video, created_at, updated_at
)
SELECT
    SetID, CreatorID, DateRanked, Genre, Lang,
    Artist, Title, HasStoryboard, HasVideo, NOW(), NOW()
FROM omdb_old.beatmapsets;

INSERT INTO winteright.beatmaps (
    id, set_id, difficulty_name, mode, status, sr,
    weighted_avg,
    blacklisted, blacklist_reason, created_at, updated_at
)
SELECT
    BeatmapID, SetID, DifficultyName, Mode, Status, SR,
    WeightedAvg,
    Blacklisted, BlacklistReason, NOW(), NOW()
FROM omdb_old.beatmaps;
