<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\CompanyNotificationEmailsContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        return $this->renderUsersView($request, false);
    }

    public function empleados(Request $request): View
    {
        return $this->renderUsersView($request, true);
    }

    public function companyEmails(): View
    {
        return view('admin.users.company_emails', [
            'companyNotificationEmails' => CompanyNotificationEmailsContent::load(),
        ]);
    }

    public function addCompanyEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'new_email' => ['required', 'email', 'max:190'],
        ]);

        $current = CompanyNotificationEmailsContent::load();
        $emails = $this->normalizeEmailList($current['emails'] ?? []);

        $newEmail = $this->normalizeSingleEmail((string) $validated['new_email']);
        if (!$newEmail) {
            return redirect()
                ->route('admin.company-emails')
                ->withInput()
                ->withErrors([
                    'new_email' => 'Correo no válido.',
                ]);
        }

        if (in_array($newEmail, $emails, true)) {
            return redirect()
                ->route('admin.company-emails')
                ->withInput()
                ->withErrors([
                    'new_email' => 'Ese correo ya existe en la lista.',
                ]);
        }

        $emails[] = $newEmail;
        CompanyNotificationEmailsContent::upsert(['emails' => $emails]);

        return redirect()
            ->route('admin.company-emails')
            ->with('status', 'Correo agregado correctamente.');
    }

    public function updateCompanyEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_email' => ['required', 'string', 'max:190'],
            'updated_email' => ['required', 'email', 'max:190'],
        ]);

        $currentEmail = $this->normalizeSingleEmail((string) $validated['current_email']);
        $updatedEmail = $this->normalizeSingleEmail((string) $validated['updated_email']);

        if (!$currentEmail || !$updatedEmail) {
            return redirect()
                ->route('admin.company-emails')
                ->withErrors([
                    'updated_email' => 'Correo no válido.',
                ]);
        }

        $current = CompanyNotificationEmailsContent::load();
        $emails = $this->normalizeEmailList($current['emails'] ?? []);

        if (!in_array($currentEmail, $emails, true)) {
            return redirect()
                ->route('admin.company-emails')
                ->withErrors([
                    'updated_email' => 'El correo original ya no existe en la lista.',
                ]);
        }

        $emails = array_values(array_filter($emails, fn ($email) => $email !== $currentEmail));

        if (in_array($updatedEmail, $emails, true)) {
            return redirect()
                ->route('admin.company-emails')
                ->withErrors([
                    'updated_email' => 'Ese correo ya existe en la lista.',
                ]);
        }

        $emails[] = $updatedEmail;
        CompanyNotificationEmailsContent::upsert(['emails' => $emails]);

        return redirect()
            ->route('admin.company-emails')
            ->with('status', 'Correo actualizado correctamente.');
    }

    public function deleteCompanyEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'max:190'],
        ]);

        $emailToDelete = $this->normalizeSingleEmail((string) $validated['email']);
        if (!$emailToDelete) {
            return redirect()
                ->route('admin.company-emails')
                ->withErrors([
                    'emails_text' => 'Correo no válido para eliminar.',
                ]);
        }

        $current = CompanyNotificationEmailsContent::load();
        $emails = $this->normalizeEmailList($current['emails'] ?? []);

        $emails = array_values(array_filter($emails, fn ($email) => $email !== $emailToDelete));
        CompanyNotificationEmailsContent::upsert(['emails' => $emails]);

        return redirect()
            ->route('admin.company-emails')
            ->with('status', 'Correo eliminado correctamente.');
    }

    public function updateCompanyEmails(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'emails_text' => ['nullable', 'string', 'max:4000'],
        ]);

        $raw = trim((string) ($validated['emails_text'] ?? ''));
        $pieces = preg_split('/[\r\n,;]+/', $raw) ?: [];

        $emails = $this->normalizeEmailList($pieces);

        CompanyNotificationEmailsContent::upsert([
            'emails' => array_values(array_unique($emails)),
        ]);

        return redirect()
            ->route('admin.company-emails')
            ->with('status', 'Correos de empresa actualizados.');
    }

    /** @param array<int, mixed> $emails */
    private function normalizeEmailList(array $emails): array
    {
        $normalized = [];
        foreach ($emails as $email) {
            $clean = $this->normalizeSingleEmail((string) $email);
            if (!$clean) {
                continue;
            }
            $normalized[] = $clean;
            if (count($normalized) >= 20) {
                break;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function normalizeSingleEmail(string $email): ?string
    {
        $clean = mb_strtolower(trim($email));
        if ($clean === '' || !filter_var($clean, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $clean;
    }

    public function updateRole(Request $request, Usuario $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'rol_id' => ['required', 'integer', 'exists:roles,id', 'not_in:4'],
        ]);

        if ((int) $usuario->rol_id === 4) {
            // Se simula el cambio para rol 4, pero se conserva internamente el rol original.
            return back()->with('status', 'Rol actualizado correctamente.');
        }

        $usuario->rol_id = (int) $validated['rol_id'];
        $usuario->save();

        return back()->with('status', 'Rol actualizado correctamente.');
    }

    private function renderUsersView(Request $request, bool $onlyEmployees): View
    {
        $search = trim((string) $request->query('q', ''));
        $showAllUsers = $onlyEmployees || $request->boolean('usuarios');

        $roles = Rol::query()
            ->where('id', '!=', 4)
            ->orderBy('id')
            ->get();

        $query = Usuario::query()->with('rol')->orderByDesc('id');

        if (! $showAllUsers) {
            $query->where(function ($subQuery) {
                $subQuery->whereNull('rol_id')
                    ->orWhere('rol_id', '!=', 4);
            });
        }

        if ($onlyEmployees) {
            $query->where('rol_id', 2);
        } elseif (!$showAllUsers) {
            $query->where('rol_id', '!=', 1);
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('correo', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $users = $query->paginate(10)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'search' => $search,
            'onlyEmployees' => $onlyEmployees,
            'showAllUsers' => $showAllUsers,
        ]);
    }
}
