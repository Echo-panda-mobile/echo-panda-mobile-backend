# Echo Panda User Adaptive Recommendation Guide

This document explains the implemented recommendation logic step by step, from user actions to personalized results.

## 1. System Goal

Echo Panda recommendation has two connected goals:

1. Recommend songs the user is likely to enjoy right now.
2. Adapt automatically as the user behavior changes over time.

This is done with rule-based scoring (not machine learning), which is suitable for FYP scope and easy to defend.

## 2. High-Level Flow

```text
User Action
  -> Activity Tracking
  -> Preference Update
  -> Candidate Generation
  -> Recommendation Scoring
  -> Ranked Results API
  -> Frontend Display
  -> Recommendation Event Analytics
```

## 3. Data Sources Used

The recommendation engine uses existing platform data:

- `songs`
- `user_listen_history`
- `play_history`
- `favorites`
- `playlist_song`
- `user_preferences`
- `genres`
- `tags`

## 4. Step-by-Step Adaptive Logic

### Step 1: User performs an action

Examples:

- play song
- complete playback
- skip early
- favorite song
- add song to playlist

### Step 2: Backend records the action

Main tracking routes/services:

- `POST /api/listen-history`
- `POST /api/playback/progress`
- `POST /api/playback/complete`
- `POST /api/favorites/songs`
- `POST /api/playlists/{playlist}/songs`

### Step 3: Preference scores are updated

`UserPreferenceService` updates preference dimensions:

- genre
- mood
- artist
- tag

Current score updates:

- play base: `+1`
- half-listen bonus: `+2`
- completion bonus: `+2`
- favorite: `+10`
- playlist add: `+8`
- quick skip (<= 15s): `-5`

Score floor is `0` (no negative totals).

### Step 4: Preferences are stored in `user_preferences`

Each record has:

- `user_id`
- `preference_type` (`genre`, `mood`, `artist`, `tag`)
- `preference_value`
- `preference_score`

Unique key:

- `(user_id, preference_type, preference_value)`

### Step 5: Daily decay keeps profile fresh

A scheduled Laravel job runs daily:

- Job: `DecayUserPreferenceScores`
- Rule: reduce each score by 1% (`score = floor(score * 0.99)`)

Purpose:

- old taste fades naturally
- new behavior gets more influence over time

### Step 6: Candidate songs are generated

Engine does not score all songs. It first builds a candidate pool from top preferences:

- top genres
- top moods
- top artists
- top tags

Then excludes very recent songs from listen history to avoid repetition.

### Step 7: Candidate songs are scored

Current weighted formula:

- artist score: 35%
- genre score: 25%
- mood score: 20%
- tag score: 10%
- popularity score: 10%

If song is already favorited by the user, extra boost is applied.

### Step 8: Explainable reason is generated

Each recommendation includes human-readable reason text, for example:

- "Because you frequently listen to The Weeknd"
- "Because you often enjoy Pop music"
- "Because your listening mood is often Energetic"
- fallback: popularity reason

### Step 9: API returns ranked results

Main endpoint:

- `GET /api/recommendations?limit=20`

Response includes:

- `id`
- `title`
- `recommendation_score`
- `recommendation_reason`
- detailed component scores
- serialized song object

### Step 10: Recommendation analytics are tracked

Event types tracked:

- `recommendation_shown`
- `recommendation_clicked`
- `recommendation_played`
- `recommendation_skipped`

Stored in `recommendation_events` table for quality measurement and future tuning.

## 5. Additional Recommendation Endpoints

### Similar songs

- `GET /api/recommendations/similar/{songId}`

Similarity signals:

- same artist
- same genre
- same mood
- same tag

### Cold start

- `GET /api/recommendations/cold-start`

Fallback priority:

1. trending songs
2. recently added songs
3. editor picks

## 6. Caching and Performance

To keep API fast, recommendation responses are cached for 15 minutes.

Also, DB indexes were added for recommendation-heavy queries on:

- songs active/category/artist/mood/tag/play_count
- user preferences user/type/score
- recommendation events analytics paths

## 7. Frontend Integration Summary

Integrated surfaces:

- Home page
: Recommended For You, Continue Listening, Trending Now
- Discover page
: backend adaptive recommendations with load-more behavior
- Song Detail page
: "More Like This" section from similar endpoint

## 8. Admin Analytics

Admin page:

- `/admin/analytics/recommendations`

Shows:

- total recommendations served
- click rate
- play rate
- top songs
- most successful reasons
- daily shown/clicked/played trend

## 9. Defense-Ready Explanation (Short)

Echo Panda uses a rule-based adaptive recommendation engine. User interactions (plays, completion, skips, favorites, and playlist adds) continuously update a preference profile across genre, mood, artist, and tag. The recommender generates candidate songs from dominant preferences, scores candidates with weighted ranking, returns explainable recommendation reasons, and applies daily score decay so recommendations shift as user taste evolves.

## 10. Future Enhancements

- A/B test multiple scoring weight profiles
- Add collaborative filtering layer
- Use recommendation events for automatic weight tuning
- Increase automated coverage toward 80%+
