<?php

return [
    'indexable' => env('SITE_INDEXABLE', false),
    'contact_email' => 'admin@idlaps.com',
    'events' => [
        [
            'slug' => 'ini-run-fest-2026', 'name' => 'INI RUN FEST 2026', 'sport' => 'Running', 'year' => '2026', 'date' => '28 Juni 2026',
            'location' => 'AEON Mall Deltamas, Cikarang', 'image' => null, 'categories' => ['5K', 'Kid Dash'],
            'description' => 'INI RUN FEST 2026 di AEON Mall Deltamas, Cikarang. Dipersembahkan oleh Pengda Kabupaten Bekasi Ikatan Notaris Indonesia dan diselenggarakan oleh JBR Race Management.',
            'result_url' => 'https://inirun.idlaps.com/results.html', 'article_url' => null, 'featured' => false,
        ],
        [
            'slug' => 'cosmo-run-2026', 'name' => 'Cosmo Run 2026', 'sport' => 'Running', 'year' => '2026', 'date' => '1 Februari 2026',
            'location' => 'District 1 Meikarta', 'image' => '/media/cosmo.webp', 'categories' => ['Running event'],
            'description' => 'IDLAPS berkolaborasi dengan JBR Race Management dalam penyelenggaraan Cosmo Run 2026 di District 1 Meikarta. Dokumentasi dan liputan event tersedia di IDLAPS Sport News.',
            'result_url' => 'https://cosmorun.idlaps.com',
            'article_url' => 'https://www.idlaps.com/berita/cosmo-run-2026-sukses-digelar-di-district-1-meikarta-kolaborasi-idlaps-dan-jbr-race-management/', 'featured' => true,
        ],
        [
            'slug' => 'starfit-fun-run-2025', 'name' => 'Starfit Fun Run 2025', 'sport' => 'Running', 'year' => '2025', 'date' => '26 Oktober 2025',
            'location' => 'Intermoda Cisauk, BSD', 'image' => '/media/starfit.webp', 'categories' => ['5K', '10K'],
            'description' => 'Starfit Fun Run mempertemukan komunitas lari dalam kategori 5K dan 10K. IDLAPS berkolaborasi dengan Starfit EO untuk mendukung penyelenggaraan event di BSD.',
            'result_url' => 'https://starfit-run.idlaps.com',
            'article_url' => 'https://www.idlaps.com/berita/idlaps-berkolaborasi-dengan-starfit-eo-menyukseskan-starfit-fun-run-2025-bsd/', 'featured' => true,
        ],
        [
            'slug' => 'tkg-half-marathon-2024', 'name' => 'TKG Half Marathon 2024', 'sport' => 'Running', 'year' => '2024', 'date' => '2024',
            'location' => 'Bandar Lampung, Lampung', 'image' => '/media/tkg.jpg', 'categories' => ['Half marathon'],
            'description' => 'Dokumentasi layanan IDLAPS pada TKG Half Marathon 2024 di Bandar Lampung. Hasil event dapat ditemukan melalui arsip result IDLAPS.',
            'result_url' => 'https://idlaps.com/#section_4', 'article_url' => null, 'featured' => true,
        ],
        [
            'slug' => 'bukit-batu-2023', 'name' => 'Bukit Batu 2023', 'sport' => 'Cycling', 'year' => '2023', 'date' => '2023',
            'location' => 'Bukit Batu JPG MTB Trail Park', 'image' => '/media/bukit-batu.jpg', 'categories' => ['MTB'],
            'description' => 'Dari lintasan tanah hingga garis finis. Dokumentasi event sepeda di Bukit Batu JPG MTB Trail Park, bagian dari perjalanan IDLAPS mendukung berbagai format olahraga.',
            'result_url' => null, 'article_url' => null, 'featured' => false,
        ],
    ],
];
