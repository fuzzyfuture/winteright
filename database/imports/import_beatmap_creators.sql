INSERT INTO winteright.beatmap_creators (beatmap_id, creator_id)
SELECT BeatmapID, CreatorID
FROM omdb_old.beatmap_creators
WHERE BeatmapID IN (SELECT beatmap_id FROM winteright.beatmaps);
