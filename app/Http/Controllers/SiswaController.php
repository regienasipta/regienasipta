<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    public function index(): View
    {
        $siswas = DB::table('siswas')
            ->join('users', 'siswas.id_user', '=', 'users.id')
            ->select('siswas.*', 'users.name', 'users.email', 'siswas.hp');

        if (request('cari')) {
            $siswas = $this->search(request('cari'));
        } else {
            $siswas = $siswas->paginate(10);
        }

        return view('admin.siswa.index', compact('siswas'));
    }

    public function create(): View
    {
        return view('admin.siswa.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'email' => 'required|email|max:250|unique:users',
            'password' => 'required|min:8|confirmed',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'nis' => 'required|numeric',
            'tingkatan' => 'required',
            'jurusan' => 'required',
            'kelas' => 'required',
            'hp' => 'required|numeric',
        ]);

        $image = $request->file('image');
        $imagePath = $image->storeAs('siswas', $image->hashName(), 'public');

        $id_akun = $this->insertAccount($request->name, $request->email, $request->password);

        Siswa::create([
            'id_user' => $id_akun,
            'image' => $image->hashName(),
            'nis' => $request->nis,
            'tingkatan' => $request->tingkatan,
            'jurusan' => $request->jurusan,
            'kelas' => $request->kelas,
            'hp' => $request->hp,
            'status' => 1
        ]);

        return redirect()->route('siswa.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function insertAccount(string $name, string $email, string $password)
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'usertype' => 'siswa'
        ]);

        return $user->id;
    }

    public function show(string $id): View
    {
        $siswa = DB::table('siswas')
            ->join('users', 'siswas.id_user', '=', 'users.id')
            ->select('siswas.*', 'users.name', 'users.email')
            ->where('siswas.id', $id)
            ->first();

        return view('admin.siswa.show', compact('siswa'));
    }

    public function search(string $cari)
    {
        return DB::table('siswas')
            ->join('users', 'siswas.id_user', '=', 'users.id')
            ->select('siswas.*', 'users.name', 'users.email')
            ->where('users.name', 'like', '%' . $cari . '%')
            ->orWhere('siswas.nis', 'like', '%' . $cari . '%')
            ->orWhere('users.email', 'like', '%' . $cari . '%')
            ->paginate(10);
    }

    public function edit(string $id): View
    {
        $siswa = DB::table('siswas')
            ->join('users', 'siswas.id_user', '=', 'users.id')
            ->select('siswas.*', 'users.name', 'users.email')
            ->where('siswas.id', $id)
            ->first();

        return view('admin.siswa.edit', compact('siswa'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'image' => 'image|mimes:jpeg,png,jpg|max:2048',
            'nis' => 'required|numeric',
            'tingkatan' => 'required',
            'jurusan' => 'required',
            'kelas' => 'required',
            'hp' => 'required|numeric',
            'status' => 'required'
        ]);

        $datas = Siswa::findOrFail($id);
        $this->editAccount($request->name, $id);

        if ($request->hasFile('image')) {
            Storage::delete('public/siswas/' . $datas->image);

            $image = $request->file('image');
            $imagePath = $image->storeAs('siswas', $image->hashName(), 'public');

            $datas->update([
                'image' => $image->hashName(),
                'nis' => $request->nis,
                'tingkatan' => $request->tingkatan,
                'jurusan' => $request->jurusan,
                'kelas' => $request->kelas,
                'hp' => $request->hp,
                'status' => $request->status
            ]);
        } else {
            $datas->update($request->only(['nis', 'tingkatan', 'jurusan', 'kelas', 'hp', 'status']));
        }

        return redirect()->route('siswa.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    public function editAccount(string $name, string $id)
    {
        $siswa = DB::table('siswas')->where('id', $id)->value('id_user');
        $user = User::findOrFail($siswa);

        $user->update(['name' => $name]);
    }

    public function destroy($id): RedirectResponse
    {
        $post = Siswa::findOrFail($id);

        Storage::delete('public/siswas/' . $post->image);

        $this->destroyUser($id);
        $post->delete();

        return redirect()->route('siswa.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

    public function destroyUser(string $id)
    {
        $siswa = DB::table('siswas')->where('id', $id)->value('id_user');
        $user = User::findOrFail($siswa);

        $user->delete();
    }
}