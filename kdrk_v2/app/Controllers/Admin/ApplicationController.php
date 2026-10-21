<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Audit;
use App\Core\Auth;
use App\Core\Crypto;
use App\Core\View;
use App\Models\Activity;

/**
 * Bir faaliyetin başvuruları: durum, ulaşım, biniş noktası; yazdırılabilir listeler.
 * TC kimlik ve sağlık notu yalnızca admin'e çözülür (CLAUDE.md §15).
 */
final class ApplicationController extends BaseController
{
    protected array $roles = ['admin', 'editor', 'viewer', 'guide'];
    protected bool $viewerCanPost = true;

    public const PICKUPS = ['Rize Otogar', 'Pazar Merkez', 'Ardeşen Merkez', 'Çamlıhemşin Merkez', 'Ayder'];
    public const TRANSPORT = ['club' => 'Kulüp aracı', 'own' => 'Kendi aracı'];

    public function index(string $id): void
    {
        $a = $this->activity((int) $id);
        $status = in_array($_GET['durum'] ?? '', ['pending', 'approved', 'waitlist', 'rejected'], true) ? $_GET['durum'] : null;
        $q = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 80);
        $this->render('activities/applications', $a['title'], 'activities', [
            'a'      => $a,
            'rows'   => $this->applications((int) $a['id'], $status, $q),
            'status' => $status,
            'q'      => $q,
            'head'   => $this->headCounts((int) $a['id']),
        ]);
    }

    /** Yoklama özetindeki ödeme cümlesi; ücreti girilmemiş ödemeler "₺0" diye yanıltmaz. */
    public static function payText(int $paid, float $sum, int $missing): string
    {
        if ($paid === 0) {
            return 'Henüz ödeme yok';
        }
        if ($sum <= 0) {
            return 'Tutar girilmedi';
        }
        return tr_money($sum) . ' tahsil' . ($missing > 0 ? ' · ' . $missing . ' kişide tutar yok' : '');
    }

    /** Başvurular ve Yoklama ekranlarının ortak şeridi için sayılar (tek sorgu). */
    private function headCounts(int $activityId): array
    {
        $r = App::db()->one(
            "SELECT SUM(status = 'pending') pending, SUM(status = 'approved') approved, SUM(status = 'waitlist') waitlist,
                    SUM(status = 'rejected') rejected, COUNT(*) total,
                    SUM(status = 'approved' AND attended = 1) came, SUM(status = 'approved' AND paid = 1) paid
             FROM applications WHERE activity_id = ?",
            [$activityId]
        );
        return array_map('intval', $r);
    }

    public function update(string $id): void
    {
        $p = App::db()->one('SELECT id, activity_id, status FROM applications WHERE id = ?', [(int) $id]) ?? abort(404);
        $this->activity((int) $p['activity_id']);
        if (!\App\Core\ActivityAccess::canApprove()) abort(403);
        $sets = [];
        $params = [];
        if (in_array($_POST['status'] ?? '', ['pending', 'approved', 'waitlist', 'rejected'], true)) {
            $sets[] = 'status = ?';
            $params[] = $_POST['status'];
        }
        if (array_key_exists('transport', $_POST)) {
            $sets[] = 'transport = ?';
            $params[] = array_key_exists($_POST['transport'], self::TRANSPORT) ? $_POST['transport'] : null;
            $sets[] = 'pickup = ?';
            $params[] = ($_POST['transport'] ?? '') === 'club' ? (self::nullable('pickup', 120)) : null;
        }
        if ($sets) {
            $params[] = $p['id'];
            App::db()->run('UPDATE applications SET ' . implode(', ', $sets) . ' WHERE id = ?', $params);
            Audit::log('update', 'application', (int) $p['id'], array_intersect_key($_POST, ['status' => 1, 'transport' => 1]));
        }
        $labels = ['approved' => 'Başvuru onaylandı.', 'rejected' => 'Başvuru reddedildi.', 'waitlist' => 'Yedek listeye alındı.', 'pending' => 'Başvuru beklemeye alındı.'];
        $this->flash($labels[$_POST['status'] ?? ''] ?? 'Kaydedildi.');
        redirect(url('admin.applications', ['id' => $p['activity_id']]) . '#p' . $p['id'], 303);
    }

    /** KVKK silme talebi gibi durumlar için; yalnızca admin. */
    public function delete(string $id): void
    {
        $this->requireRole('admin');
        $p = App::db()->one('SELECT id, activity_id FROM applications WHERE id = ?', [(int) $id]) ?? abort(404);
        App::db()->run('DELETE FROM applications WHERE id = ?', [$p['id']]);
        Audit::log('delete', 'application', (int) $p['id']);
        $this->flash('Başvuru ve kişisel verileri silindi.');
        redirect(url('admin.applications', ['id' => $p['activity_id']]), 303);
    }

    /**
     * Faaliyet günü yoklaması: onaylı katılımcılar; ara, geldi/gelmedi, ödedi/ödemedi.
     * Rehber yalnız kendisine atanan faaliyeti görür (activity() → ActivityAccess::requireAssigned).
     */
    public function checklist(string $id): void
    {
        $a = $this->activity((int) $id);
        $this->render('activities/checklist', $a['title'] . ' · Yoklama', 'activities', [
            'a'       => $a,
            'rows'    => $this->applications((int) $a['id'], 'approved'),
            'canEdit' => \App\Core\ActivityAccess::canApprove(),
            'head'    => $this->headCounts((int) $a['id']),
        ]);
    }

    /**
     * Yoklama işareti, JSON ya da form ile:
     *   attended = 1|0|''  geldi / gelmedi / işareti kaldır
     *   paid     = 1|0     ödedi (tutar: amount, yoksa kayıtlı tutar, yoksa faaliyet ücreti) / ödemedi (tutar silinir)
     *   amount   = "1.500" tek başına gelirse ödendi sayılır ve tutar güncellenir
     */
    public function checkin(string $id): void
    {
        $p = App::db()->one('SELECT id, activity_id, status, paid, paid_amount FROM applications WHERE id = ?', [(int) $id]) ?? abort(404);
        $a = $this->activity((int) $p['activity_id']);
        if (!\App\Core\ActivityAccess::canApprove()) abort(403);
        $json = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        $fail = function (string $message) use ($json, $p): never {
            if ($json) {
                http_response_code(422);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $this->flash($message, 'error');
            redirect(url('admin.applications.checklist', ['id' => $p['activity_id']]) . '#p' . $p['id'], 303);
        };
        if ($p['status'] !== 'approved') {
            $fail('Yalnız onaylı katılımcı işaretlenebilir.');
        }
        $amount = null;
        if (trim((string) ($_POST['amount'] ?? '')) !== '') {
            $amount = parse_money((string) $_POST['amount']) ?? $fail('Geçerli bir tutar yaz (ör. 1500 ya da 1.500,50).');
        }
        if (!array_key_exists('paid', $_POST) && $amount !== null) {
            $_POST['paid'] = '1';
        }
        $sets = ['checked_by = ?'];
        $params = [(int) Auth::user()['id']];
        if (array_key_exists('attended', $_POST) && in_array($_POST['attended'], ['1', '0', ''], true)) {
            $sets[] = 'attended = ?';
            $params[] = $_POST['attended'] === '' ? null : (int) $_POST['attended'];
            $sets[] = 'attended_at = ' . ($_POST['attended'] === '' ? 'NULL' : 'NOW()');
        }
        if (array_key_exists('paid', $_POST) && in_array($_POST['paid'], ['1', '0'], true)) {
            $paidNow = $_POST['paid'] === '1';
            $sets[] = 'paid = ?';
            $params[] = (int) $paidNow;
            // Ödeme zamanı ilk "ödedi" anıdır; yalnız tutar düzeltilince değişmez.
            $sets[] = 'paid_at = ' . ($paidNow ? ((int) $p['paid'] ? 'COALESCE(paid_at, NOW())' : 'NOW()') : 'NULL');
            $sets[] = 'paid_amount = ?';
            $params[] = $paidNow ? ($amount ?? ($p['paid_amount'] !== null ? (float) $p['paid_amount'] : ($a['fee'] !== null ? (float) $a['fee'] : null))) : null;
        }
        if (count($sets) > 1) {
            $params[] = $p['id'];
            App::db()->run('UPDATE applications SET ' . implode(', ', $sets) . ' WHERE id = ?', $params);
            Audit::log('checkin', 'application', (int) $p['id'], array_intersect_key($_POST, ['attended' => 1, 'paid' => 1, 'amount' => 1]));
        }
        $row = App::db()->one('SELECT attended, paid, paid_amount FROM applications WHERE id = ?', [$p['id']]);
        if ($json) {
            $sum = App::db()->one("SELECT COUNT(*) total, SUM(attended = 1) came, SUM(attended = 0) absent, SUM(paid = 1) paid, SUM(CASE WHEN paid = 1 THEN paid_amount END) amount, SUM(paid = 1 AND paid_amount IS NULL) missing FROM applications WHERE activity_id = ? AND status = 'approved'", [$p['activity_id']]);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => true,
                'attended' => $row['attended'] === null ? null : (int) $row['attended'],
                'paid' => (int) $row['paid'],
                'amount' => $row['paid_amount'] === null ? null : (float) $row['paid_amount'],
                'amountText' => $row['paid_amount'] === null ? '' : number_format((float) $row['paid_amount'], fmod((float) $row['paid_amount'], 1.0) === 0.0 ? 0 : 2, ',', '.'),
                'summary' => [
                    'total' => (int) $sum['total'], 'came' => (int) $sum['came'], 'absent' => (int) $sum['absent'], 'paid' => (int) $sum['paid'],
                    'amount' => (float) $sum['amount'], 'amountText' => tr_money((float) $sum['amount']),
                    'payText' => self::payText((int) $sum['paid'], (float) $sum['amount'], (int) $sum['missing']),
                ],
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $this->flash('Yoklama kaydedildi.');
        redirect(url('admin.applications.checklist', ['id' => $p['activity_id']]) . '#p' . $p['id'], 303);
    }

    /**
     * Katılım / Taşımacılık listesi (yalnızca onaylılar).
     * ?bicim=pdf  → yazdırma sayfası açılır açılmaz "PDF olarak kaydet" penceresi gelir (paket gerektirmez).
     * ?bicim=excel → Excel'in doğrudan açtığı UTF-8, noktalı virgüllü CSV indirilir.
     */
    public function printList(string $id, string $kind): void
    {
        $a = $this->activity((int) $id);
        if (!in_array($kind, ['katilim', 'tasima'], true)) {
            abort(404);
        }
        $format = in_array($_GET['bicim'] ?? '', ['pdf', 'excel'], true) ? $_GET['bicim'] : 'print';
        $rows = $this->applications((int) $a['id'], 'approved');
        $sensitive = Auth::canSeeSensitive();
        Audit::log('print', 'activity', (int) $a['id'], ['list' => $kind, 'format' => $format]);
        if ($format === 'excel') {
            $this->csv($a, $kind, $rows, $sensitive);
        }
        echo View::render('admin/activities/print', [
            'a'         => $a,
            'kind'      => $kind,
            'rows'      => $rows,
            'sensitive' => $sensitive,
            'autoPrint' => $format === 'pdf',
        ], null);
    }

    /** Excel için CSV: BOM + ";" ayraç (Türkçe Excel), formül olarak çalışabilecek hücreler etkisizleştirilir. */
    private function csv(array $a, string $kind, array $rows, bool $sensitive): never
    {
        $ph = static fn (?string $p): string => $p ? preg_replace('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', '0$1 $2 $3 $4', $p) : '';
        $yes = static fn (?int $v, string $y, string $n): string => $v === null ? '' : ($v ? $y : $n);
        $paidCol = $a['fee'] === null || (float) $a['fee'] > 0;
        if ($kind === 'katilim') {
            $head = array_merge(['#', 'Ad Soyad'], $sensitive ? ['TC Kimlik No'] : [], ['Telefon', 'E-posta', 'Acil durum kişisi', 'Acil durum telefonu'], $sensitive ? ['Sağlık notu'] : [], ['Ulaşım', 'Biniş noktası', 'Yoklama'], $paidCol ? ['Ücret', 'Ödenen tutar'] : []);
            $lines = [];
            foreach (array_values($rows) as $i => $p) {
                $lines[] = array_merge([$i + 1, $p['name']], $sensitive ? [$p['tc'] ?? ''] : [], [$ph($p['phone']), $p['email'], $p['emer_name'] ?? '', $ph($p['emer_phone'])], $sensitive ? [$p['health'] ?? ''] : [], [self::TRANSPORT[$p['transport']] ?? '', $p['pickup'] ?? '', $yes(isset($p['attended']) ? (int) $p['attended'] : null, 'Geldi', 'Gelmedi')], $paidCol ? [(int) $p['paid'] ? 'Ödedi' : 'Ödemedi', (int) $p['paid'] && $p['paid_amount'] !== null ? number_format((float) $p['paid_amount'], 2, ',', '') : ''] : []);
            }
        } else {
            $head = array_merge(['#', 'Biniş noktası', 'Ad Soyad'], $sensitive ? ['TC Kimlik No'] : [], ['Telefon', 'Acil durum telefonu', 'Ulaşım']);
            usort($rows, static fn ($x, $y) => [$x['transport'] === 'own', $x['pickup'] ?? 'zzz'] <=> [$y['transport'] === 'own', $y['pickup'] ?? 'zzz']);
            $lines = [];
            foreach (array_values($rows) as $i => $p) {
                $lines[] = array_merge([$i + 1, $p['transport'] === 'own' ? '' : ($p['pickup'] ?: 'Belirtilmedi'), $p['name']], $sensitive ? [$p['tc'] ?? ''] : [], [$ph($p['phone']), $ph($p['emer_phone']), self::TRANSPORT[$p['transport']] ?? 'Belirtilmedi']);
            }
        }
        $cell = static function ($v): string {
            $v = (string) $v;
            if ($v !== '' && strpbrk($v[0], '=+-@') !== false) {
                $v = "'" . $v; // CSV formül enjeksiyonu
            }
            return '"' . str_replace('"', '""', $v) . '"';
        };
        $name = ($kind === 'katilim' ? 'katilim' : 'tasimacilik') . '-listesi-' . $a['slug'] . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        echo "\xEF\xBB\xBF";
        foreach (array_merge([$head], $lines) as $line) {
            echo implode(';', array_map($cell, $line)), "\r\n";
        }
        exit;
    }

    private function activity(int $id): array
    {
        $row = App::db()->one(
            "SELECT a.*, (SELECT COUNT(*) FROM applications p WHERE p.activity_id = a.id AND p.status IN ('pending','approved')) AS taken
             FROM activities a WHERE a.id = ?",
            [$id]
        ) ?? abort(404);
        \App\Core\ActivityAccess::requireAssigned($row);
        return Activity::decorate($row);
    }

    private function applications(int $activityId, ?string $status, string $q = ''): array
    {
        $sql = 'SELECT * FROM applications WHERE activity_id = ?';
        $params = [$activityId];
        if ($status) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        if ($q !== '') {
            // İsim/e-posta parçası ya da telefon (boşluk ve baştaki 0 yok sayılır: "0532 111" → 532111)
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $digits = ltrim(preg_replace('/\D/', '', $q), '0');
            $sql .= ' AND (name LIKE ? OR email LIKE ?' . ($digits !== '' ? ' OR phone LIKE ?' : '') . ')';
            array_push($params, $like, $like);
            if ($digits !== '') {
                $params[] = '%' . $digits . '%';
            }
        }
        $rows = App::db()->all($sql . " ORDER BY FIELD(status, 'pending', 'approved', 'waitlist', 'rejected'), created_at", $params);
        $sensitive = Auth::canSeeSensitive();
        foreach ($rows as &$r) {
            $r['tc'] = $sensitive ? Crypto::decrypt($r['tc_enc']) : null;
            $r['health'] = $sensitive ? Crypto::decrypt($r['health_enc']) : null;
            $r['has_health'] = $r['health_enc'] !== null;
            unset($r['tc_enc'], $r['health_enc']);
        }
        return $rows;
    }
}
