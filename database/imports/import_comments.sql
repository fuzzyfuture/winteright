INSERT INTO winteright.comments (
    user_id, beatmap_set_id, content, created_at, updated_at
)
SELECT
    wu.id, wb.id, oc.Comment, oc.date, oc.date
FROM omdb_old.comments oc
    JOIN winteright.users wu ON wu.id = oc.UserID
    JOIN winteright.beatmap_sets wb ON wb.id = oc.SetID;
