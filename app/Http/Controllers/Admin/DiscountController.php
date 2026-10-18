<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscountController extends Controller
{
    public function index(): View
    {
        return view('admin.diskon.index', [
            'diskons' => DiscountCode::latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.diskon.form', ['diskon' => new DiscountCode()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['kode'] = strtoupper($data['kode']);
        DiscountCode::create($data);

        return redirect()->route('admin.diskon.index')->with('sukses', 'Kode diskon dibuat.');
    }

    public function edit(DiscountCode $diskon): View
    {
        return view('admin.diskon.form', ['diskon' => $diskon]);
    }

    public function update(Request $request, DiscountCode $diskon): RedirectResponse
    {
        $data = $this->validated($request, $diskon->id);
        $data['kode'] = strtoupper($data['kode']);
        $diskon->update($data);

        return redirect()->route('admin.diskon.index')->with('sukses', 'Kode diskon diperbarui.');
    }

    public function destroy(DiscountCode $diskon): RedirectResponse
    {
        $diskon->delete();

        return redirect()->route('admin.diskon.index')->with('sukses', 'Kode diskon dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'kode' => ['required', 'string', 'max:20', 'unique:discount_codes,kode,'.$ignoreId],
            'tipe' => ['required', 'in:persen,nominal'],
            'nilai' => ['required', 'integer', 'min:1'],
            'kuota' => ['nullable', 'integer', 'min:1'],
            'mulai' => ['nullable', 'date'],
            'berakhir' => ['nullable', 'date', 'after_or_equal:mulai'],
            'aktif' => ['boolean'],
        ]);
    }
}
