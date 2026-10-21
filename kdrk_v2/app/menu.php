<?php
/*
 * Ana menü — tek şablon (CLAUDE.md §3). Masaüstü menü ve mobil drawer bu diziden üretilir.
 * group: aktif üst öğeyi belirler (sayfanın 'nav' değeri). Bir öğe:
 *   [etiket, route adı, route parametreleri, simge] veya ['---', ara başlık]
 * Simge adları views/partials/nav.php içindeki $navIcons dizisindedir.
 */
return [
    ['group' => 'home', 'label' => 'Anasayfa', 'route' => 'home'],
    ['group' => 'kurumsal', 'label' => 'Kurumsal', 'items' => [
        ['Hakkımızda', 'hakkimizda', [], 'info'],
        ['Yönetim Kurulu', 'yonetim-kurulu', [], 'users'],
        ['Emeği Geçenler', 'emegi-gecenler', [], 'award'],
    ]],
    ['group' => 'faaliyetler', 'label' => 'Faaliyetler', 'items' => [
        ['Faaliyet Takvimi', 'faaliyetler', [], 'calendar'],
        ['---', 'Disiplinler'],
        ['Günübirlik Doğa Yürüyüşü', 'disiplin', ['slug' => 'gunubirlik'], 'trail'],
        ['Dağcılık', 'disiplin', ['slug' => 'dagcilik'], 'mountain'],
        ['Spor Tırmanış', 'disiplin', ['slug' => 'spor-tirmanis'], 'carabiner'],
        ['Dağ Kayağı', 'disiplin', ['slug' => 'dag-kayagi'], 'ski'],
        ['Eğitim Programı', 'disiplin', ['slug' => 'egitim'], 'book'],
        ['Sosyal-Kültürel', 'disiplin', ['slug' => 'sosyal-kulturel'], 'chat'],
        ['Arama Kurtarma', 'disiplin', ['slug' => 'arama-kurtarma'], 'lifebuoy'],
    ]],
    ['group' => 'galeri', 'label' => 'Fotoğraf Galerisi', 'route' => 'galeri'],
    ['group' => 'duyuru', 'label' => 'Duyuru & Haber', 'route' => 'duyurular'],
    ['group' => 'bilgi', 'label' => 'Bilgi Bankası', 'items' => [
        ['Kulüp Üyeliği', 'kulup-uyeligi', [], 'card'],
        ['Sporcu Lisansı', 'sporcu-lisansi', [], 'license'],
        ['---', 'Eğitim Notları'],
        ['Yaz Dağcılık', 'yaz-dagcilik', [], 'sun'],
        ['Kış Dağcılık', 'kis-dagcilik', [], 'snow'],
        ['Tek İp Boylu', 'tek-ip-boylu', [], 'rope'],
        ['Çok İp Boylu', 'cok-ip-boylu', [], 'ropes'],
    ]],
    ['group' => 'iletisim', 'label' => 'İletişim', 'route' => 'iletisim'],
];
