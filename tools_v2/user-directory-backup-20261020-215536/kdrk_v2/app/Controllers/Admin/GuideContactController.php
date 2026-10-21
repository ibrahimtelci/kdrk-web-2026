<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\App;
use App\Core\Audit;
use App\Models\GuideContact;

final class GuideContactController extends BaseController
{
    protected array $roles = ['admin', 'editor', 'viewer'];
    public function index(): void
    {
        $this->render('contacts/index', 'İletişim Bilgileri', 'contacts', ['rows' => GuideContact::all()]);
    }
    public function create(): void
    {
        $this->requireRole('admin', 'editor');
        $this->render('contacts/form', 'Yeni iletişim kişisi', 'contacts', ['person' => null]);
    }
    public function edit(string $id): void
    {
        $p = App::db()->one('SELECT id, name, phone FROM guide_contacts WHERE id = ?', [(int) $id]) ?? abort(404);
        $this->render('contacts/form', 'İletişim kişisi', 'contacts', ['person' => $p]);
    }
    public function store(): void { $this->save(null); }
    public function update(string $id): void
    {
        $p = App::db()->one('SELECT id FROM guide_contacts WHERE id = ?', [(int) $id]) ?? abort(404);
        $this->save((int) $p['id']);
    }
    private function save(?int $id): never
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $raw = trim((string) ($_POST['phone'] ?? ''));
        $digits = preg_replace('/\D/', '', $raw);
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) { $digits = '90' . substr($digits, 1); }
        elseif (strlen($digits) === 10 && str_starts_with($digits, '5')) { $digits = '90' . $digits; }
        $phone = '+' . $digits; $errors = [];
        if (mb_strlen($name) < 3 || mb_strlen($name) > 120) { $errors['name'] = 'Ad soyad 3–120 karakter olmalı.'; }
        if (preg_match('/[^\d\s+().-]/u', $raw) || strlen($digits) < 10 || strlen($digits) > 15) { $errors['phone'] = 'Geçerli telefon numarasını ülke koduyla yaz.'; }
        if (App::db()->value('SELECT id FROM guide_contacts WHERE phone = ? AND id <> ?', [$phone, $id ?? 0])) { $errors['phone'] = 'Bu numara kayıtlı. Listeden mevcut kişiyi seç.'; }
        $json = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        if ($errors) {
            if ($json) { http_response_code(422); $this->json(['errors' => $errors]); }
            $this->back($id ? url('admin.contacts.edit', ['id' => $id]) : url('admin.contacts.new'), $errors, $_POST);
        }
        try {
            if ($id === null) { $id = App::db()->insert('guide_contacts', ['name' => $name, 'phone' => $phone]); $action = 'create'; }
            else { App::db()->run('UPDATE guide_contacts SET name = ?, phone = ? WHERE id = ?', [$name, $phone, $id]); $action = 'update'; }
        } catch (\PDOException $e) {
            if ($e->getCode() !== '23000') { throw $e; }
            if ($json) { http_response_code(422); $this->json(['errors' => ['phone' => 'Bu numara zaten kayıtlı.']]); }
            $this->back(url('admin.contacts.new'), ['phone' => 'Bu numara zaten kayıtlı.'], $_POST);
        }
        Audit::log($action, 'guide_contact', $id);
        if ($json) { $this->json(['ok' => true, 'person' => ['id' => $id, 'name' => $name, 'phone' => $phone]]); }
        $this->flash('İletişim kişisi kaydedildi.');
        $this->done(url('admin.contacts'));
    }
    private function json(array $data): never
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE); exit;
    }
}
