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
        $check_dc = DB::connection('other')->select("SELECT CODE_STOF, FC_BRANCH, SATELITE_OFFICE
                                                     FROM [d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$user->fc_branch'");
        if ($check_dc) {
            $data = DB::connection('CSAREPORT')->select("SELECT fc_branch, kode_area, nama_area, 
                                                            CASE 
                                                                WHEN code_stof = fc_branch THEN '-'
                                                                ELSE code_stof
                                                            END AS code_stof
                                                         FROM [CSAREPORT].[dbo].[t_area] WHERE fc_branch = '$user->fc_branch'
                                                         ORDER BY code_stof");
            return view('area/index', ['data' => $data, 'dc' => true]);
        } else {
            $data = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_area] WHERE fc_branch = '$user->fc_branch'");
            return view('area/index', ['data' => $data, 'dc' => false]);
        }
    }

    public function input()
    {
        $user = Auth::user();
        $check_dc = DB::connection('other')->select("SELECT CODE_STOF, FC_BRANCH, SATELITE_OFFICE FROM [d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$user->fc_branch'");
        if ($check_dc) {
            $data = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_area] WHERE fc_branch = '$user->fc_branch'");
            return view('area/input_dc', ['data' => $data, 'code_stof' => $check_dc]);
        } else {
            $data = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_area] WHERE fc_branch = '$user->fc_branch'");
            return view('area/input', ['data' => $data]);
        }
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
            return redirect('/input-area')->with('session1', 'Kode ' . $kode . ' Sudah Digunakan');
        }
        DB::connection('CSAREPORT')->table('t_area')->insert([
            'fc_branch' => $branch,
            'kode_area' => $kode,
            'nama_area' => $nama,
            'setting'   => 'NO',
            'nama'      => $user->name,
            'create_at' => $today,
            'code_stof' => $branch
        ]);
        return redirect('/input-area')->with('success1', 'Kode ' . $kode . ' Sukses Ditambahkan');
    }

    public function store_dc(Request $request)
    {
        if (!isset($_POST['create_area_dc'])) {
            return redirect('/area');
        }
        date_default_timezone_set('Asia/Jakarta');
        $user   = Auth::user();
        $branch = $request->CODE_STOF;
        $today  = date('d-m-Y H:i:s');
        $kode   = strtoupper($request->kode_area);
        $nama   = strtoupper($request->nama_area);
        $data = DB::connection('CSAREPORT')->select("SELECT code_stof, kode_area FROM [CSAREPORT].[dbo].[t_area] WITH (NOLOCK) 
                                                     WHERE code_stof = '$branch' AND kode_area = '$kode'");;
        if ($data) {
            return redirect('/input-area')->with('session2', 'Kode ' . $kode . ' Sudah Digunakan');
        } else {
            DB::connection('CSAREPORT')->table('t_area')->insert([
                'fc_branch' => $user->fc_branch,
                'kode_area' => $kode,
                'nama_area' => $nama,
                'setting'   => 'NO',
                'nama'      => $user->name,
                'create_at' => $today,
                'code_stof' => $request->CODE_STOF
            ]);
            return redirect('/input-area')->with('success2', 'Kode ' . $kode . ' Sukses Ditambahkan');
        }
    }

    public function checkCode($branch, $kode)
    {
        $data = DB::connection('CSAREPORT')->select("SELECT fc_branch, kode_area FROM [CSAREPORT].[dbo].[t_area] WITH (NOLOCK) 
                                                     WHERE fc_branch = '$branch' AND kode_area = '$kode'");
        return $data;
    }
}
