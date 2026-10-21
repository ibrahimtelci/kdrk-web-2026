# Kullanıcı ve görevli birleştirme paketi

Durum: 20 Ekim 2026 tarihinde yerel kdrk_web_v2 üzerinde başarıyla uygulandı. Paket yeniden çalıştırılmamalıdır.

Proje kökünde PowerShell:

```powershell
& ".\tools_v2\user-directory-update\Apply.ps1"
```

Betik yalnızca yerel `kdrk_web_v2` üzerinde çalışır. Kaynak hashlerini kontrol eder, dosyaları yedekler, 009 migration'ını uygular ve başarılı olursa yeni dosyaları kopyalar. Migration başarısızsa uygulama dosyaları değişmez. Paket tek seferliktir; dosyalar değiştikten sonra tekrar uygulanmaz.

- Cep telefonu kullanıcı ekleme, düzenleme ve listesine eklendi.
- Görevli rolü yalnızca atanmış faaliyetleri ve katılımcıları görür; başvuruları onaylar, reddeder veya yedeğe alır. Faaliyet içeriğini, kullanıcıları ve diğer modülleri yönetemez.
- Yetki kontrolü liste, faaliyet detayı, başvuru listesi, başvuru güncelleme ve yazdırma isteklerinde uygulanır. TC ve sağlık bilgileri Admin rolünde kalır.
- Eski rehber kayıtları ayrı, girişe kapalı kullanıcı kayıtlarına dönüşür. E-posta/parola üretilmez. Admin gerçek e-posta ve parola tanımlayıp hesabı etkinleştirmelidir. Aynı adlı mevcut bir kullanıcıya otomatik birleştirme yapılmaz.
- Eski rehber tablosu ve bağlantıları korunur; yeni bağlantılar kullanıcı kimliklerini kullanır. Eski faaliyetlerdeki rehber atamaları taşınır.
- İletişim Bilgileri menüsü kaldırılır; eski adresler admin için Kullanıcılar'a yönlenir. Yeni kişi ekleme ayrı rehber kaydı oluşturmaz.
- Yeni atamalarda telefonu olan aktif Admin, Editör veya Görevli seçilebilir. Önceden atanmış pasif kişi bilgisi geçmiş kayıtta korunur.

Kontroller: 29 yetki/telefon kontrolü ve paket PHP sözdizimi kontrolleri geçti. Gerçek veritabanında migration, kullanıcı kaydetme, görevli oturumu ve uçtan uca başvuru onayı henüz doğrulanamadı.
