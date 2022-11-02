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
        $toko = DB::connection('CSAREPORT')->select("SELECT A.fc_branch, A.kode_rayon, A.name, A.tanggal FROM [CSAREPORT].[dbo].[t_rayon] A WITH (NOLOCK)
                                                     WHERE fc_branch = '$user->fc_branch' AND is_hold = 'NO'");
        return view('rayon/index', ['data' => $toko]);
    }

    public function input()
    {
        $user = Auth::user();
        $data = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_area] WHERE fc_branch = '$user->fc_branch' AND setting = 'NO'");
        return view('rayon/input', ['data' => $data]);
    }

    // public function store(Request $request)
    // {
    //     date_default_timezone_set('Asia/Jakarta');
    //     if (isset($_POST['create_rayon'])) {
    //         $user = Auth::user();
    //         $today = date('d-m-Y H:i:s');
    //         $kode_rayon = strtoupper($request->kode_rayon);
    //         $cek_kode = DB::connection('CSAREPORT')->select("SELECT kode_rayon FROM [CSAREPORT].[dbo].[t_rayon] WHERE fc_branch = '$user->fc_branch' AND kode_rayon = '$kode_rayon'");
    //         if (!$cek_kode) {
    //             $data = [
    //                 'fc_branch'  => $user->fc_branch,
    //                 'kode_rayon' => $kode_rayon,
    //                 'name'       => $user->name,
    //                 'tanggal'    => $today
    //             ];
    //             DB::connection('CSAREPORT')->table('t_rayon')->insert($data);
    //             return redirect('/rayon')->with('success', 'Sukses ditambahkan');
    //         }
    //         return redirect()->back()->with('session', 'Kode Sudah digunakan');
    //     }
    // }

    public function store(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        if (isset($_POST['create_rayon'])) {
            $user  = Auth::user();
            $today = date('d-m-Y H:i:s');
            $set = '';
            foreach ($request->kode_area as $key => $value) {
                DB::connection('CSAREPORT')->update("UPDATE [CSAREPORT].[dbo].[t_area] 
                                                     SET setting = 'YES' 
                                                     WHERE 
                                                     fc_branch = '$user->fc_branch' AND 
                                                     kode_area = '$value'");
                if ($set == '') {
                    $set = $value;
                } else {
                    $set = $set . '-' . $value;
                }
            }
            $data = [
                'fc_branch'  => $user->fc_branch,
                'kode_rayon' => $set,
                'name'       => $user->name,
                'tanggal'    => $today,
                'is_hold'    => 'NO'
            ];
            DB::connection('CSAREPORT')->table('t_rayon')->insert($data);
            return redirect('/rayon')->with('success', 'Sukses ditambahkan');
        }
        return redirect()->back()->with('session', 'Kode Sudah digunakan');
    }

    public function setting($kode_rayon)
    {
        $user = Auth::user();
        $check = $this->getRayonDetail($kode_rayon, $user->fc_branch, 'CheckRayon');
        if (!$check) {
            return redirect('/rayon');
        }

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

            $checkData = DB::connection('sqlsrv')->select("SELECT FC_CUSTCODE, KODE_RAYON FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK)
                                                           WHERE FC_BRANCH = '$user->fc_branch' AND FC_CUSTCODE = '$code'");
            if ($checkData) {
                if ($checkData[0]->KODE_RAYON == 'Belum Ada') {
                    DB::connection('sqlsrv')->update("UPDATE [d_transaksi].[dbo].[temporarydetailorders] 
                                                      SET KODE_RAYON = '$request->kode_rayon'
                                                      WHERE FC_BRANCH = '$user->fc_branch' AND FC_CUSTCODE = '$code'");
                }
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

            $checkData = DB::connection('sqlsrv')->select("SELECT FC_CUSTCODE, KODE_RAYON FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK)
                                                           WHERE FC_BRANCH = '$user->fc_branch' AND FC_CUSTCODE = '$code'");
            if ($checkData) {
                if ($checkData[0]->KODE_RAYON == 'Belum Ada') {
                    DB::connection('sqlsrv')->update("UPDATE [d_transaksi].[dbo].[temporarydetailorders] 
                                                      SET KODE_RAYON = '$request->kode_rayon'
                                                      WHERE FC_BRANCH = '$user->fc_branch' AND FC_CUSTCODE = '$code'");
                }
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

            $checkData = DB::connection('sqlsrv')->select("SELECT FC_CUSTCODE, KODE_RAYON FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK)
                                                           WHERE FC_BRANCH = '$user->fc_branch' AND FC_CUSTCODE = '$code'");
            if ($checkData) {
                if ($checkData[0]->KODE_RAYON == 'Belum Ada') {
                    DB::connection('sqlsrv')->update("UPDATE [d_transaksi].[dbo].[temporarydetailorders] 
                                                      SET KODE_RAYON = '$request->kode_rayon'
                                                      WHERE FC_BRANCH = '$user->fc_branch' AND FC_CUSTCODE = '$code'");
                }
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

            $checkData = DB::connection('sqlsrv')->select("SELECT FC_CUSTCODE, KODE_RAYON FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK)
                                                           WHERE FC_BRANCH = '$user->fc_branch' AND FC_CUSTCODE = '$code'");
            if ($checkData) {
                if ($checkData[0]->KODE_RAYON == 'Belum Ada') {
                    DB::connection('sqlsrv')->update("UPDATE [d_transaksi].[dbo].[temporarydetailorders] 
                                                      SET KODE_RAYON = '$request->kode_rayon'
                                                      WHERE FC_BRANCH = '$user->fc_branch' AND FC_CUSTCODE = '$code'");
                }
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

            $checkData = DB::connection('sqlsrv')->select("SELECT FC_CUSTCODE, KODE_RAYON FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK)
                                                           WHERE FC_BRANCH = '$user->fc_branch' AND FC_CUSTCODE = '$code'");
            if ($checkData) {
                if ($checkData[0]->KODE_RAYON == 'Belum Ada') {
                    DB::connection('sqlsrv')->update("UPDATE [d_transaksi].[dbo].[temporarydetailorders] 
                                                      SET KODE_RAYON = '$request->kode_rayon'
                                                      WHERE FC_BRANCH = '$user->fc_branch' AND FC_CUSTCODE = '$code'");
                }
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

            $checkData = DB::connection('sqlsrv')->select("SELECT FC_CUSTCODE, KODE_RAYON FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK)
                                                           WHERE FC_BRANCH = '$user->fc_branch' AND FC_CUSTCODE = '$code'");
            if ($checkData) {
                if ($checkData[0]->KODE_RAYON == 'Belum Ada') {
                    DB::connection('sqlsrv')->update("UPDATE [d_transaksi].[dbo].[temporarydetailorders] 
                                                      SET KODE_RAYON = '$request->kode_rayon'
                                                      WHERE FC_BRANCH = '$user->fc_branch' AND FC_CUSTCODE = '$code'");
                }
            }
            $this->inputRayonDetail($data[0]->FC_BRANCH, $request->kode_rayon, $data[0]->FC_CUSTCODE, $data[0]->FV_CUSTNAME, $data[0]->FV_CUSTADD1, $data[0]->FV_CUSTCITY);
            return redirect('/setting-rayon/' . $request->kode_rayon)->with('success', 'Kode Customer ' . $code . ' Berhasil Ditambahkan');
        }
    }

    public function detailRayon($rayon)
    {
        $user = Auth::user();
        $check = $this->getRayonDetail($rayon, $user->fc_branch, 'CheckRayon');
        if (!$check) {
            return redirect('/rayon');
        }
        $data = $this->getRayonDetail($rayon, $user->fc_branch, 'allCodeRayon');
        if (!$data) {
            return redirect('/rayon');
        }
        return view('rayon/detail_toko_rayon', [
            'data' => $data,
            'rayon' => $rayon,
            'isContent' => true,
            'total'     => count($data)
        ]);
    }

    public function checkbox_rayon(Request $request)
    {
        if (empty($request->input('FC_CUSTCODE'))) {
            return redirect('/setting-rayon/' . $request->kode_rayon);
        }

        $branch = $request->FC_BRANCH;
        $rayon  = $request->kode_rayon;
        $will_insert = [];
        $guard  = 0;
        foreach ($request->FC_CUSTCODE as $key => $code) {
            $check_toko = $this->getRayonDetail($code, $branch, 'byCode');
            if (!$check_toko) {
                $toko = $this->getAllTemporaryby($branch, $code, 'CodeCust');
                array_push($will_insert, [
                    'fc_branch'   => $branch,
                    'kode_rayon'  => $rayon,
                    'fc_custcode' => $code,
                    'fv_custname' => $toko[0]->FV_CUSTNAME,
                    'fv_custadd1' => $toko[0]->FV_CUSTADD1,
                    'fv_custcity' => $toko[0]->FV_CUSTCITY
                ]);
                if ($guard == 80) {
                    DB::connection('CSAREPORT')->table('t_rayon_detail')->insert($will_insert);
                    $will_insert = [];
                    $guard = 0;
                }
                $guard += 1;
            }
        }
        if ($guard > 0) {
            DB::connection('CSAREPORT')->table('t_rayon_detail')->insert($will_insert);
            $will_insert = [];
            $guard = 0;
        }
        return redirect('/setting-rayon/' . $rayon)->with('success', 'Data Berhasil Ditambahkan');
    }

    public function hapus_toko_rayon(Request $request)
    {
        if (!isset($_POST['hapus_toko'])) {
            return redirect('rayon');
        }
        DB::connection('sqlsrv')->update("UPDATE [d_transaksi].[dbo].[temporarydetailorders] 
                                          SET KODE_RAYON  = 'Belum Ada'
                                          WHERE FC_BRANCH = '$request->fc_branch' AND
                                          FC_CUSTCODE     = '$request->fc_custcode'");
        DB::connection('CSAREPORT')->delete("DELETE FROM [CSAREPORT].[dbo].[t_rayon_detail] 
                                             WHERE 
                                             fc_branch   = '$request->fc_branch' AND 
                                             kode_rayon  = '$request->kode_rayon' AND 
                                             fc_custcode = '$request->fc_custcode'");
        return redirect('/detail-toko-rayon/' . $request->kode_rayon)->with('success', 'Data Berhasil Dihapus');
    }

    public function hold_rayon(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        $today = date('d-m-Y H:i:s');
        $user = Auth::user();
        $rayon = $request->kode_rayon;
        $getData = $this->getRayonDetail($rayon, $user->fc_branch, 'CheckRayon');
        $allData = $this->getRayonDetail($rayon, $user->fc_branch, 'allCodeRayon');
        $data = explode('-', $getData[0]->kode_rayon);
        foreach ($data as $d) {
            DB::connection('CSAREPORT')->update("UPDATE [CSAREPORT].[dbo].[t_area] 
                                                 SET setting = 'NO' 
                                                 WHERE fc_branch = '$user->fc_branch' AND 
                                                 kode_area = '$d'");
        }
        $will_insert = [];
        $guard = 0;
        foreach ($allData as $a) {
            array_push($will_insert, [
                'fc_branch'   => $a->fc_branch,
                'kode_rayon'  => $a->kode_rayon,
                'fc_custcode' => $a->fc_custcode,
                'fv_custname' => $a->fv_custname,
                'fv_custadd1' => $a->fv_custadd1,
                'fv_custcity' => $a->fv_custcity,
                'is_hold'     => 'YES'
            ]);
            if ($guard == 80) {
                DB::connection('CSAREPORT')->table('t_hold')->insert($will_insert);
                $will_insert = [];
                $guard = 0;
            }
            $guard += 1;
        }
        if ($guard > 0) {
            DB::connection('CSAREPORT')->table('t_hold')->insert($will_insert);
            $will_insert = [];
            $guard = 0;
        }
        $temporary = DB::connection('sqlsrv')->select("SELECT * FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch' AND KODE_RAYON = '$rayon'");
        $insert = [];
        $guard_insert = 0;
        foreach ($temporary as $t) {
            array_push($insert, [
                'FC_BRANCH'     => $t->FC_BRANCH,
                'FV_BRANDNAME'  => $t->FV_BRANDNAME,
                'FC_SONO'       => $t->FC_SONO,
                'FD_SODATE'     => $t->FD_SODATE,
                'FV_CUSTNAME'   => $t->FV_CUSTNAME,
                'FC_CUSTCODE'   => $t->FC_CUSTCODE,
                'FV_STOCKNAME'  => $t->FV_STOCKNAME,
                'FC_STOCKCODE'  => $t->FC_STOCKCODE,
                'FC_REGIONDESC' => $t->FC_REGIONDESC,
                'SHIPNAME'      => $t->SHIPNAME,
                'SHIPADDRESS'   => $t->SHIPADDRESS,
                'FC_CUSTTYPE'   => $t->FC_CUSTTYPE,
                'FC_CUSTJENIS'  => $t->FC_CUSTJENIS,
                'Uom'           => $t->UoM,
                'FN_QTY'        => $t->FN_QTY,
                'FN_EXTRA'      => $t->FN_EXTRA,
                'KUBIKASI'      => $t->KUBIKASI,
                'KODE_RAYON'    => "Belum Ada"
            ]);
            if ($guard_insert == 80) {
                DB::connection('sqlsrv')->table('temporarydetailorders')->insert($insert);
                $insert = [];
                $guard_insert = 0;
            }
            $guard_insert += 1;
        }
        if ($guard_insert > 0) {
            DB::connection('sqlsrv')->table('temporarydetailorders')->insert($insert);
            $insert = [];
            $guard_insert = 0;
        }
        DB::connection('sqlsrv')->delete("DELETE FROM [d_transaksi].[dbo].[temporarydetailorders] WHERE FC_BRANCH = '$user->fc_branch' AND KODE_RAYON = '$rayon'");
        DB::connection('CSAREPORT')->update("UPDATE [CSAREPORT].[dbo].[t_rayon]
                                                 SET is_hold = 'YES', tanggal_hold = '$today'
                                                 WHERE
                                                 fc_branch = '$user->fc_branch' AND
                                                 kode_rayon = '$rayon'");
        DB::connection('CSAREPORT')->delete("DELETE FROM [CSAREPORT].[dbo].[t_rayon_detail]
                                             WHERE
                                             fc_branch = '$user->fc_branch' AND
                                             kode_rayon = '$rayon'");
        return redirect('/rayon')->with('success', 'Kode Rayon Sudah Di Hold');
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

        if ($by == 'CheckRayon') {
            $rayon = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_rayon] WITH (NOLOCK) WHERE fc_branch = '$branch' AND kode_rayon = '$code'");
            return $rayon;
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
                                                             AND B.fc_custcode IS NULL
                                                             ORDER BY A.FC_CUSTCODE");
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
