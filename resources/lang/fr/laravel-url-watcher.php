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
            'events_total' => 'Événements totaux : :count',
            'events_delta' => 'Delta d’événements vs fenêtre précédente : :count (précédente : :previous)',
            'unique_hosts' => 'Hôtes uniques : :count',
            'new_watches' => 'Nouvelles surveillances sur la fenêtre : :count',
            'write_attempts' => 'Tentatives d’écriture : :count',
            'client_errors' => 'Erreurs client : :count',
            'server_errors' => 'Erreurs serveur : :count',
            'pending' => 'En attente : :count',
            'reviewed' => 'Révisées : :count',
            'bots' => 'Classées bot : :count',
            'normal' => 'Classées normal : :count',
            'ignored' => 'Ignorées : :count',
            'largest_host_spike' => 'Plus forte hausse par hôte : :label (courant : :current, précédent : :previous, delta : :delta)',
            'top_paths_heading' => 'Top chemins',
            'top_hosts_heading' => 'Top hôtes',
            'top_methods_heading' => 'Top méthodes',
            'top_statuses_heading' => 'Top statuts HTTP',
            'new_paths_heading' => 'Nouveaux chemins vus sur cette fenêtre',
            'new_path' => ':label',
            'recent_events_heading' => 'Événements récents',
            'row' => ':label - :count',
            'recent_event' => ':occurred_at | :method | :host | :path | :status',
        ],
    ],
];
