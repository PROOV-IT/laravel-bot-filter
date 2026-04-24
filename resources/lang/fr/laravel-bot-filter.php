<?php

return [
    'common' => [
        'probe' => 'Sonde',
    ],
    'enums' => [
        'classification' => [
            'unknown' => 'Inconnu',
            'bot' => 'Bot',
            'normal' => 'Normal',
            'ignored' => 'Ignoré',
            'whitelisted' => 'Autorisé',
        ],
        'status' => [
            'pending' => 'En attente',
            'notified' => 'Notifié',
            'reviewed' => 'Révisé',
            'archived' => 'Archivé',
        ],
    ],
    'notifications' => [
        'bot_probe_detected' => [
            'subject' => 'Sonde bot détectée : :path',
            'title' => 'Une nouvelle alerte bot/sonde a été enregistrée.',
            'path' => 'Chemin : :path',
            'host' => 'Hôte : :host',
            'count' => 'Compteur : :count',
        ],
    ],
];
