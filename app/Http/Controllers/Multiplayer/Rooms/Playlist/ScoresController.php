<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Http\Controllers\Multiplayer\Rooms\Playlist;

use App\Exceptions\InvariantException;
use App\Http\Controllers\Controller as BaseController;
use App\Libraries\DbCursorHelper;
use App\Libraries\Multiplayer\Mod;
use App\Models\Build;
use App\Models\Multiplayer\PlaylistItem;
use App\Models\Multiplayer\PlaylistItemUserHighScore;
use App\Models\Multiplayer\Room;
use App\Transformers\Multiplayer\ScoreTransformer;
use Carbon\Carbon;

class ScoresController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('require-scopes:public', ['only' => ['index']]);
    }

    /**
     * Get Scores
     *
     * @group Multiplayer
     *
     * Returns a list of scores for specified playlist item.
     *
     * ---
     *
     * ### Response Format
     *
     * Returns [MultiplayerScores](#multiplayerscores) object.
     *
     * @urlParam room required Id of the room.
     * @urlParam playlist required Id of the playlist item.
     *
     * @queryParam limit Number of scores to be returned.
     * @queryParam sort [MultiplayerScoresSort](#multiplayerscoressort) parameter.
     * @queryParam cursor [MultiplayerScoresCursor](#multiplayerscorescursor) parameter.
     */
    public function index($roomId, $playlistId)
    {
        $playlist = PlaylistItem::where('room_id', $roomId)->where('id', $playlistId)->firstOrFail();
        $params = request()->all();
        $cursorHelper = new DbCursorHelper(
            PlaylistItemUserHighScore::SORTS,
            PlaylistItemUserHighScore::DEFAULT_SORT,
            get_string($params['sort'] ?? null)
        );

        $sort = $cursorHelper->getSort();
        $cursor = $cursorHelper->prepare($params['cursor'] ?? null);
        $limit = clamp(get_int($params['limit'] ?? null) ?? 50, 1, 50);

        $highScores = $playlist
            ->highScores()
            ->cursorSort($sort, $cursor)
            ->with(ScoreTransformer::BASE_PRELOAD)
            ->limit($limit + 1) // an extra to check for pagination
            ->get();

        $hasMore = count($highScores) === $limit + 1;
        if ($hasMore) {
            $highScores->pop();
        }
        $scoresJson = json_collection(
            $highScores->pluck('score'),
            'Multiplayer\Score',
            ScoreTransformer::BASE_INCLUDES
        );
        $total = $playlist->highScores()->count();

        $user = auth()->user();

        if ($user !== null) {
            $userHighScore = $playlist->highScores()->where('user_id', $user->getKey())->first();

            if ($userHighScore !== null) {
                $userScoreJson = json_item($userHighScore->score, 'Multiplayer\Score', ScoreTransformer::BASE_INCLUDES);
            }
        }

        return [
            'cursor' => $hasMore ? $cursorHelper->next($highScores) : null,
            'params' => ['limit' => $limit, 'sort' => $cursorHelper->getSortName()],
            'scores' => $scoresJson,
            'total' => $total,
            'user_score' => $userScoreJson ?? null,
        ];
    }

    /**
     * Get a Score
     *
     * @group Multiplayer
     *
     * Returns detail of specified score and the surrounding scores.
     *
     * ---
     *
     * ### Response Format
     *
     * Returns [MultiplayerScore](#multiplayerscore) object.
     *
     * @urlParam room required Id of the room.
     * @urlParam playlist required Id of the playlist item.
     * @urlParam score required Id of the score.
     */
    public function show($roomId, $playlistId, $id)
    {
        $room = Room::find($roomId) ?? abort(404, 'Invalid room id');
        $playlistItem = $room->playlist()->find($playlistId) ?? abort(404, 'Invalid playlist id');
        $score = $playlistItem->scores()->findOrFail($id);

        return json_item(
            $score,
            'Multiplayer\Score',
            array_merge(['position', 'scores_around'], ScoreTransformer::BASE_INCLUDES)
        );
    }

    /**
     * Get User High Score
     *
     * @group Multiplayer
     *
     * Returns detail of highest score of specified user and the surrounding scores.
     *
     * ---
     *
     * ### Response Format
     *
     * Returns [MultiplayerScore](#multiplayerscore) object.
     *
     * @urlParam room required Id of the room.
     * @urlParam playlist required Id of the playlist item.
     * @urlParam user required User id.
     */
    public function showUser($roomId, $playlistId, $userId)
    {
        $room = Room::find($roomId) ?? abort(404, 'Invalid room id');
        $playlistItem = $room->playlist()->find($playlistId) ?? abort(404, 'Invalid playlist id');
        $score = $playlistItem->highScores()->where('user_id', $userId)->firstOrFail()->score ?? abort(404);

        return json_item(
            $score,
            'Multiplayer\Score',
            array_merge(['position', 'scores_around'], ScoreTransformer::BASE_INCLUDES)
        );
    }

    public function store($roomId, $playlistId)
    {
        $room = Room::findOrFail($roomId);
        $playlistItem = $room->playlist()->where('id', $playlistId)->firstOrFail();
        $user = auth()->user();

        if ($user->findUserGroup(app('groups')->byIdentifier('admin'), true) === null) {
            $clientHash = presence(request('version_hash'));
            abort_if($clientHash === null, 422, 'missing client version');

            // temporary measure to allow android builds to submit without access to the underlying dll to hash
            if (strlen($clientHash) !== 32) {
                $clientHash = md5($clientHash);
            }

            Build::where([
                'hash' => hex2bin($clientHash),
                'allow_ranking' => true,
            ])->firstOrFail();
        }

        $score = $room->startPlay($user, $playlistItem);

        return json_item(
            $score,
            'Multiplayer\Score'
        );
    }

    public function update($roomId, $playlistId, $scoreId)
    {
        $room = Room::findOrFail($roomId);

        $playlistItem = $room->playlist()
            ->where('id', $playlistId)
            ->firstOrFail();

        $roomScore = $playlistItem->scores()
            ->where('user_id', auth()->user()->getKey())
            ->where('id', $scoreId)
            ->firstOrFail();

        try {
            $score = $room->completePlay(
                $roomScore,
                $this->extractScoreParams(request()->all(), $playlistItem)
            );

            return json_item(
                $score,
                'Multiplayer\Score',
                array_merge(['position', 'scores_around'], ScoreTransformer::BASE_INCLUDES)
            );
        } catch (InvariantException $e) {
            return error_popup($e->getMessage(), $e->getStatusCode());
        }
    }

    private function extractScoreParams(array $params, PlaylistItem $playlistItem)
    {
        $mods = Mod::parseInputArray(
            $params['mods'] ?? [],
            $playlistItem->ruleset_id
        );

        return [
            'rank' => $params['rank'] ?? null,
            'total_score' => get_int($params['total_score'] ?? null),
            'accuracy' => get_float($params['accuracy'] ?? null),
            'max_combo' => get_int($params['max_combo'] ?? null),
            'ended_at' => Carbon::now(),
            'passed' => get_bool($params['passed'] ?? null),
            'mods' => $mods,
            'statistics' => $params['statistics'] ?? null,
        ];
    }
}
