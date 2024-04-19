<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace Tests\Libraries\Search;

use App\Libraries\Search\ScoreSearchParams;
use App\Libraries\Search\UserSearch;
use App\Libraries\Search\UserSearchParams;
use App\Models\User;
use Tests\TestCase;

class ScoresSearchParamsTest extends TestCase
{
    /**
     * @dataProvider showLegacyForUserDataSource
     */
    public function testShowLegacyForGuest(?bool $legacyOnly, ?bool $isApiRequest, ?bool $expected)
    {
        $this->assertSame(
            $expected,
            ScoreSearchParams::showLegacyForUser(null, $legacyOnly, $isApiRequest)
        );
    }

    /**
     * @dataProvider showLegacyForUserDataSource
     */
    public function testShowLegacyForUser(?bool $legacyOnly, ?bool $isApiRequest, ?bool $expected)
    {
        $user = User::factory()->create();

        $this->assertSame(
            $expected,
            ScoreSearchParams::showLegacyForUser($user, $legacyOnly, $isApiRequest)
        );
    }

    /**
     * @dataProvider showLegacyForUserSettingDataSource
     */
    public function testShowLegacyForUserSetting(?bool $setting, ?bool $expected)
    {
        $user = User::factory()->create();

        if ($setting !== null) {
            $user->userProfileCustomization()->create([
                'legacy_score_only' => $setting
            ]);
        }

        $this->assertSame(
            $expected,
            ScoreSearchParams::showLegacyForUser($user, null, null)
        );
    }

    public function showLegacyForGuestDataSource()
    {
        return [
            [null, null, true],
            [null, false, true],
            [null, true, null],
            [false, null, null],
            [false, false, null],
            [false, true, null],
            [true, null, true],
            [true, false, true],
            [true, true, true],
        ];
    }

    public function showLegacyForUserDataSource()
    {
        return [
            [null, null, true],
            [null, false, true],
            [null, true, null],
            [false, null, null],
            [false, false, null],
            [false, true, null],
            [true, null, true],
            [true, false, true],
            [true, true, true],
        ];
    }

    public function showLegacyForUserSettingDataSource()
    {
        return [
            [null, true],
            [false, null],
            [true, true],
        ];
    }
}
