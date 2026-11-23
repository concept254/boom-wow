<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BusinessUnit;
use App\Models\Media;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\HeroImageService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

/** Customers, users, roles, media library, notifications, activity logs and settings. */
class SystemController extends Controller
{
    private function gate(Request $request, string $permission): void
    {
        abort_unless($request->user()->hasRole('Super Admin') || $request->user()->can($permission), 403);
    }

    private function superOnly(Request $request): void
    {
        abort_unless($request->user()->hasRole('Super Admin'), 403);
    }

    // ---- Customers -------------------------------------------------------
    public function customers(Request $request)
    {
        $this->gate($request, 'customers.view');
        $users = User::role('Customer')->withCount(['bookings', 'orders'])
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('email', 'like', '%'.addcslashes($t, '%_\\').'%')->orWhere('last_name', 'like', '%'.addcslashes($t, '%_\\').'%')))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.customers', compact('users'));
    }

    // ---- Users & roles (Super Admin only) -------------------------------
    public function users(Request $request)
    {
        $this->superOnly($request);
        $users = User::with(['roles', 'businesses'])->whereDoesntHave('roles', fn ($q) => $q->where('name', 'Customer'))
            ->when($request->q, fn ($q, $t) => $q->where('email', 'like', '%'.addcslashes($t, '%_\\').'%'))->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', ['users' => $users, 'roles' => Role::whereIn('name', ['Super Admin', 'Business Manager', 'Staff'])->pluck('name'), 'businesses' => BusinessUnit::orderBy('name')->get()]);
    }

    public function storeUser(Request $request)
    {
        $this->superOnly($request);
        $data = $request->validate([
            'first_name' => ['required', 'max:100'], 'last_name' => ['required', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'], 'phone' => ['nullable', 'max:30'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'role' => ['required', 'in:Super Admin,Business Manager,Staff'],
            'businesses' => ['array'], 'businesses.*' => ['exists:business_units,id'],
        ]);
        abort_if($data['role'] !== 'Super Admin' && empty($data['businesses']), 422, 'Assign at least one business.');

        $user = User::create(collect($data)->only(['first_name', 'last_name', 'email', 'phone', 'password'])->all());
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole($data['role']);
        $user->businesses()->sync($data['role'] === 'Super Admin' ? [] : $data['businesses']);
        ActivityLogger::log('created', "Created user {$user->email} ({$data['role']})", $user);

        return back()->with('status', 'User created.');
    }

    public function updateUser(Request $request, User $user)
    {
        $this->superOnly($request);
        $data = $request->validate(['role' => ['required', 'in:Super Admin,Business Manager,Staff'], 'businesses' => ['array'], 'businesses.*' => ['exists:business_units,id'], 'is_active' => ['boolean']]);
        abort_if($user->id === $request->user()->id && $data['role'] !== 'Super Admin', 422, 'You cannot demote yourself.');

        $user->syncRoles([$data['role']]);
        $user->businesses()->sync($data['role'] === 'Super Admin' ? [] : ($data['businesses'] ?? []));
        $user->update(['is_active' => $request->boolean('is_active')]);
        ActivityLogger::log('updated', "Updated user {$user->email}", $user);

        return back()->with('status', 'User updated.');
    }

    public function roles(Request $request)
    {
        $this->superOnly($request);

        return view('admin.roles', ['roles' => Role::with('permissions')->get(), 'permissions' => \Spatie\Permission\Models\Permission::orderBy('name')->get()->groupBy(fn ($p) => explode('.', $p->name)[0])]);
    }

    public function updateRole(Request $request, Role $role)
    {
        $this->superOnly($request);
        abort_if($role->name === 'Super Admin', 422, 'Super Admin always has every permission.');
        $role->syncPermissions($request->validate(['permissions' => ['array'], 'permissions.*' => ['exists:permissions,name']])['permissions'] ?? []);
        ActivityLogger::log('updated', "Updated permissions for role {$role->name}");

        return back()->with('status', 'Role updated.');
    }

    // ---- Media library ---------------------------------------------------
    public function media(Request $request)
    {
        $this->gate($request, 'media.view');

        return view('admin.media', ['items' => Media::latest()->paginate(24)]);
    }

    public function uploadMedia(Request $request, HeroImageService $hero)
    {
        $this->gate($request, 'media.create');
        $request->validate(['files' => ['required', 'array', 'max:10'], 'files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'], 'as_hero' => ['nullable', 'boolean']]);

        foreach ($request->file('files') as $file) {
            [$w, $h] = getimagesize($file->getRealPath()) ?: [null, null];
            $path = $file->store('media', 'public');
            Media::create(['uploaded_by' => $request->user()->id, 'disk' => 'public', 'path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType(), 'size' => $file->getSize(), 'width' => $w, 'height' => $h]);

            if ($request->boolean('as_hero') && $request->user()->hasRole('Super Admin')) {
                $file->move(public_path(config('boomwow.hero.directory')), uniqid('hero_').'.'.$file->guessExtension());
                $hero->forget();
            }
        }

        return back()->with('status', 'Uploaded.');
    }

    public function deleteMedia(Request $request, Media $media)
    {
        $this->gate($request, 'media.delete');
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return back()->with('status', 'Deleted.');
    }

    // ---- Notifications, logs, settings ----------------------------------
    public function notifications(Request $request)
    {
        $items = $request->user()->notifications()->latest()->paginate(20);
        $request->user()->unreadNotifications->markAsRead();

        return view('admin.notifications', compact('items'));
    }

    public function logs(Request $request)
    {
        $this->gate($request, 'logs.view');
        $logs = ActivityLog::with('user')
            ->when(! $request->user()->hasRole('Super Admin'), fn ($q) => $q->whereIn('business_unit_id', $request->user()->businessIds()))
            ->latest()->paginate(25);

        return view('admin.logs', compact('logs'));
    }

    public const SETTINGS = [
        'site_name' => 'Site name', 'contact_email' => 'Contact email', 'contact_phone' => 'Contact phone', 'contact_address' => 'Address', 'contact_lat' => 'Map latitude (e.g. -26.1076)', 'contact_lng' => 'Map longitude (e.g. 28.0567)', 'whatsapp_number' => 'WhatsApp number (digits, e.g. 27110000000)', 'trading_note' => 'Opening hours note (shown in info strip)',
        'hero_title' => 'Hero headline', 'hero_subtitle' => 'Hero supporting copy', 'footer_text' => 'Footer text',
        'social_facebook' => 'Facebook URL', 'social_instagram' => 'Instagram URL', 'social_linkedin' => 'LinkedIn URL', 'social_x' => 'X URL',
        'seo_title' => 'Default SEO title', 'seo_description' => 'Default SEO description',
        'liquor_notice' => 'Responsible-sales notice', 'liquor_licence_no' => 'Liquor licence number',
    ];

    public function settings(Request $request)
    {
        $this->superOnly($request);

        return view('admin.settings', ['fields' => self::SETTINGS]);
    }

    public function updateSettings(Request $request)
    {
        $this->superOnly($request);
        $data = $request->validate(collect(self::SETTINGS)->map(fn () => ['nullable', 'string', 'max:500'])->all());
        foreach ($data as $k => $v) {
            SettingsService::set($k, $v);
        }
        ActivityLogger::log('updated', 'Updated site settings');

        return back()->with('status', 'Settings saved.');
    }
}
