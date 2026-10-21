<?php use App\Core\Auth; ?>
<div class="toolbar"><p class="muted">Faaliyet rehberlerini bir kez tanımla, faaliyetlerde listeden seç. Kişi güncellendiğinde bağlı faaliyetlerdeki bilgileri de güncellenir.</p>
<?php if (Auth::canEdit()): ?><a class="btn btn-primary" href="<?= e(url('admin.contacts.new')) ?>" data-modal-link>Yeni kişi</a><?php endif ?></div>
<div class="panel">
<?php if ($rows): ?><table class="tbl tbl-cards"><thead><tr><th>Ad soyad</th><th>Telefon</th><th>İşlem</th></tr></thead><tbody>
<?php foreach ($rows as $person): ?><tr><td data-label="Ad soyad"><?= e($person['name']) ?></td><td data-label="Telefon"><a href="tel:<?= e($person['phone']) ?>"><?= e($person['phone']) ?></a></td><td class="t-actions"><a class="btn btn-ghost btn-sm" href="<?= e(url('admin.contacts.edit', ['id' => $person['id']])) ?>"><?= Auth::canEdit() ? 'Düzenle' : 'Görüntüle' ?></a></td></tr><?php endforeach ?>
</tbody></table><?php else: ?><div class="empty"><h4>Henüz kişi tanımlanmamış</h4><p>Buradan veya faaliyet formundaki “Yeni kişi ekle” seçeneğinden başlayabilirsin.</p></div><?php endif ?>
</div>
