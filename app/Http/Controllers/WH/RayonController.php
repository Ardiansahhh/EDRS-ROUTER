<?php

namespace App\Http\Controllers\WH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RayonController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $toko = DB::connection('CSAREPORT')->select("SELECT A.fc_branch, A.kode_rayon, A.name, A.tanggal FROM [CSAREPORT].[dbo].[t_rayon] A WITH (NOLOCK) WHERE fc_branch = '$user->fc_branch'");
        return view('rayon/index', ['data' => $toko]);
    }

    public function input()
    {
        return view('rayon/input');
    }

    public function store(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        if (isset($_POST['create_rayon'])) {
            $user = Auth::user();
            $today = date('d-m-Y H:i:s');
            $kode_rayon = strtoupper($request->kode_rayon);
            $cek_kode = DB::connection('CSAREPORT')->select("SELECT kode_rayon FROM [CSAREPORT].[dbo].[t_rayon] WHERE fc_branch = '$user->fc_branch' AND kode_rayon = '$kode_rayon'");
            if (!$cek_kode) {
                $data = [
                    'fc_branch'  => $user->fc_branch,
                    'kode_rayon' => $kode_rayon,
                    'name'       => $user->name,
                    'tanggal'    => $today
                ];
                DB::connection('CSAREPORT')->table('t_rayon')->insert($data);
                return redirect('/rayon')->with('success', 'Sukses ditambahkan');
            }
            return redirect()->back()->with('session', 'Kode Sudah digunakan');
        }
    }
}
