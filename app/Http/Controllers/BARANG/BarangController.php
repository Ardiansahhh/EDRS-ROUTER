<?php

namespace App\Http\Controllers\BARANG;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index()
    {
        $data = DB::connection('other')->select("SELECT * FROM [d_master].[dbo].[MASTER_KUBIKASI]");
        return view('barang/index', [
            "data" => $data,
            "empty" => false
        ]);
    }

    public function input()
    {
        $data = DB::connection('other3')->select("SELECT FV_BRANDNAME FROM [d_master].[dbo].[t_mbrand]");
        return view('barang/input', [
            "data" => $data
        ]);
    }

    public function store(Request $request)
    {
        if (isset($_POST['store_kubikasi'])) {
            if (!$request->KUBIKASI_CTN) {
                return redirect()->back()->with('error', 'Data Kubikasi Tidak Boleh Kosong');
            }
            date_default_timezone_set('Asia/Jakarta');
            $today = date('d-m-Y H:i:s');
            $user = Auth::user()->name;
            $stockcode  = $request->FC_STOCKCODE;
            $brand      = $request->FV_BRANDNAME;
            $volume     = (int)$request->KUBIKASI_CTN / 1000000;
            $volume_pcs = (int)$request->KUBIKASI_CTN / (int)$request->UOM;
            $cek_barang = DB::connection('other')->select("SELECT FC_STOCKCODE, FV_BRANDNAME FROM [d_master].[dbo].[MASTER_KUBIKASI] WHERE FV_BRANDNAME = '$brand' AND FC_STOCKCODE = '$stockcode'");
            if (!$cek_barang) {
                DB::connection('other')->table('MASTER_KUBIKASI')->insert([
                    'FV_BRANDNAME' => $brand,
                    'FC_STOCKCODE' => $stockcode,
                    'FV_STOCKNAME' => $request->FV_STOCKNAME,
                    'UOM'          => $request->UOM,
                    'VOLUME'       => $volume,
                    'KUBIKASI_CTN' => $request->KUBIKASI_CTN,
                    'KUBIKASI_PCS' => $volume_pcs,
                    'UPDATE_AT'    => $today,
                    'UPDATE_WITH'  => $user
                ]);
                $data = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.NOROUTING, A.FC_SONO, A.FC_STOCKCODE, 
                                                                 A.FV_STOCKNAME, A.FC_REGIONDESC, A.FN_QTY, A.FN_EXTRA
                                                          FROM [d_transaksi].[dbo].[routingdetailorders] A WITH (NOLOCK)
                                                          WHERE A.FC_STOCKCODE = '$stockcode' AND A.KUBIKASI = 0 AND A.CONFIRM = 'NO'");
                if ($data) {
                    $will_insert = [];
                    $guard = 0;
                    $master = DB::connection('other')->select("SELECT KUBIKASI_PCS FROM [d_master].[dbo].[MASTER_KUBIKASI] WITH (NOLOCK)
                                                               WHERE FV_BRANDNAME = '$brand' AND FC_STOCKCODE = '$stockcode'")[0];
                    foreach ($data as $d) {
                        if ($master) {
                            $qty = (int)$d->FN_QTY + (int)$d->FN_EXTRA;
                            $kubikasi = (int)$master->KUBIKASI_PCS * (int)$qty;
                            array_push($will_insert, [
                                'FC_BRANCH'    => $d->FC_BRANCH,
                                'NOROUTING'    => $d->NOROUTING,
                                'FC_SONO'      => $d->FC_SONO,
                                'FC_STOCKCODE' => $d->FC_STOCKCODE,
                                'FV_STOCKNAME' => $d->FV_STOCKNAME,
                                'FC_REGIONDESC' => $d->FC_REGIONDESC,
                                'FN_QTY'       => $d->FN_QTY,
                                'FN_EXTRA'     => $d->FN_EXTRA,
                                'KUBIKASI'     => $kubikasi,
                                'CONFIRM'      => 'NO'
                            ]);
                            if ($guard == 100) {
                                DB::connection('sqlsrv')->table('routingdetailorders')->insert($will_insert);
                                $will_insert = [];
                                $guard = 0;
                            }
                            $guard += 1;
                        }
                        echo 'Hubungi IT ada master barang gak ada';
                    }
                    if ($guard > 0) {
                        DB::connection('sqlsrv')->table('routingdetailorders')->insert($will_insert);
                        $will_insert = [];
                        $guard = 0;
                    }
                    DB::connection('sqlsrv')->delete("DELETE FROM [d_transaksi].[dbo].[routingdetailorders] 
                                                  WHERE 
                                                     FC_STOCKCODE = '$stockcode' AND 
                                                     KUBIKASI = 0 AND 
                                                     CONFIRM = 'NO'");
                    return redirect('/check')->with('success', 'Sukses Ditambahkan');
                }
                return redirect('/check')->with('success', 'Sukses Ditambahkan, tidak ada data routing yang diupdate');
            } else {
                DB::connection('other')->update("UPDATE [d_master].[dbo].[MASTER_KUBIKASI]
                                                 SET 
                                                    VOLUME       = $volume, 
                                                    KUBIKASI_CTN = $request->KUBIKASI_CTN,
                                                    KUBIKASI_PCS = $volume_pcs,
                                                    UPDATE_AT    = '$today',
                                                    UPDATE_WITH  = '$user'
                                                 WHERE FC_STOCKCODE = '$stockcode' AND FV_BRANDNAME = '$brand'");
                $data = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.NOROUTING, A.FC_SONO, A.FC_STOCKCODE, 
                                                                 A.FV_STOCKNAME, A.FC_REGIONDESC, A.FN_QTY, A.FN_EXTRA
                                                          FROM [d_transaksi].[dbo].[routingdetailorders] A WITH (NOLOCK)
                                                          WHERE A.FC_STOCKCODE = '$stockcode' AND A.KUBIKASI = 0 AND A.CONFIRM = 'NO'");
                if ($data) {
                    $will_insert = [];
                    $guard = 0;
                    $master = DB::connection('other')->select("SELECT KUBIKASI_PCS FROM [d_master].[dbo].[MASTER_KUBIKASI] WITH (NOLOCK)
                                                               WHERE FV_BRANDNAME = '$brand' AND FC_STOCKCODE = '$stockcode'")[0];
                    foreach ($data as $d) {
                        if ($master) {
                            $qty = (int)$d->FN_QTY + (int)$d->FN_EXTRA;
                            $kubikasi = (int)$master->KUBIKASI_PCS * (int)$qty;
                            array_push($will_insert, [
                                'FC_BRANCH'    => $d->FC_BRANCH,
                                'NOROUTING'    => $d->NOROUTING,
                                'FC_SONO'      => $d->FC_SONO,
                                'FC_STOCKCODE' => $d->FC_STOCKCODE,
                                'FV_STOCKNAME' => $d->FV_STOCKNAME,
                                'FC_REGIONDESC' => $d->FC_REGIONDESC,
                                'FN_QTY'       => $d->FN_QTY,
                                'FN_EXTRA'     => $d->FN_EXTRA,
                                'KUBIKASI'     => $kubikasi,
                                'CONFIRM'      => 'NO'
                            ]);
                            if ($guard == 100) {
                                DB::connection('sqlsrv')->table('routingdetailorders')->insert($will_insert);
                                $will_insert = [];
                                $guard = 0;
                            }
                            $guard += 1;
                        }
                        echo 'Hubungi IT ada master barang gak ada';
                    }
                    if ($guard > 0) {
                        DB::connection('sqlsrv')->table('routingdetailorders')->insert($will_insert);
                        $will_insert = [];
                        $guard = 0;
                    }
                    DB::connection('sqlsrv')->delete("DELETE FROM [d_transaksi].[dbo].[routingdetailorders] 
                                                  WHERE 
                                                     FC_STOCKCODE = '$stockcode' AND 
                                                     KUBIKASI = 0 AND 
                                                     CONFIRM = 'NO'");
                    return redirect('/check')->with('success', 'Sukses Ditambahkan');
                }
                return redirect('/check')->with('success', 'Sukses Ditambahkan, tidak ada data routing yang diupdate');
            }
            return redirect()->back()->with('error', 'Data Sudah Digunakan');
        }

        if (isset($_POST['store'])) {
            date_default_timezone_set('Asia/Jakarta');
            $today = date('d-m-Y H:i:s');
            $stockcode  = $request->FC_STOCKCODE;
            $brand      = $request->FV_BRANDNAME;
            $volume     = (int)$request->KUBIKASI_CTN / 1000000;
            $volume_pcs = (int)$request->KUBIKASI_CTN / (int)$request->UOM;
            $user       = Auth::user()->name;
            $cek_barang = DB::connection('other')->select("SELECT FC_STOCKCODE, FV_BRANDNAME FROM [d_master].[dbo].[MASTER_KUBIKASI] WITH (NOLOCK) WHERE FV_BRANDNAME = '$brand' AND FC_STOCKCODE = '$stockcode'");
            if (!$cek_barang) {
                DB::connection('other')->table('MASTER_KUBIKASI')->insert([
                    'FV_BRANDNAME' => $brand,
                    'FC_STOCKCODE' => $stockcode,
                    'FV_STOCKNAME' => $request->FV_STOCKNAME,
                    'UOM'          => $request->UOM,
                    'VOLUME'       => $volume,
                    'KUBIKASI_CTN' => $request->KUBIKASI_CTN,
                    'KUBIKASI_PCS' => $volume_pcs,
                    'UPDATE_AT'    => $today,
                    'UPDATE_WITH'  => $user
                ]);
                $data = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.NOROUTING, A.FC_SONO, A.FC_STOCKCODE, 
                                                                 A.FV_STOCKNAME, A.FC_REGIONDESC, A.FN_QTY, A.FN_EXTRA
                                                          FROM [d_transaksi].[dbo].[routingdetailorders] A WITH (NOLOCK)
                                                          WHERE A.FC_STOCKCODE = '$stockcode' AND A.KUBIKASI = 0 AND A.CONFIRM = 'NO'");
                if ($data) {
                    $will_insert = [];
                    $guard = 0;
                    $master = DB::connection('other')->select("SELECT KUBIKASI_PCS FROM [d_master].[dbo].[MASTER_KUBIKASI] WITH (NOLOCK)
                                                               WHERE FV_BRANDNAME = '$brand' AND FC_STOCKCODE = '$stockcode'")[0];
                    foreach ($data as $d) {
                        if ($master) {
                            $qty = (int)$d->FN_QTY + (int)$d->FN_EXTRA;
                            $kubikasi = (int)$master->KUBIKASI_PCS * (int)$qty;
                            array_push($will_insert, [
                                'FC_BRANCH'    => $d->FC_BRANCH,
                                'NOROUTING'    => $d->NOROUTING,
                                'FC_SONO'      => $d->FC_SONO,
                                'FC_STOCKCODE' => $d->FC_STOCKCODE,
                                'FV_STOCKNAME' => $d->FV_STOCKNAME,
                                'FC_REGIONDESC' => $d->FC_REGIONDESC,
                                'FN_QTY'       => $d->FN_QTY,
                                'FN_EXTRA'     => $d->FN_EXTRA,
                                'KUBIKASI'     => $kubikasi,
                                'CONFIRM'      => 'NO'
                            ]);
                            if ($guard == 100) {
                                DB::connection('sqlsrv')->table('routingdetailorders')->insert($will_insert);
                                $will_insert = [];
                                $guard = 0;
                            }
                            $guard += 1;
                        }
                        echo 'Hubungi IT ada master barang gak ada';
                    }
                    if ($guard > 0) {
                        DB::connection('sqlsrv')->table('routingdetailorders')->insert($will_insert);
                        $will_insert = [];
                        $guard = 0;
                    }
                    DB::connection('sqlsrv')->delete("DELETE FROM [d_transaksi].[dbo].[routingdetailorders] 
                                                  WHERE 
                                                     FC_STOCKCODE = '$stockcode' AND 
                                                     KUBIKASI = 0 AND 
                                                     CONFIRM = 'NO'");
                    return redirect('/check')->with('success', 'Sukses Ditambahkan');
                }
                return redirect()->back()->with('success', 'Sukses Ditambahkan, tidak ada data routing yang diupdate');
            } else {
                DB::connection('other')->update("UPDATE [d_master].[dbo].[MASTER_KUBIKASI]
                                                 SET 
                                                    VOLUME       = $volume, 
                                                    KUBIKASI_CTN = $request->KUBIKASI_CTN,
                                                    KUBIKASI_PCS = $volume_pcs,
                                                    UPDATE_AT    = '$today',
                                                    UPDATE_WITH  = '$user'
                                                 WHERE FC_STOCKCODE = '$stockcode' AND FV_BRANDNAME = '$brand'");
                $data = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.NOROUTING, A.FC_SONO, A.FC_STOCKCODE, 
                                                                 A.FV_STOCKNAME, A.FC_REGIONDESC, A.FN_QTY, A.FN_EXTRA
                                                          FROM [d_transaksi].[dbo].[routingdetailorders] A WITH (NOLOCK)
                                                          WHERE A.FC_STOCKCODE = '$stockcode' AND A.KUBIKASI = 0 AND A.CONFIRM = 'NO'");
                if ($data) {
                    $will_insert = [];
                    $guard = 0;
                    $master = DB::connection('other')->select("SELECT KUBIKASI_PCS FROM [d_master].[dbo].[MASTER_KUBIKASI] WITH (NOLOCK)
                                                               WHERE FV_BRANDNAME = '$brand' AND FC_STOCKCODE = '$stockcode'")[0];
                    foreach ($data as $d) {
                        if ($master) {
                            $qty = (int)$d->FN_QTY + (int)$d->FN_EXTRA;
                            $kubikasi = (int)$master->KUBIKASI_PCS * (int)$qty;
                            array_push($will_insert, [
                                'FC_BRANCH'    => $d->FC_BRANCH,
                                'NOROUTING'    => $d->NOROUTING,
                                'FC_SONO'      => $d->FC_SONO,
                                'FC_STOCKCODE' => $d->FC_STOCKCODE,
                                'FV_STOCKNAME' => $d->FV_STOCKNAME,
                                'FC_REGIONDESC' => $d->FC_REGIONDESC,
                                'FN_QTY'       => $d->FN_QTY,
                                'FN_EXTRA'     => $d->FN_EXTRA,
                                'KUBIKASI'     => $kubikasi,
                                'CONFIRM'      => 'NO'
                            ]);
                            if ($guard == 100) {
                                DB::connection('sqlsrv')->table('routingdetailorders')->insert($will_insert);
                                $will_insert = [];
                                $guard = 0;
                            }
                            $guard += 1;
                        }
                        echo 'Hubungi IT ada master barang gak ada';
                    }
                    if ($guard > 0) {
                        DB::connection('sqlsrv')->table('routingdetailorders')->insert($will_insert);
                        $will_insert = [];
                        $guard = 0;
                    }
                    DB::connection('sqlsrv')->delete("DELETE FROM [d_transaksi].[dbo].[routingdetailorders] 
                                                  WHERE 
                                                     FC_STOCKCODE = '$stockcode' AND 
                                                     KUBIKASI = 0 AND 
                                                     CONFIRM = 'NO'");
                    return redirect('/check')->with('success', 'Sukses Ditambahkan');
                }
                return redirect('/check')->with('success', 'Sukses Ditambahkan, tidak ada data routing yang diupdate');
            }
            return redirect()->back()->with('error', 'Data Sudah Digunakan');
        }
    }

    public function check_kubikasi()
    {
        $data = DB::connection('other')->select("SELECT FV_BRANDNAME, FC_STOCKCODE, FV_STOCKNAME, UOM FROM [d_master].[dbo].[MASTER_KUBIKASI] WITH (NOLOCK)
                                                WHERE VOLUME = 0 AND KUBIKASI_CTN = 0 AND KUBIKASI_PCS = 0 
                                                ORDER BY FV_BRANDNAME");
        if ($data) {
            return view('barang/index', [
                "data"  => $data,
                "empty" => true
            ]);
        }
    }

    public function store_empty(Request $request)
    {
        if (isset($_POST['store'])) {
            date_default_timezone_set('Asia/Jakarta');
            $today = date('d-m-Y H:i:s');
            $stockcode  = $request->FC_STOCKCODE;
            $brand      = $request->FV_BRANDNAME;
            $volume     = (int)$request->KUBIKASI_CTN / 1000000;
            $volume_pcs = (int)$request->KUBIKASI_CTN / (int)$request->UOM;
            $user       = Auth::user()->name;
            $cek_barang = DB::connection('other')->select("SELECT FC_STOCKCODE, FV_BRANDNAME FROM [d_master].[dbo].[MASTER_KUBIKASI] WITH (NOLOCK) WHERE FV_BRANDNAME = '$brand' AND FC_STOCKCODE = '$stockcode'");
            if (!$cek_barang) {
                DB::connection('other')->table('MASTER_KUBIKASI')->insert([
                    'FV_BRANDNAME' => $brand,
                    'FC_STOCKCODE' => $stockcode,
                    'FV_STOCKNAME' => $request->FV_STOCKNAME,
                    'UOM'          => $request->UOM,
                    'VOLUME'       => $volume,
                    'KUBIKASI_CTN' => $request->KUBIKASI_CTN,
                    'KUBIKASI_PCS' => $volume_pcs,
                    'UPDATE_AT'    => $today,
                    'UPDATE_WITH'  => $user
                ]);
                $data = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.NOROUTING, A.FC_SONO, A.FC_STOCKCODE, 
                                                                 A.FV_STOCKNAME, A.FC_REGIONDESC, A.FN_QTY, A.FN_EXTRA
                                                          FROM [d_transaksi].[dbo].[routingdetailorders] A WITH (NOLOCK)
                                                          WHERE A.FC_STOCKCODE = '$stockcode' AND A.KUBIKASI = 0 AND A.CONFIRM = 'NO'");
                if ($data) {
                    $will_insert = [];
                    $guard = 0;
                    $master = DB::connection('other')->select("SELECT KUBIKASI_PCS FROM [d_master].[dbo].[MASTER_KUBIKASI] WITH (NOLOCK)
                                                               WHERE FV_BRANDNAME = '$brand' AND FC_STOCKCODE = '$stockcode'")[0];
                    foreach ($data as $d) {
                        if ($master) {
                            $qty = (int)$d->FN_QTY + (int)$d->FN_EXTRA;
                            $kubikasi = (int)$master->KUBIKASI_PCS * (int)$qty;
                            array_push($will_insert, [
                                'FC_BRANCH'    => $d->FC_BRANCH,
                                'NOROUTING'    => $d->NOROUTING,
                                'FC_SONO'      => $d->FC_SONO,
                                'FC_STOCKCODE' => $d->FC_STOCKCODE,
                                'FV_STOCKNAME' => $d->FV_STOCKNAME,
                                'FC_REGIONDESC' => $d->FC_REGIONDESC,
                                'FN_QTY'       => $d->FN_QTY,
                                'FN_EXTRA'     => $d->FN_EXTRA,
                                'KUBIKASI'     => $kubikasi,
                                'CONFIRM'      => 'NO'
                            ]);
                            if ($guard == 100) {
                                DB::connection('sqlsrv')->table('routingdetailorders')->insert($will_insert);
                                $will_insert = [];
                                $guard = 0;
                            }
                            $guard += 1;
                        }
                        echo 'Hubungi IT ada master barang gak ada';
                    }
                    if ($guard > 0) {
                        DB::connection('sqlsrv')->table('routingdetailorders')->insert($will_insert);
                        $will_insert = [];
                        $guard = 0;
                    }
                    DB::connection('sqlsrv')->delete("DELETE FROM [d_transaksi].[dbo].[routingdetailorders] 
                                                  WHERE 
                                                     FC_STOCKCODE = '$stockcode' AND 
                                                     KUBIKASI = 0 AND 
                                                     CONFIRM = 'NO'");
                    return redirect('/check')->with('success', 'Sukses Ditambahkan');
                }
                return redirect()->back()->with('success', 'Sukses Ditambahkan, tidak ada data routing yang diupdate');
            } else {
                DB::connection('other')->update("UPDATE [d_master].[dbo].[MASTER_KUBIKASI]
                                                 SET 
                                                    VOLUME       = $volume, 
                                                    KUBIKASI_CTN = $request->KUBIKASI_CTN,
                                                    KUBIKASI_PCS = $volume_pcs,
                                                    UPDATE_AT    = '$today',
                                                    UPDATE_WITH  = '$user'
                                                 WHERE FC_STOCKCODE = '$stockcode' AND FV_BRANDNAME = '$brand'");
                $data = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.NOROUTING, A.FC_SONO, A.FC_STOCKCODE, 
                                                                 A.FV_STOCKNAME, A.FC_REGIONDESC, A.FN_QTY, A.FN_EXTRA
                                                          FROM [d_transaksi].[dbo].[routingdetailorders] A WITH (NOLOCK)
                                                          WHERE A.FC_STOCKCODE = '$stockcode' AND A.KUBIKASI = 0 AND A.CONFIRM = 'NO'");
                if ($data) {
                    $will_insert = [];
                    $guard = 0;
                    $master = DB::connection('other')->select("SELECT KUBIKASI_PCS FROM [d_master].[dbo].[MASTER_KUBIKASI] WITH (NOLOCK)
                                                               WHERE FV_BRANDNAME = '$brand' AND FC_STOCKCODE = '$stockcode'")[0];
                    foreach ($data as $d) {
                        if ($master) {
                            $qty = (int)$d->FN_QTY + (int)$d->FN_EXTRA;
                            $kubikasi = (int)$master->KUBIKASI_PCS * (int)$qty;
                            array_push($will_insert, [
                                'FC_BRANCH'    => $d->FC_BRANCH,
                                'NOROUTING'    => $d->NOROUTING,
                                'FC_SONO'      => $d->FC_SONO,
                                'FC_STOCKCODE' => $d->FC_STOCKCODE,
                                'FV_STOCKNAME' => $d->FV_STOCKNAME,
                                'FC_REGIONDESC' => $d->FC_REGIONDESC,
                                'FN_QTY'       => $d->FN_QTY,
                                'FN_EXTRA'     => $d->FN_EXTRA,
                                'KUBIKASI'     => $kubikasi,
                                'CONFIRM'      => 'NO'
                            ]);
                            if ($guard == 100) {
                                DB::connection('sqlsrv')->table('routingdetailorders')->insert($will_insert);
                                $will_insert = [];
                                $guard = 0;
                            }
                            $guard += 1;
                        }
                        echo 'Hubungi IT ada master barang gak ada';
                    }
                    if ($guard > 0) {
                        DB::connection('sqlsrv')->table('routingdetailorders')->insert($will_insert);
                        $will_insert = [];
                        $guard = 0;
                    }
                    DB::connection('sqlsrv')->delete("DELETE FROM [d_transaksi].[dbo].[routingdetailorders] 
                                                  WHERE 
                                                     FC_STOCKCODE = '$stockcode' AND 
                                                     KUBIKASI = 0 AND 
                                                     CONFIRM = 'NO'");
                    return redirect('/check-kubikasi-empty')->with('success', 'Sukses Ditambahkan');
                }
                return redirect('/check-kubikasi-empty')->with('success', 'Sukses Ditambahkan, tidak ada data routing yang diupdate');
            }
            return redirect()->back()->with('error', 'Data Sudah Digunakan');
        }
    }
}
