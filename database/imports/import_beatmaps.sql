INSERT INTO winteright.beatmap_sets (
    set_id, creator_id, status, date_ranked, genre, lang,
    artist, title, has_storyboard, has_video, created_at, updated_at
)
SELECT
    SetID, CreatorID, Status, DateRanked, Genre, Lang,
    Artist, Title, HasStoryboard, HasVideo, NOW(), NOW()
FROM omdb_old.beatmapsets;

INSERT INTO winteright.beatmaps (
    beatmap_id, set_id, difficulty_name, mode, status, sr,
    rating, chart_rank, chart_year_rank, rating_count, weighted_avg,
    blacklisted, blacklist_reason, controversy, created_at, updated_at
)
SELECT
    BeatmapID, SetID, DifficultyName, Mode, Status, SR,
    Rating, ChartRank, ChartYearRank, RatingCount, WeightedAvg,
    Blacklisted, BlacklistReason, controversy, NOW(), NOW()
FROM omdb_old.beatmaps;
