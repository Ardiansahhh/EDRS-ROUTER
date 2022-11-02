<?php

namespace App\Http\Controllers\WH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AreaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_area] WHERE fc_branch = '$user->fc_branch'");
        return view('area/index', ['data' => $data]);
    }

    public function input()
    {
        $user = Auth::user();
        $data = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_area] WHERE fc_branch = '$user->fc_branch'");
        return view('area/input', ['data' => $data]);
    }

    public function store(Request $request)
    {
        if (!isset($_POST['create_area'])) {
            return redirect('/area');
        }
        date_default_timezone_set('Asia/Jakarta');
        $user   = Auth::user();
        $branch = $user->fc_branch;
        $today  = date('d-m-Y H:i:s');
        $kode   = strtoupper($request->kode_area);
        $nama   = strtoupper($request->nama_area);
        $data   = $this->checkCode($branch, $kode);
        if ($data) {
            return redirect('/input-area')->with('session', 'Kode ' . $kode . ' Sudah Digunakan');
        }
        DB::connection('CSAREPORT')->table('t_area')->insert([
            'fc_branch' => $branch,
            'kode_area' => $kode,
            'nama_area' => $nama,
            'setting'   => 'NO',
            'nama'      => $user->name,
            'create_at' => $today
        ]);
        return redirect('/input-area')->with('success', 'Kode ' . $kode . ' Sukses Ditambahkan');
    }

    public function checkCode($branch, $kode)
    {
        $data = DB::connection('CSAREPORT')->select("SELECT fc_branch, kode_area FROM [CSAREPORT].[dbo].[t_area] WITH (NOLOCK) 
                                                     WHERE fc_branch = '$branch' AND kode_area = '$kode'");
        return $data;
    }
}
