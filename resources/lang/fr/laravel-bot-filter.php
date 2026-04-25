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
        'bot_probe_digest' => [
            'subject' => 'Digest des sondes bot',
            'period' => 'Fenêtre du digest : :hours heure(s)',
            'total' => 'Sondes totales : :count',
            'pending' => 'En attente : :count',
            'reviewed' => 'Révisées : :count',
            'bots' => 'Classées bot : :count',
            'normal' => 'Classées normal : :count',
            'ignored' => 'Ignorées : :count',
        ],
    ],
];
