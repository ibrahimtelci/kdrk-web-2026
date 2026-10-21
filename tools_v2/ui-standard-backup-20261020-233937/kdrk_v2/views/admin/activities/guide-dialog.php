<dialog class="guide-dialog" data-guide-dialog aria-labelledby="guide-dialog-title">
<form method="post" action="<?= e(url('admin.contacts.store')) ?>" data-guide-form>
<?= csrf_field() ?>
<div class="guide-dialog-head"><h2 id="guide-dialog-title">Yeni iletişim kişisi</h2><button type="button" class="btn-icon" data-guide-close aria-label="Kapat">×</button></div>
<div class="field"><label for="guide-name">Ad soyad *</label><input id="guide-name" name="name" required minlength="3" maxlength="120" autocomplete="name"></div>
<div class="field"><label for="guide-phone">Telefon *</label><input id="guide-phone" name="phone" type="tel" required maxlength="40" autocomplete="tel" placeholder="05XX XXX XX XX"></div>
<p class="f-hint">Kişi iletişim listene kaydedilir ve bu faaliyette seçilir.</p>
<p data-guide-error role="alert" hidden></p>
<div class="guide-dialog-actions"><button type="button" class="btn btn-ghost" data-guide-close>Vazgeç</button><button type="submit" class="btn btn-primary">Kaydet ve seç</button></div>
</form></dialog>
