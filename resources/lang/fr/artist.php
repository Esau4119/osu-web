<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

return [
    'page_description' => 'Featured Artists sur osu!',
    'title' => 'Featured Artists',

    'admin' => [
        'hidden' => 'L\'ARTISTE EST ACTUELLEMENT MASQUÉ',
    ],

    'beatmaps' => [
        '_' => 'Beatmaps',
        'download' => 'Télécharger une beatmap type de cette musique',
        'download-na' => 'La beatmap type n\'est pas encore disponible',
    ],

    'index' => [
        'description' => 'Les Featured Artists sont des artistes avec qui nous collaborons pour apporter des musiques nouvelles et originales à osu!. Ces artistes ainsi qu\'une sélection de leurs musiques ont été sélectionnés par l\'équipe d\'osu! pour leur qualité et leur potentiel pour le mapping. Quelques-uns de ces artistes ont également créé des musiques exclusivement pour osu!.<br><br>Toutes les musiques de cette section sont fournies avec des fichiers .osz pré-timés et peuvent officiellement être utilisées sur osu! ainsi que tout contenu relatif à osu!.',
    ],

    'links' => [
        'beatmaps' => 'Beatmaps osu!',
        'osu' => 'Profil osu!',
        'site' => 'Site officiel',
    ],

    'songs' => [
        '_' => 'Titres',
        'count' => ':count titre|:count titres',
        'original' => 'osu! original',
        'original_badge' => 'ORIGINAL',
    ],

    'tracklist' => [
        'title' => 'titre',
        'length' => 'durée',
        'bpm' => 'bpm',
        'genre' => 'genre',
    ],

    'tracks' => [
        'index' => [
            '_' => 'recherche de titres',

            'exclusive_only' => [
                'all' => 'Tout',
                'exclusive_only' => 'osu! original',
            ],

            'form' => [
                'advanced' => 'Recherche Avancée',
                'album' => 'Album',
                'artist' => 'Artiste',
                'bpm_gte' => 'BPM min.',
                'bpm_lte' => 'BPM max.',
                'empty' => 'Aucune musique correspondant aux critères de recherche n\'a été trouvée.',
                'exclusive_only' => 'Type',
                'genre' => 'Genre',
                'genre_all' => 'Tous',
                'length_gte' => 'Durée min.',
                'length_lte' => 'Durée max.',
            ],
        ],
    ],
];
