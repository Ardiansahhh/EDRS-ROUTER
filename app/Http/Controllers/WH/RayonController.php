<?php

namespace App\Http\Controllers\WH;

use App\Http\Controllers\Controller;
use App\Models\RayonModel;
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

    public function setting($kode_rayon)
    {
        $user = Auth::user();
        $cek_toko = $this->getAllTemporaryby($user->fc_branch, null, 'branch');
        $count = count($this->getRayonDetail($kode_rayon, $user->fc_branch, 'allCodeRayon'));
        if ($cek_toko) {
            return view('rayon/setting', [
                'data' => $cek_toko,
                'rayon' => $kode_rayon,
                'isContent' => true,
                'total'     => $count
            ]);
        } else {
            return view('rayon/setting', [
                'isContent' => false,
                'rayon' => $kode_rayon,
                'total' => $count
            ]);
        }
    }

    public function load_rayon(Request $request)
    {
        $user = Auth::user();
        $check_temporary = DB::connection('CSAREPORT')->select("SELECT FC_BRANCH FROM [CSAREPORT].[dbo].[t_temporary_customer] 
                                                                WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch'");
        if ($check_temporary) {
            DB::connection('CSAREPORT')->delete("DELETE FROM [CSAREPORT].[dbo].[t_temporary_customer] WHERE FC_BRANCH = '$user->fc_branch'");
        }
        if (isset($_POST['load_rayon'])) {
            $true = true;
            if ($true) {
                $toko = DB::connection('other3')->select("SELECT FC_BRANCH, FC_CUSTCODE, FV_CUSTNAME, FV_CUSTADD1, FV_CUSTCITY
                                                  FROM [d_master].[dbo].[t_customer] WITH (NOLOCK) WHERE 
                                                  FC_BRANCH = '$user->fc_branch' AND FC_CUSTTYPE = 'PR' AND FC_CUSTHOLD = 'NO' ORDER BY FV_CUSTCITY");
                if ($toko) {
                    $guard = 0;
                    $will_insert = [];
                    foreach ($toko as $t) {
                        array_push($will_insert, [
                            'FC_BRANCH'   => $t->FC_BRANCH,
                            'FC_CUSTCODE' => $t->FC_CUSTCODE,
                            'FV_CUSTNAME' => $t->FV_CUSTNAME,
                            'FV_CUSTADD1' => $t->FV_CUSTADD1,
                            'FV_CUSTCITY' => $t->FV_CUSTCITY
                        ]);
                        if ($guard == 80) {
                            DB::connection('CSAREPORT')->table('t_temporary_customer')->insert($will_insert);
                            $guard = 0;
                            $will_insert = [];
                        }
                        $guard += 1;
                    }
                    if ($guard > 0) {
                        DB::connection('CSAREPORT')->table('t_temporary_customer')->insert($will_insert);
                        $guard = 0;
                        $will_insert = [];
                        return redirect('/setting-rayon/' . $request->kode_rayon)->with('success', 'Data Successfully');
                    }
                }
            }
        }
        return redirect('/rayon');
    }

    public function pilih_toko_rayon(Request $request)
    {
        $user = Auth::user();
        $length = strlen($request->FC_CUSTCODE);
        if ($length == 1) {
            $code = '00000' . $request->FC_CUSTCODE;
            $data = $this->getAllTemporaryby($user->fc_branch, $code, 'CodeCust');
            $detail = $this->getRayonDetail($code, $user->fc_branch, 'byCode');
            if (!$data) {
                return redirect('/setting-rayon/' . $request->kode_rayon)->with('session', 'Kode Customer ' . $code . ' Tidak Ditemukan');
            }

            if ($detail) {
                return redirect('/setting-rayon/' . $request->kode_rayon)->with('session', 'Kode Customer ' . $code . ' Sudah Ada di Rayon ' .  $detail[0]->kode_rayon);
            }
            $this->inputRayonDetail($data[0]->FC_BRANCH, $request->kode_rayon, $data[0]->FC_CUSTCODE, $data[0]->FV_CUSTNAME, $data[0]->FV_CUSTADD1, $data[0]->FV_CUSTCITY);
            return redirect('/setting-rayon/' . $request->kode_rayon)->with('success', 'Kode Customer ' . $code . ' Berhasil Ditambahkan');
        }

        if ($length == 2) {
            $code = '0000' . $request->FC_CUSTCODE;
            $data = $this->getAllTemporaryby($user->fc_branch, $code, 'CodeCust');
            $detail = $this->getRayonDetail($code, $user->fc_branch, 'byCode');
            if (!$data) {
                return redirect('/setting-rayon/' . $request->kode_rayon)->with('session', 'Kode Customer ' . $code . ' Tidak Ditemukan');
            }

            if ($detail) {
                return redirect('/setting-rayon/' . $request->kode_rayon)->with('session', 'Kode Customer ' . $code . ' Sudah Ada di Rayon ' .  $detail[0]->kode_rayon);
            }
            $this->inputRayonDetail($data[0]->FC_BRANCH, $request->kode_rayon, $data[0]->FC_CUSTCODE, $data[0]->FV_CUSTNAME, $data[0]->FV_CUSTADD1, $data[0]->FV_CUSTCITY);
            return redirect('/setting-rayon/' . $request->kode_rayon)->with('success', 'Kode Customer ' . $code . ' Berhasil Ditambahkan');
        }

        if ($length == 3) {
            $code = '000' . $request->FC_CUSTCODE;
            $data = $this->getAllTemporaryby($user->fc_branch, $code, 'CodeCust');
            $detail = $this->getRayonDetail($code, $user->fc_branch, 'byCode');
            if (!$data) {
                return redirect('/setting-rayon/' . $request->kode_rayon)->with('session', 'Kode Customer ' . $code . ' Tidak Ditemukan');
            }

            if ($detail) {
                return redirect('/setting-rayon/' . $request->kode_rayon)->with('session', 'Kode Customer ' . $code . ' Sudah Ada di Rayon ' .  $detail[0]->kode_rayon);
            }
            $this->inputRayonDetail($data[0]->FC_BRANCH, $request->kode_rayon, $data[0]->FC_CUSTCODE, $data[0]->FV_CUSTNAME, $data[0]->FV_CUSTADD1, $data[0]->FV_CUSTCITY);
            return redirect('/setting-rayon/' . $request->kode_rayon)->with('success', 'Kode Customer ' . $code . ' Berhasil Ditambahkan');
        }

        if ($length == 4) {
            $code = '00' . $request->FC_CUSTCODE;
            $data = $this->getAllTemporaryby($user->fc_branch, $code, 'CodeCust');
            $detail = $this->getRayonDetail($code, $user->fc_branch, 'byCode');
            if (!$data) {
                return redirect('/setting-rayon/' . $request->kode_rayon)->with('session', 'Kode Customer ' . $code . ' Tidak Ditemukan');
            }

            if ($detail) {
                return redirect('/setting-rayon/' . $request->kode_rayon)->with('session', 'Kode Customer ' . $code . ' Sudah Ada di Rayon ' .  $detail[0]->kode_rayon);
            }
            $this->inputRayonDetail($data[0]->FC_BRANCH, $request->kode_rayon, $data[0]->FC_CUSTCODE, $data[0]->FV_CUSTNAME, $data[0]->FV_CUSTADD1, $data[0]->FV_CUSTCITY);
            return redirect('/setting-rayon/' . $request->kode_rayon)->with('success', 'Kode Customer ' . $code . ' Berhasil Ditambahkan');
        }

        if ($length == 5) {
            $code = '0' . $request->FC_CUSTCODE;
            $data = $this->getAllTemporaryby($user->fc_branch, $code, 'CodeCust');
            $detail = $this->getRayonDetail($code, $user->fc_branch, 'byCode');
            if (!$data) {
                return redirect('/setting-rayon/' . $request->kode_rayon)->with('session', 'Kode Customer ' . $code . ' Tidak Ditemukan');
            }

            if ($detail) {
                return redirect('/setting-rayon/' . $request->kode_rayon)->with('session', 'Kode Customer ' . $code . ' Sudah Ada di Rayon ' .  $detail[0]->kode_rayon);
            }
            $this->inputRayonDetail($data[0]->FC_BRANCH, $request->kode_rayon, $data[0]->FC_CUSTCODE, $data[0]->FV_CUSTNAME, $data[0]->FV_CUSTADD1, $data[0]->FV_CUSTCITY);
            return redirect('/setting-rayon/' . $request->kode_rayon)->with('success', 'Kode Customer ' . $code . ' Berhasil Ditambahkan');
        }

        if ($length == 6) {
            $code = $request->FC_CUSTCODE;
            $data = $this->getAllTemporaryby($user->fc_branch, $code, 'CodeCust');
            $detail = $this->getRayonDetail($code, $user->fc_branch, 'byCode');
            if (!$data) {
                return redirect('/setting-rayon/' . $request->kode_rayon)->with('session', 'Kode Customer ' . $code . ' Tidak Ditemukan');
            }

            if ($detail) {
                return redirect('/setting-rayon/' . $request->kode_rayon)->with('session', 'Kode Customer ' . $code . ' Sudah Ada di Rayon ' .  $detail[0]->kode_rayon);
            }
            $this->inputRayonDetail($data[0]->FC_BRANCH, $request->kode_rayon, $data[0]->FC_CUSTCODE, $data[0]->FV_CUSTNAME, $data[0]->FV_CUSTADD1, $data[0]->FV_CUSTCITY);
            return redirect('/setting-rayon/' . $request->kode_rayon)->with('success', 'Kode Customer ' . $code . ' Berhasil Ditambahkan');
        }
    }

    public function getRayonDetail($code, $branch, $by)
    {
        if ($by == 'byCode') {
            $toko = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_rayon_detail] WITH (NOLOCK) WHERE fc_branch = '$branch' AND fc_custcode = '$code'");
            return $toko;
        }

        if ($by == 'allCodeRayon') {
            $toko = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_rayon_detail] WITH (NOLOCK) WHERE fc_branch = '$branch' AND kode_rayon = '$code'");
            return $toko;
        }
    }

    public function getAllTemporaryby($branch, $code, $by)
    {
        if ($by == 'branch') {
            $cek_toko = DB::connection('CSAREPORT')->select("SELECT A.FC_BRANCH, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FV_CUSTADD1, A.FV_CUSTCITY
                                                             FROM [CSAREPORT].[dbo].[t_temporary_customer] A WITH (NOLOCK)
                                                             LEFT JOIN [CSAREPORT].[dbo].[t_rayon_detail] B WITH (NOLOCK)
                                                             ON A.FC_BRANCH = B.fc_branch AND A.FC_CUSTCODE = B.fc_custcode 
                                                             WHERE A.FC_BRANCH = '$branch'
                                                             AND B.fc_custcode IS NULL");
            return $cek_toko;
        }

        if ($by == 'CodeCust') {
            $toko = DB::connection('CSAREPORT')->select("SELECT A.FC_BRANCH, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FV_CUSTADD1, A.FV_CUSTCITY 
                                                     FROM [CSAREPORT].[dbo].[t_temporary_customer] A WITH (NOLOCK) 
                                                     WHERE FC_BRANCH = '$branch' AND FC_CUSTCODE = '$code'");
            return $toko;
        }
    }

    public function inputRayonDetail($branch, $rayon, $custcode, $custname, $address, $city)
    {
        DB::connection('CSAREPORT')->table('t_rayon_detail')->insert([
            'fc_branch'   => $branch,
            'kode_rayon'  => $rayon,
            'fc_custcode' => $custcode,
            'fv_custname' => $custname,
            'fv_custadd1' => $address,
            'fv_custcity' => $city
        ]);
    }
}
