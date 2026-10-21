<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
final class GuideContactController extends BaseController
{
    protected array $roles = ['admin'];
    public function index(): void { redirect(url('admin.users'), 303); }
    public function create(): void { redirect(url('admin.users.new'), 303); }
    public function edit(string $id): void
    {
        $userId = \App\Core\App::db()->value('SELECT id FROM users WHERE legacy_contact_id = ?', [(int) $id]);
        redirect($userId ? url('admin.users.edit', ['id' => $userId]) : url('admin.users'), 303);
    }
    public function store(): void { abort(410); }
    public function update(string $id): void { abort(410); }
}
