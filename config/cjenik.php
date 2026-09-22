<?php

// Jedini izvor istine za cijene planova na javnoj stranici (#cijene).
// Koristi ga i welcome.blade.php (kartice + online cjenik tablica) i ruta /cjenik.csv,
// tako da prikazana cijena i sidrena cijena nikad ne mogu izaći iz sinkronizacije.
return [
    'azurirano' => '2026-09-22',

    'planovi' => [
        [
            'naziv' => 'Starter',
            'cijena_prikaz' => '0 €',
            'cijena_csv' => '0.00',
            'jedinica' => 'zauvijek',
        ],
        [
            'naziv' => 'Pro',
            'cijena_prikaz' => '4,99 €',
            'cijena_csv' => '4.99',
            'jedinica' => 'mjesec',
        ],
        [
            'naziv' => 'Business',
            'cijena_prikaz' => '12,99 €',
            'cijena_csv' => '12.99',
            'jedinica' => 'mjesec',
        ],
    ],
];
