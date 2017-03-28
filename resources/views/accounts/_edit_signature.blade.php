{{--
    Copyright 2015-2017 ppy Pty. Ltd.

    This file is part of osu!web. osu!web is distributed with the hope of
    attracting more community contributions to the core ecosystem of osu!.

    osu!web is free software: you can redistribute it and/or modify
    it under the terms of the Affero GNU General Public License version 3
    as published by the Free Software Foundation.

    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
--}}
{!! Form::open([
    'route' => 'account.update',
    'method' => 'PUT',
    'data-remote' => true,
    'class' => 'account-edit'
]) !!}
    <div class="account-edit__section">
        <h2 class="account-edit__section-title">
            {{ trans('accounts.edit.signature.title') }}
        </h2>
    </div>

    <div class="account-edit__input-groups">
        <div class="account-edit__input-group">

            <div class="account-edit-entry account-edit-entry--submit js-post-preview--body">
                {!! bbcode(Auth::user()->user_sig, Auth::user()->user_sig_bbcode_uid) !!}
            </div>

            <label class="account-edit-entry account-edit-entry--submit">
                <textarea
                    class="account-edit-entry__input js-post-preview--auto"
                    name="user[user_sig]"
                >{{ bbcode_for_editor(Auth::user()->user_sig, Auth::user()->user_sig_bbcode_uid) }}</textarea>
            </label>
        </div>

        <div class="account-edit__input-group">
            <div class="account-edit-entry account-edit-entry--submit js-parent-focus">
                <button class="btn-osu-big btn-osu-big--account-edit" type="submit">
                    <div class="btn-osu-big__content">
                        <div class="btn-osu-big__left">
                            {{ trans('accounts.edit.signature.update') }}
                        </div>

                        <div class="btn-osu-big__icon">
                            <i class="fa fa-check"></i>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </div>
{!! Form::close() !!}
