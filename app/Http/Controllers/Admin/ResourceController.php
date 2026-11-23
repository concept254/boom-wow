<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Generic CRUD driven by config/admin.php. Authorization is enforced here on
 * the backend: permission check per action + business-unit ownership check on writes.
 */
class ResourceController extends Controller
{
    private function cfg(string $resource): array
    {
        $cfg = config("admin.$resource");
        abort_unless($cfg, 404);

        return $cfg;
    }

    private function authorizeAction(Request $request, array $cfg, string $action): void
    {
        $user = $request->user();
        $only = $cfg['only'] ?? ['index', 'create', 'store', 'edit', 'update', 'destroy'];
        abort_unless(in_array($action, $only, true), 404);

        if ($user->hasRole('Super Admin')) {
            return;
        }
        abort_if(($cfg['super_only_write'] ?? false) && $action !== 'index', 403);

        $ability = match ($action) { 'index' => 'view', 'create', 'store' => 'create', 'edit', 'update' => 'update', 'destroy' => 'delete' };
        abort_unless($user->can($cfg['permission'].'.'.$ability), 403);
    }

    private function find(array $cfg, int|string $id): Model
    {
        // The model's business global scope (switched on by EnsureBusinessAccess) hides other businesses' rows -> 404.
        return $cfg['model']::findOrFail($id);
    }

    public function index(Request $request, string $resource)
    {
        $cfg = $this->cfg($resource);
        $this->authorizeAction($request, $cfg, 'index');

        $sort = in_array($request->sort, array_keys($cfg['columns']), true) && ! str_contains((string) $request->sort, '.') ? $request->sort : 'id';
        $dir = $request->dir === 'asc' ? 'asc' : 'desc';

        $query = $cfg['model']::query()->with($cfg['with'] ?? [])
            ->when($request->q, function ($q, $term) use ($cfg) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(fn ($w) => collect($cfg['search'] ?? [])->each(fn ($col) => $w->orWhere($col, 'like', $like)));
            })
            ->when($request->status && in_array('status', array_column($cfg['fields'], 'name')), fn ($q, $s) => $q->where('status', $s))
            ->when($request->business && ($cfg['scoped'] ?? false), fn ($q, $b) => $q->where('business_unit_id', (int) $b))
            ->orderBy($sort, $dir);

        return view('admin.resource.index', [
            'resource' => $resource, 'cfg' => $cfg,
            'rows' => $query->paginate(15)->withQueryString(),
            'businesses' => $this->businessOptions($request),
        ]);
    }

    public function create(Request $request, string $resource)
    {
        $cfg = $this->cfg($resource);
        $this->authorizeAction($request, $cfg, 'create');

        return view('admin.resource.form', ['resource' => $resource, 'cfg' => $cfg, 'row' => new $cfg['model'], 'options' => $this->options($request, $cfg)]);
    }

    public function store(Request $request, string $resource)
    {
        $cfg = $this->cfg($resource);
        $this->authorizeAction($request, $cfg, 'store');

        $data = $this->validated($request, $cfg, null);
        $row = $cfg['model']::create($data);
        ActivityLogger::log('created', "Created {$cfg['label']} #{$row->getKey()}", $row);

        return redirect()->route('admin.resource.index', $resource)->with('status', 'Created.');
    }

    public function edit(Request $request, string $resource, int $id)
    {
        $cfg = $this->cfg($resource);
        $this->authorizeAction($request, $cfg, 'edit');

        return view('admin.resource.form', ['resource' => $resource, 'cfg' => $cfg, 'row' => $this->find($cfg, $id), 'options' => $this->options($request, $cfg)]);
    }

    public function update(Request $request, string $resource, int $id)
    {
        $cfg = $this->cfg($resource);
        $this->authorizeAction($request, $cfg, 'update');

        $row = $this->find($cfg, $id);
        $row->update($this->validated($request, $cfg, $row));
        ActivityLogger::log('updated', "Updated {$cfg['label']} #{$row->getKey()}", $row);

        return redirect()->route('admin.resource.index', $resource)->with('status', 'Saved.');
    }

    public function destroy(Request $request, string $resource, int $id)
    {
        $cfg = $this->cfg($resource);
        $this->authorizeAction($request, $cfg, 'destroy');

        $row = $this->find($cfg, $id);
        $row->delete();
        ActivityLogger::log('deleted', "Deleted {$cfg['label']} #{$id}", null, $row->business_unit_id ?? null);

        return redirect()->route('admin.resource.index', $resource)->with('status', 'Deleted.');
    }

    private function validated(Request $request, array $cfg, ?Model $row): array
    {
        $user = $request->user();
        $rules = [];
        foreach ($cfg['fields'] as $f) {
            $rules[$f['name']] = str_replace(['{id}', '{business}'], [(string) ($row?->getKey() ?? 'NULL'), (string) (int) $request->input('business_unit_id', $row?->business_unit_id ?? 0)], $f['rules'] ?? ($f['type'] === 'business' ? 'required|exists:business_units,id' : 'nullable'));
            if ($f['type'] === 'business') {
                $rules[$f['name']] = ['required', 'integer', Rule::exists('business_units', 'id')];
            }
        }
        $data = $request->validate($rules);

        // Business-unit isolation on writes: non-super users may only write to their own businesses.
        if (($cfg['scoped'] ?? false) && ! $user->hasRole('Super Admin')) {
            abort_unless($user->canAccessBusiness((int) $data['business_unit_id']), 403);
        }

        // Same rule for staff-owned records (availability, time off): the staff member must be in the user's business.
        if (($cfg['staff_scoped'] ?? false) && ! $user->hasRole('Super Admin')) {
            abort_unless(\App\Models\StaffProfile::whereKey((int) $data['staff_profile_id'])->exists(), 403);
        }

        foreach ($cfg['fields'] as $f) {
            $name = $f['name'];
            if ($f['type'] === 'checkbox') {
                $data[$name] = $request->boolean($name);
            } elseif ($f['type'] === 'lines') {
                $data[$name] = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) ($data[$name] ?? '')) ?: [])));
            } elseif ($f['type'] === 'image') {
                if ($request->hasFile($name)) {
                    $data[$name] = $request->file($name)->store($cfg['permission'], 'public');
                    if ($row?->{$name}) {
                        Storage::disk('public')->delete($row->{$name});
                    }
                } elseif ($row && $request->boolean('remove_'.$name)) {
                    // "Remove current image" ticked and no replacement uploaded: delete the file and clear the field.
                    if ($row->{$name}) {
                        Storage::disk('public')->delete($row->{$name});
                    }
                    $data[$name] = null;
                } else {
                    unset($data[$name]);
                }
            }
        }

        if (isset($data['status']) && $data['status'] === 'published' && $row === null && in_array('published_at', (new $cfg['model'])->getFillable(), true)) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function businessOptions(Request $request)
    {
        $q = \App\Models\BusinessUnit::query()->orderBy('name');
        if (! $request->user()->hasRole('Super Admin')) {
            $q->whereIn('id', $request->user()->businessIds());
        }

        return $q->pluck('name', 'id');
    }

    private function options(Request $request, array $cfg): array
    {
        $out = ['business' => $this->businessOptions($request)];
        foreach ($cfg['fields'] as $f) {
            if (isset($f['model'])) {
                $out[$f['name']] = $f['model']::query()->orderBy($f['label_column'])->pluck($f['label_column'], 'id');
            }
        }

        return $out;
    }
}
