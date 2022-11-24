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
        $check_dc = DB::connection('other')->select("SELECT CODE_STOF, FC_BRANCH, SATELITE_OFFICE FROM [d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$user->fc_branch'");
        if ($check_dc) {
            $data = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_area] WHERE fc_branch = '$user->fc_branch' AND setting = 'NO' ORDER BY code_stof");
            return view('rayon/input', ['data' => $data, 'dc' => true]);
        } else {
            $data = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_area] WHERE fc_branch = '$user->fc_branch' AND setting = 'NO' ORDER BY code_stof");
            return view('rayon/input', ['data' => $data, 'dc' => false]);
        }
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
        // $check = $this->getRayonDetail($kode_rayon, $user->fc_branch, 'CheckRayon');
        // if (!$check) {
        //     return redirect('/rayon');
        // }

        $cek_toko = DB::connection('CSAREPORT')->select("SELECT A.FC_BRANCH, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FV_CUSTADD1, A.FV_CUSTCITY, A.FC_SHIPCODE, A.FV_SHIPADD1, A.CODE_STOF
                                                         FROM [CSAREPORT].[dbo].[t_temporary_customer] A WITH (NOLOCK)
                                                         LEFT JOIN [CSAREPORT].[dbo].[t_rayon_detail] B WITH (NOLOCK)
                                                         ON A.CODE_STOF = B.code_stof AND A.FC_CUSTCODE = B.fc_custcode AND A.FC_SHIPCODE = B.fc_shipcode
                                                         WHERE A.FC_BRANCH = '$user->fc_branch' 
                                                         AND B.fc_custcode IS NULL
                                                         ORDER BY A.CODE_STOF");
        $count = count($this->getRayonDetail($kode_rayon, $user->fc_branch, 'allCodeRayon'));

        $check_dc = DB::connection('other')->select("SELECT CODE_STOF, FC_BRANCH, SATELITE_OFFICE
                                                     FROM [d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$user->fc_branch'");
        if ($cek_toko) {
            if ($check_dc) {
                return view('rayon/setting', [
                    'data' => $cek_toko,
                    'rayon' => $kode_rayon,
                    'isContent' => true,
                    'total'     => $count,
                    'dc'        => $check_dc,
                    'is_dc'     => true
                ]);
            } else {
                return view('rayon/setting', [
                    'data'      => $cek_toko,
                    'rayon'     => $kode_rayon,
                    'isContent' => true,
                    'total'     => $count,
                    'is_dc'     => false
                ]);
            }
        } else {
            if ($check_dc) {
                return view('rayon/setting', [
                    'isContent' => false,
                    'rayon' => $kode_rayon,
                    'total' => $count,
                    'dc'    => $check_dc,
                    'is_dc' => true
                ]);
            } else {
                return view('rayon/setting', [
                    'isContent' => false,
                    'rayon' => $kode_rayon,
                    'total' => $count,
                    'is_dc' => false
                ]);
            }
        }
    }

    public function setting_shipto(Request $request)
    {
        if (!isset($_POST['setting_rayon'])) {
            return redirect('/rayon');
        }

        $user = Auth::user();
        $kode_rayon = $request->kode_rayon;
        $data = DB::connection('CSAREPORT')->select("SELECT A.* FROM [CSAREPORT].[dbo].[t_temporary_customer] A WITH (NOLOCK)
                                                    LEFT JOIN [CSAREPORT].[dbo].[t_rayon_detail] B WITH (NOLOCK)
                                                    ON A.FC_BRANCH = B.fc_branch AND A.FC_CUSTCODE = B.fc_custcode AND A.FC_SHIPCODE = B.fc_shipcode
                                                    WHERE A.FC_BRANCH = '$user->fc_branch' AND A.FC_SHIPCODE != '0' AND B.fc_custcode IS NULL");
        $count = count($this->getRayonDetail($kode_rayon, $user->fc_branch, 'allCodeRayon'));
        $check_dc = DB::connection('other')->select("SELECT CODE_STOF, FC_BRANCH, SATELITE_OFFICE
                                                     FROM [d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$user->fc_branch'");
        if ($data) {
            if ($check_dc) {
                return view('rayon/setting', [
                    'data'      => $data,
                    'rayon'     => $kode_rayon,
                    'isContent' => true,
                    'total'     => $count,
                    'dc'        => $check_dc,
                    'is_dc'     => true
                ]);
            } else {
                return view('rayon/setting', [
                    'data'      => $data,
                    'rayon'     => $kode_rayon,
                    'isContent' => true,
                    'total'     => $count,
                    'is_dc'     => false
                ]);
            }
        } else {
            if ($check_dc) {
                return view('rayon/setting', [
                    'isContent' => false,
                    'rayon' => $kode_rayon,
                    'total' => $count,
                    'dc'    => $check_dc,
                    'is_dc' => true
                ]);
            } else {
                return view('rayon/setting', [
                    'isContent' => false,
                    'rayon' => $kode_rayon,
                    'total' => $count,
                    'is_dc' => false
                ]);
            }
        }
    }

    public function load_rayon(Request $request)
    {
        $user = Auth::user();
        $branch = $user->fc_branch;
        $dc = DB::connection('other')->select("SELECT * FROM [d_master].[dbo].[t_dc] WHERE FC_BRANCH = '$user->fc_branch'");
        if ($dc) {
            // $check_temporary = DB::connection('CSAREPORT')->select("SELECT FC_BRANCH FROM [CSAREPORT].[dbo].[t_temporary_customer] 
            //                                                     WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch' AND CODE_STOF = '$request->fc_branch'");
            $check_temporary = DB::connection('CSAREPORT')->select("SELECT FC_BRANCH FROM [CSAREPORT].[dbo].[t_temporary_customer] 
                                                                WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch'");
            if ($check_temporary) {
                // DB::connection('CSAREPORT')->delete("DELETE FROM [CSAREPORT].[dbo].[t_temporary_customer] WHERE FC_BRANCH = '$user->fc_branch' AND CODE_STOF = '$request->fc_branch'");
                DB::connection('CSAREPORT')->delete("DELETE FROM [CSAREPORT].[dbo].[t_temporary_customer] WHERE FC_BRANCH = '$user->fc_branch'");
            }
            if (isset($_POST['load_rayon'])) {
                $dc_detail = DB::connection('other')->select("SELECT * FROM [d_master].[dbo].[t_dc_details] WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch'");
                $count = count($dc_detail);
                $toko = DB::connection('other3')->select("SELECT A.FC_BRANCH, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FV_CUSTADD1, A.FV_CUSTCITY, 
                                                                CASE 
                                                                     WHEN B.FC_SHIPCODE IS NULL THEN '0'
                                                                     WHEN B.FC_SHIPCODE = '' THEN '0'
                                                                ELSE B.FC_SHIPCODE
                                                            END AS SHIPTO, B.FC_SHIPCODE, B.FV_SHIPADD1
                                                            FROM [d_master].[dbo].[t_customer] A WITH (NOLOCK)
                                                            LEFT JOIN [d_master].[dbo].[t_custship] B WITH (NOLOCK)
                                                            ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_CUSTCODE = B.FC_CUSTCODE
                                                            WHERE 
                                                            A.FC_BRANCH IN (SELECT CODE_STOF FROM [192.169.1.21].[d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$user->fc_branch') 
                                                            AND A.FC_CUSTTYPE = 'PR' AND A.FC_CUSTHOLD = 'NO' ORDER BY FV_CUSTCITY");
                if ($toko) {
                    $guard = 0;
                    $will_insert = [];
                    foreach ($toko as $t) {
                        array_push($will_insert, [
                            'FC_BRANCH'   => $user->fc_branch,
                            'FC_CUSTCODE' => $t->FC_CUSTCODE,
                            'FV_CUSTNAME' => $t->FV_CUSTNAME,
                            'FV_CUSTADD1' => $t->FV_CUSTADD1,
                            'FV_CUSTCITY' => $t->FV_CUSTCITY,
                            'FC_SHIPCODE' => $t->SHIPTO,
                            'FV_SHIPADD1' => $t->FV_SHIPADD1,
                            'CODE_STOF'   => $t->FC_BRANCH
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
                return redirect('/area');
            }
            return redirect('/area');
        } else {
            $check_ = DB::connection('CSAREPORT')->select("SELECT FC_BRANCH FROM [CSAREPORT].[dbo].[t_temporary_customer] 
                                                                    WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch'");
            if ($check_) {
                DB::connection('CSAREPORT')->delete("DELETE FROM [CSAREPORT].[dbo].[t_temporary_customer] WHERE FC_BRANCH = '$user->fc_branch'");
            }
            if (isset($_POST['load_rayon'])) {
                $true = true;
                if ($true) {
                    $toko = DB::connection('other3')->select("SELECT A.FC_BRANCH, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FV_CUSTADD1, A.FV_CUSTCITY, 
                                                                CASE 
                                                                     WHEN B.FC_SHIPCODE IS NULL THEN '0'
                                                                     WHEN B.FC_SHIPCODE = '' THEN '0'
                                                                ELSE B.FC_SHIPCODE
                                                            END AS SHIPTO, B.FC_SHIPCODE, B.FV_SHIPADD1
                                                            FROM [d_master].[dbo].[t_customer] A WITH (NOLOCK)
                                                            LEFT JOIN [d_master].[dbo].[t_custship] B WITH (NOLOCK)
                                                            ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_CUSTCODE = B.FC_CUSTCODE
                                                            WHERE 
                                                            A.FC_BRANCH = '$user->fc_branch' AND A.FC_CUSTTYPE = 'PR' AND A.FC_CUSTHOLD = 'NO' ORDER BY FV_CUSTCITY");
                    if ($toko) {
                        $guard = 0;
                        $will_insert = [];
                        foreach ($toko as $t) {
                            array_push($will_insert, [
                                'FC_BRANCH'   => $user->fc_branch,
                                'FC_CUSTCODE' => $t->FC_CUSTCODE,
                                'FV_CUSTNAME' => $t->FV_CUSTNAME,
                                'FV_CUSTADD1' => $t->FV_CUSTADD1,
                                'FV_CUSTCITY' => $t->FV_CUSTCITY,
                                'FC_SHIPCODE' => $t->SHIPTO,
                                'FV_SHIPADD1' => $t->FV_SHIPADD1,
                                'CODE_STOF'   => $t->FC_BRANCH
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
                    return redirect('/rayon');
                }
            }
            return redirect('/rayon');
        }
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

        if ($length > 6) {
            return redirect('/setting-rayon/' . $request->kode_rayon)->with('session', 'Kode Customer lebih dari 6 karakter');
        }
    }

    public function detailRayon($rayon)
    {
        $user = Auth::user();
        $take_code_stof = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_area] WITH (NOLOCK) WHERE fc_branch = '$user->fc_branch' AND kode_area = '$rayon'");
        if (!$take_code_stof) {
            return redirect('/area');
        } else {
            $code = $take_code_stof[0]->code_stof;
            $check = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_rayon_detail] WITH (NOLOCK) WHERE fc_branch = '$user->fc_branch' AND kode_rayon = '$rayon'");
            if (!$check) {
                return redirect('/area');
            }
            $data = DB::connection('CSAREPORT')->select("SELECT fc_branch, kode_rayon, fc_custcode AS code_customer_real,
                                                        CASE 
                                                                WHEN fc_shipcode IS NOT NULL THEN CONCAT(fc_custcode,'-', fc_shipcode)
                                                            ELSE fc_custcode
                                                        END AS code_customer
                                                        , fv_custname, fv_custcity, fc_shipcode,
                                                        CASE 
                                                                WHEN fv_shipadd1 IS NOT NULL THEN fv_shipadd1
                                                            ELSE fv_custadd1 
                                                        END AS alamat
                                                     FROM [CSAREPORT].[dbo].[t_rayon_detail] A WITH (NOLOCK)
                                                     WHERE fc_branch = '$user->fc_branch' AND kode_rayon = '$rayon'");
            $check_dc = DB::connection('other')->select("SELECT CODE_STOF, FC_BRANCH, SATELITE_OFFICE
                                                     FROM [d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$user->fc_branch'");
            if (!$data) {
                return redirect('/area');
            } else {
                if ($check_dc) {
                    return view('rayon/detail_toko_rayon', [
                        'data' => $data,
                        'rayon' => $rayon,
                        'isContent' => true,
                        'total'     => count($data),
                        'dc'        => $check_dc,
                        'is_dc'     => true
                    ]);
                } else {
                    return view('rayon/detail_toko_rayon', [
                        'data' => $data,
                        'rayon' => $rayon,
                        'isContent' => true,
                        'total'     => count($data),
                        'is_dc'     => false
                    ]);
                }
            }
        }
    }

    public function search_customer(Request $request)
    {
        if (!isset($_POST['search_toko'])) {
            return redirect('/area');
        }

        $user = Auth::user();
        $kode_rayon = $request->kode_rayon;
        if ($request->pilih == 'FC_CUSTCODE') {
            $data = DB::connection('CSAREPORT')->select("SELECT A.* FROM [CSAREPORT].[dbo].[t_temporary_customer] A WITH (NOLOCK)
                                                         LEFT JOIN [CSAREPORT].[dbo].[t_rayon_detail] B WITH (NOLOCK) ON
                                                         A.FC_BRANCH = B.fc_branch AND A.FC_CUSTCODE = B.fc_custcode AND A.FC_SHIPCODE = B.fc_shipcode
                                                         WHERE A.FC_BRANCH =  '$user->fc_branch' AND
                                                               A.FC_CUSTCODE LIKE '%$request->search%' AND
                                                         B.fc_shipcode IS NULL");
            $count = count($this->getRayonDetail($kode_rayon, $user->fc_branch, 'allCodeRayon'));
            $check_dc = DB::connection('other')->select("SELECT CODE_STOF, FC_BRANCH, SATELITE_OFFICE
                                                     FROM [d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$user->fc_branch'");
            if ($data) {
                if ($check_dc) {
                    return view('rayon/setting', [
                        'data' => $data,
                        'rayon' => $kode_rayon,
                        'isContent' => true,
                        'total'     => $count,
                        'dc'        => $check_dc,
                        'is_dc'     => true
                    ]);
                } else {
                    return view('rayon/setting', [
                        'data' => $data,
                        'rayon' => $kode_rayon,
                        'isContent' => true,
                        'total'     => $count,
                        'is_dc'     => false
                    ]);
                }
            } else {
                if ($check_dc) {
                    return view('rayon/setting', [
                        'isContent' => false,
                        'rayon' => $kode_rayon,
                        'total' => $count,
                        'dc'    => $check_dc,
                        'is_dc' => true
                    ]);
                } else {
                    return view('rayon/setting', [
                        'isContent' => false,
                        'rayon' => $kode_rayon,
                        'total' => $count,
                        'is_dc' => false
                    ]);
                }
            }
        } elseif ($request->pilih == 'FV_CUSTNAME') {
            $data = DB::connection('CSAREPORT')->select("SELECT A.* FROM [CSAREPORT].[dbo].[t_temporary_customer] A WITH (NOLOCK)
                                                         LEFT JOIN [CSAREPORT].[dbo].[t_rayon_detail] B WITH (NOLOCK) ON
                                                         A.FC_BRANCH = B.fc_branch AND A.FC_CUSTCODE = B.fc_custcode AND A.FC_SHIPCODE = B.fc_shipcode
                                                         WHERE A.FC_BRANCH =  '$user->fc_branch' AND
                                                               A.FV_CUSTNAME LIKE '%$request->search%' AND
                                                         B.fc_shipcode IS NULL");
            $count = count($this->getRayonDetail($kode_rayon, $user->fc_branch, 'allCodeRayon'));
            $check_dc = DB::connection('other')->select("SELECT CODE_STOF, FC_BRANCH, SATELITE_OFFICE
                                                     FROM [d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$user->fc_branch'");
            if ($data) {
                if ($check_dc) {
                    return view('rayon/setting', [
                        'data' => $data,
                        'rayon' => $kode_rayon,
                        'isContent' => true,
                        'total'     => $count,
                        'dc'        => $check_dc,
                        'is_dc'     => true
                    ]);
                } else {
                    return view('rayon/setting', [
                        'data' => $data,
                        'rayon' => $kode_rayon,
                        'isContent' => true,
                        'total'     => $count,
                        'is_dc'     => false
                    ]);
                }
            } else {
                if ($check_dc) {
                    return view('rayon/setting', [
                        'isContent' => false,
                        'rayon' => $kode_rayon,
                        'total' => $count,
                        'dc'    => $check_dc,
                        'is_dc' => true
                    ]);
                } else {
                    return view('rayon/setting', [
                        'isContent' => false,
                        'rayon' => $kode_rayon,
                        'total' => $count,
                        'is_dc' => false
                    ]);
                }
            }
        } elseif ($request->pilih == 'FV_CUSTADD1') {
            $data = DB::connection('CSAREPORT')->select("SELECT A.FC_BRANCH, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FV_CUSTCITY, A.FC_SHIPCODE, A.FV_CUSTADD1 AS ALAMAT, A.CODE_STOF
                                                         FROM [CSAREPORT].[dbo].[t_temporary_customer] A WITH (NOLOCK)
                                                         LEFT JOIN [CSAREPORT].[dbo].[t_rayon_detail] B WITH (NOLOCK) ON
                                                         A.FC_BRANCH = B.fc_branch AND A.FC_CUSTCODE = B.fc_custcode AND A.FC_SHIPCODE = B.fc_shipcode
                                                         WHERE A.FC_BRANCH =  '$user->fc_branch' AND
                                                               A.FV_CUSTADD1 LIKE '%$request->search%' AND
                                                         B.fc_shipcode IS NULL AND A.FV_SHIPADD1 IS NULL");
            $count = count($this->getRayonDetail($kode_rayon, $user->fc_branch, 'allCodeRayon'));
            $check_dc = DB::connection('other')->select("SELECT CODE_STOF, FC_BRANCH, SATELITE_OFFICE
                                                     FROM [d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$user->fc_branch'");
            if ($data) {
                if ($check_dc) {
                    return view('rayon/result_alamat', [
                        'data' => $data,
                        'rayon' => $kode_rayon,
                        'isContent' => true,
                        'total'     => $count,
                        'dc'        => $check_dc,
                        'is_dc'     => true
                    ]);
                } else {
                    return view('rayon/result_alamat', [
                        'data' => $data,
                        'rayon' => $kode_rayon,
                        'isContent' => true,
                        'total'     => $count,
                        'is_dc'     => false
                    ]);
                }
            } else {
                if ($check_dc) {
                    return view('rayon/result_alamat', [
                        'isContent' => false,
                        'rayon' => $kode_rayon,
                        'total' => $count,
                        'dc'    => $check_dc,
                        'is_dc' => true
                    ]);
                } else {
                    return view('rayon/result_alamat', [
                        'isContent' => false,
                        'rayon' => $kode_rayon,
                        'total' => $count,
                        'is_dc' => false
                    ]);
                }
            }
        } elseif ($request->pilih == 'FV_SHIPADD1') {
            $data = DB::connection('CSAREPORT')->select("SELECT A.FC_BRANCH, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FV_CUSTCITY, A.FC_SHIPCODE, A.FV_SHIPADD1 AS ALAMAT, A.CODE_STOF
                                                         FROM [CSAREPORT].[dbo].[t_temporary_customer] A WITH (NOLOCK)
                                                         LEFT JOIN [CSAREPORT].[dbo].[t_rayon_detail] B WITH (NOLOCK) ON
                                                         A.FC_BRANCH = B.fc_branch AND A.FC_CUSTCODE = B.fc_custcode AND A.FC_SHIPCODE = B.fc_shipcode
                                                         WHERE A.FC_BRANCH =  '$user->fc_branch' AND
                                                               A.FV_SHIPADD1 LIKE '%$request->search%' AND
                                                         B.fc_shipcode IS NULL");
            $count = count($this->getRayonDetail($kode_rayon, $user->fc_branch, 'allCodeRayon'));
            $check_dc = DB::connection('other')->select("SELECT CODE_STOF, FC_BRANCH, SATELITE_OFFICE
                                                     FROM [d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$user->fc_branch'");
            if ($data) {
                if ($check_dc) {
                    return view('rayon/result_alamat', [
                        'data' => $data,
                        'rayon' => $kode_rayon,
                        'isContent' => true,
                        'total'     => $count,
                        'dc'        => $check_dc,
                        'is_dc'     => true
                    ]);
                } else {
                    return view('rayon/result_alamat', [
                        'data' => $data,
                        'rayon' => $kode_rayon,
                        'isContent' => true,
                        'total'     => $count,
                        'is_dc'     => false
                    ]);
                }
            } else {
                if ($check_dc) {
                    return view('rayon/result_alamat', [
                        'isContent' => false,
                        'rayon' => $kode_rayon,
                        'total' => $count,
                        'dc'    => $check_dc,
                        'is_dc' => true
                    ]);
                } else {
                    return view('rayon/result_alamat', [
                        'isContent' => false,
                        'rayon' => $kode_rayon,
                        'total' => $count,
                        'is_dc' => false
                    ]);
                }
            }
        }
    }

    public function checkbox_rayon(Request $request)
    {
        if (empty($request->input('FC_CUSTCODE'))) {
            return redirect('/setting-rayon/' . $request->kode_rayon);
        }

        $user   = Auth::user();
        $rayon  = $request->kode_rayon;
        $will_insert = [];
        $guard  = 0;
        foreach ($request->FC_CUSTCODE as $key => $code) {
            $shipto = explode('-', $code);
            //array_push($will_insert, ["customer" => $shipto[0], "shipcode" => $shipto[1]]);
            if ($shipto[1]) {
                $check_toko = DB::connection('CSAREPORT')->select("SELECT fc_branch, kode_rayon, fc_custcode, fc_shipcode
                                                               FROM [CSAREPORT].[dbo].[t_rayon_detail] A WITH (NOLOCK)
                                                               WHERE A.code_stof = '$shipto[2]' AND A.fc_custcode = '$shipto[0]' AND fc_shipcode = '$shipto[1]'");
                if (!$check_toko) {
                    $toko = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_temporary_customer] A WITH (NOLOCK)
                                                             WHERE A.CODE_STOF = '$shipto[2]' AND A.FC_CUSTCODE = '$shipto[0]' AND A.FC_SHIPCODE = '$shipto[1]'");
                    array_push($will_insert, [
                        'fc_branch'   => $user->fc_branch,
                        'kode_rayon'  => $rayon,
                        'fc_custcode' => $shipto[0],
                        'fv_custname' => $toko[0]->FV_CUSTNAME,
                        'fv_custadd1' => $toko[0]->FV_CUSTADD1,
                        'fv_custcity' => $toko[0]->FV_CUSTCITY,
                        'fc_shipcode' => $toko[0]->FC_SHIPCODE,
                        'fv_shipadd1' => $toko[0]->FV_SHIPADD1,
                        'code_stof'   => $shipto[2]
                    ]);
                    if ($guard == 80) {
                        DB::connection('CSAREPORT')->table('t_rayon_detail')->insert($will_insert);
                        $will_insert = [];
                        $guard = 0;
                    }
                    $guard += 1;
                }
            } else {
                $check_toko = DB::connection('CSAREPORT')->select("SELECT fc_branch, kode_rayon, fc_custcode, fc_shipcode
                                                                   FROM [CSAREPORT].[dbo].[t_rayon_detail] A WITH (NOLOCK)
                                                                   WHERE A.code_stof = '$shipto[2]' AND A.fc_custcode = '$shipto[0]'");
                if (!$check_toko) {
                    $toko = DB::connection('CSAREPORT')->select("SELECT * FROM [CSAREPORT].[dbo].[t_temporary_customer] A WITH (NOLOCK)
                                                                 WHERE A.CODE_STOF = '$shipto[2]' AND A.FC_CUSTCODE = '$shipto[0]'");
                    array_push($will_insert, [
                        'fc_branch'   => $user->fc_branch,
                        'kode_rayon'  => $rayon,
                        'fc_custcode' => $shipto[0],
                        'fv_custname' => $toko[0]->FV_CUSTNAME,
                        'fv_custadd1' => $toko[0]->FV_CUSTADD1,
                        'fv_custcity' => $toko[0]->FV_CUSTCITY,
                        'fc_shipcode' => $toko[0]->FC_SHIPCODE,
                        'fv_shipadd1' => $toko[0]->FV_SHIPADD1,
                        'code_stof'   => $shipto[2]
                    ]);
                    if ($guard == 80) {
                        DB::connection('CSAREPORT')->table('t_rayon_detail')->insert($will_insert);
                        $will_insert = [];
                        $guard = 0;
                    }
                    $guard += 1;
                }
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
        if ($request->fc_shipcode) {
            DB::connection('CSAREPORT')->delete("DELETE FROM [CSAREPORT].[dbo].[t_rayon_detail] 
                                             WHERE 
                                             fc_branch   = '$request->fc_branch' AND 
                                             kode_rayon  = '$request->kode_rayon' AND 
                                             fc_custcode = '$request->fc_custcode' AND
                                             fc_shipcode = '$request->fc_shipcode'");
        } else {
            DB::connection('CSAREPORT')->delete("DELETE FROM [CSAREPORT].[dbo].[t_rayon_detail] 
                                             WHERE 
                                             fc_branch   = '$request->fc_branch' AND 
                                             kode_rayon  = '$request->kode_rayon' AND 
                                             fc_custcode = '$request->fc_custcode'");
        }
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
            $cek_toko = DB::connection('CSAREPORT')->select("SELECT A.FC_BRANCH, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FV_CUSTADD1, A.FV_CUSTCITY, A.FC_SHIPCODE, A.FV_SHIPADD1
                                                             FROM [CSAREPORT].[dbo].[t_temporary_customer] A WITH (NOLOCK)
                                                             LEFT JOIN [CSAREPORT].[dbo].[t_rayon_detail] B WITH (NOLOCK)
                                                             ON A.FC_BRANCH = B.fc_branch AND A.FC_CUSTCODE = B.fc_custcode AND A.FC_SHIPCODE = B.fc_shipcode
                                                             WHERE A.FC_BRANCH = '$branch' 
                                                             AND B.fc_custcode IS NULL
                                                             ORDER BY A.FC_CUSTCODE");
            return $cek_toko;
        }

        if ($by == 'CodeCust') {
            $toko = DB::connection('CSAREPORT')->select("SELECT A.FC_BRANCH, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FV_CUSTADD1, A.FV_CUSTCITY, A.FC_SHIPCODE, A.FV_SHIPADD1
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
