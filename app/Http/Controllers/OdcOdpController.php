<?php

namespace App\Http\Controllers;

use App\Models\OdcOdp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OdcOdpController extends Controller
{
    public function index()
    {
        $data = OdcOdp::with('parent')->latest()->get();
        return view('content.odc_odp.index', compact('data'));
    }

    public function create()
    {
        $odcs = OdcOdp::where('tipe', 'ODC')->get();
        return view('content.odc_odp.create', compact('odcs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tipe' => 'required|in:ODC,ODP',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'deskripsi' => 'nullable|string',
            'parent_id' => 'nullable|exists:odc_odp,id'
        ]);

        $data = $request->all();

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('odc_odp', 'public');
            $data['foto'] = Storage::url($path);
        }

        OdcOdp::create($data);

        return redirect()->route('odc-odp.index')->with('success', 'Titik Jaringan berhasil ditambahkan.');
    }

    public function edit(OdcOdp $odcOdp)
    {
        $odcs = OdcOdp::where('tipe', 'ODC')->get();
        return view('content.odc_odp.edit', compact('odcOdp', 'odcs'));
    }

    public function update(Request $request, OdcOdp $odcOdp)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tipe' => 'required|in:ODC,ODP',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'deskripsi' => 'nullable|string',
            'parent_id' => 'nullable|exists:odc_odp,id'
        ]);

        $data = $request->all();

        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($odcOdp->foto) {
                $oldPath = str_replace('/storage/', '', $odcOdp->foto);
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $request->file('foto')->store('odc_odp', 'public');
            $data['foto'] = Storage::url($path);
        }

        $odcOdp->update($data);

        return redirect()->route('odc-odp.index')->with('success', 'Titik Jaringan berhasil diperbarui.');
    }

    public function destroy(OdcOdp $odcOdp)
    {
        if ($odcOdp->foto) {
            $oldPath = str_replace('/storage/', '', $odcOdp->foto);
            Storage::disk('public')->delete($oldPath);
        }
        
        $odcOdp->delete();

        return redirect()->route('odc-odp.index')->with('success', 'Titik Jaringan berhasil dihapus.');
    }
}
