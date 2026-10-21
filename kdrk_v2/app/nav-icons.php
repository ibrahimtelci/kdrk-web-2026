<?php
/*
 * Alt menü ve faaliyet takvimi simgeleri: 24'lük ızgara, ince çizgi; renk metinden gelir (CLAUDE.md §1).
 * Dizi değeri <svg> içeriğidir; menü (partials/nav.php) ve takvim (pages/activities/index.php) aynı kaynağı kullanır.
 */
return [
    'info'      => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
    'users'     => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/>',
    'award'     => '<circle cx="12" cy="9" r="6"/><path d="M8.5 14 7 22l5-3 5 3-1.5-8"/>',
    'calendar'  => '<rect x="3.5" y="5" width="17" height="15.5" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
    'trail'     => '<circle cx="6" cy="18" r="2"/><circle cx="18" cy="6" r="2"/><path d="M8 18h7a3 3 0 0 0 0-6H9a3 3 0 0 1 0-6h7"/>',
    'mountain'  => '<path d="M2.5 20 9 8l4 7 2.5-4 6 9z"/><path d="m7.4 11 1.6 1.5 1.5-1.4"/>',
    'carabiner' => '<rect x="7" y="2.5" width="10" height="19" rx="5"/><path d="M12 7v6"/>',
    'ski'       => '<path d="M4 21 20 5M8.5 21.5 21 9M14 4.5a1.8 1.8 0 1 0 0-.1"/>',
    'book'      => '<path d="M4 5a2 2 0 0 1 2-2h13v15H6a2 2 0 0 0-2 2zM4 20a2 2 0 0 0 2 2h13v-4"/>',
    'chat'      => '<path d="M4 5h12v9H9l-4 3.5V14H4z"/><path d="M16 9h4v8.5h-1V20l-3-2.5h-4.5V14"/>',
    'lifebuoy'  => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5"/><path d="m5.6 5.6 3.9 3.9M14.5 14.5l3.9 3.9M18.4 5.6l-3.9 3.9M9.5 14.5l-3.9 3.9"/>',
    'card'      => '<rect x="2.5" y="5" width="19" height="14" rx="2"/><circle cx="8.5" cy="11" r="2"/><path d="M5.5 16a3 3 0 0 1 6 0M14 10h4.5M14 13.5h3"/>',
    'license'   => '<path d="M6 2.5h8.5L19 7v14.5H6z"/><path d="M14 2.5V7h5M9 15l2 2 4-4.5"/>',
    'sun'       => '<circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M2.5 12h2M19.5 12h2M5.3 5.3l1.4 1.4M17.3 17.3l1.4 1.4M5.3 18.7l1.4-1.4M17.3 6.7l1.4-1.4"/>',
    'snow'      => '<path d="M12 2.5v19M3.8 7.3l16.4 9.4M3.8 16.7l16.4-9.4M9.5 4l2.5 2 2.5-2M9.5 20l2.5-2 2.5 2"/>',
    'rope'      => '<path d="M12 2.5c-4 3 4 5 0 8s4 5 0 8v3"/>',
    'ropes'     => '<path d="M8.5 2.5c-3.5 3 3.5 5 0 8s3.5 5 0 8v3M15.5 2.5c-3.5 3 3.5 5 0 8s3.5 5 0 8v3"/>',
];
