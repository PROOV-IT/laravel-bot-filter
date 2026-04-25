<?php

return [
    'common' => [
        'watch' => 'Surveillance URL',
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
        'url_watch_detected' => [
            'subject' => 'Surveillance URL détectée : :path',
            'title' => 'Un nouvel incident URL a été enregistré.',
            'path' => 'Chemin : :path',
            'host' => 'Hôte : :host',
            'count' => 'Compteur : :count',
        ],
        'url_watch_digest' => [
            'subject' => 'Digest URL watcher',
            'period' => 'Fenêtre du digest : :hours heure(s)',
            'total' => 'Surveillances URL totales : :count',
            'pending' => 'En attente : :count',
            'reviewed' => 'Révisées : :count',
            'bots' => 'Classées bot : :count',
            'normal' => 'Classées normal : :count',
            'ignored' => 'Ignorées : :count',
        ],
    ],
];
