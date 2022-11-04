<?php

namespace App\Http\Controllers\WH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiFormatter;
use App\Helpers\phpqrcode\qrlib;
use App\Models\SetModel;
use DateTime;
use QRcode;

class WHController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $today = Carbon::now()->toDateString();
        $format = explode('-', $today);
        $result  = implode('', $format);
        $sto = DB::connection('other')->select("SELECT * FROM [d_master].[dbo].[t_dc_details] A WITH (NOLOCK)
                                               LEFT JOIN [d_master].[dbo].[t_dc] B WITH (NOLOCK)
                                               ON A.FC_BRANCH = B.FC_BRANCH
                                               WHERE A.FC_BRANCH = '$user->fc_branch'");
        $emp = DB::connection('other')->select("SELECT * FROM [d_master].[dbo].[karyawan] WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch'");
        $vehicle = DB::connection('other')->select("SELECT *
                                                 FROM [d_master].[dbo].[kendaraan] WITH (NOLOCK)
                                                 WHERE FC_BRANCH = '$user->fc_branch'");
        if ($sto) {
            return view('wh/routing/index', [
                "sto"      => $sto,
                "emp"      => $emp,
                "vehicle"  => $vehicle
            ]);
        }

        return view('wh/routing/index_cabang', [
            "emp"      => $emp,
            "vehicle"  => $vehicle
        ]);
    }

    public function store(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        if (isset($_POST['create_stof'])) {
            $today = date('d-m-Y H:i:s');
            $branch = Auth::user()->fc_branch;
            $prefix = substr($branch, 3);
            $NoRouting = ApiFormatter::IDGenerator(new SetModel, "NOROUTING", 5, "$prefix", $branch);
            $q = new SetModel;
            $q->NOROUTING = $NoRouting;
            $data = [
                'NOROUTING' => $NoRouting,
                'FC_BRANCH' => $branch,
                'NAME'      => Auth::user()->name,
                'DATE'      => $today,
                'CODE_STOF' => $request->CODE_STOF,
                'NOPOL'     => $request->NOPOL,
                'FC_NIK'    => $request->FC_NIK,
                'CONFIRM'   => "NO"
            ];
            DB::connection('sqlsrv')->table('routers')->insert($data);
            return redirect('/routing-list');
        }

        if (isset($_POST['create_cabang'])) {
            $today = date('d-m-Y H:i:s');
            $branch = Auth::user()->fc_branch;
            $prefix = substr($branch, 3);
            $NoRouting = ApiFormatter::IDGenerator(new SetModel, "NOROUTING", 5, "$prefix", $branch);
            $q = new SetModel;
            $q->NOROUTING = $NoRouting;
            $data = [
                'NOROUTING' => $NoRouting,
                'FC_BRANCH' => $branch,
                'NAME'      => Auth::user()->name,
                'DATE'      => $today,
                'CODE_STOF' => $branch,
                'NOPOL'     => $request->NOPOL,
                'FC_NIK'    => $request->FC_NIK,
                'CONFIRM'   => "NO"
            ];
            DB::connection('sqlsrv')->table('routers')->insert($data);
            return redirect('/routing-list');
        }
    }

    public function list()
    {
        $user = Auth::user();
        $data = DB::connection('sqlsrv')->select("SELECT A.NOROUTING, A.NAME, A.FC_BRANCH, A.DATE, A.CODE_STOF, A.NOPOL, A.CONFIRM, A.FC_NIK, B.FC_NAME, SUM(C.KUBIKASI) AS KUBIK, D.KUBIKASI
                                                 FROM [d_transaksi].[dbo].[routers] A WITH (NOLOCK)
                                                 LEFT JOIN [d_master].[dbo].[karyawan] B WITH (NOLOCK)
                                                 ON A.FC_NIK = B.FC_NIK
                                                 LEFT JOIN [d_transaksi].[dbo].[routingcustomer] C WITH (NOLOCK)
                                                 ON A.NOROUTING = C.NOROUTING
                                                 LEFT JOIN [d_master].[dbo].[kendaraan] D WITH (NOLOCK)
                                                 ON A.NOPOL = D.NOPOL
                                                 WHERE A.FC_BRANCH = '$user->fc_branch'
                                                 GROUP BY A.NOROUTING, A.NAME, A.FC_BRANCH, A.DATE, A.CODE_STOF, A.NOPOL, A.CONFIRM, A.FC_NIK, B.FC_NAME, D.KUBIKASI
                                                 ORDER BY A.NOROUTING ASC");

        return view('wh/routing/list', [
            "data" => $data
        ]);
    }

    public function pilih($routing)
    {
        $user = Auth::user();
        $cek_routing = DB::connection('sqlsrv')->select("SELECT NOROUTING, CONFIRM FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$routing' AND FC_BRANCH = '$user->fc_branch'");
        if (!$cek_routing) {
            return redirect('/routing-list');
        }
        if ($cek_routing[0]->CONFIRM == 'YES') {
            return redirect('/routing-list');
        }
        $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$routing'");
        if ($branch) {
            if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                return redirect('/routing-list');
            }
            $code_stof = $branch[0]->CODE_STOF;
            $dt = Carbon::now();
            $month = date('Ym', strtotime($dt));
            $today = Carbon::now()->toDateString();
            $format = explode('-', $today);
            $result  = implode('', $format);
            $cek_tanggal = DB::connection('sqlsrv')->select("SELECT * FROM [d_transaksi].[dbo].[t_settingdates] WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch'");
            if (!$cek_tanggal) {
                $load = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.FC_SONO, A.FD_SODATE,
                                                      A.SHIPNAME, A.SHIPADDRESS, A.KODE_RAYON,
                                                      A.FC_CUSTCODE, A.FV_CUSTNAME, A.FC_REGIONDESC AS KELURAHAN, SUM(A.KUBIKASI) AS KUBIK
                                                      FROM [d_transaksi].[dbo].[temporarydetailorders] A WITH (NOLOCK) 
                                                      LEFT JOIN [d_transaksi].[dbo].[routingcustomer] B WITH (NOLOCK)
                                                      ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_SONO = B.FC_SONO
                                                      WHERE A.FC_BRANCH = '$code_stof' 
                                                      AND B.FC_SONO IS NULL
                                                      GROUP BY A.FC_BRANCH, A.FC_SONO, A.FD_SODATE, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FC_REGIONDESC, A.SHIPNAME, A.SHIPADDRESS, A.KODE_RAYON
                                                      ORDER BY FC_SONO");
                if (!$load) {
                    $data = DB::connection('other4')->select("SELECT 
        	                                        A.FC_BRANCH,
                                                    A.FC_CUSTCODE,
                                                    A.FV_CUSTADD1,
                                                    I.FD_SODATE,
                                                    A.FV_CUSTNAME,
                                                    I.FC_SONO,
                                                    D.fc_regiondesc AS KELURAHAN		   
                                                  FROM 
                                                    [d_master].[dbo].[t_customer] A WITH (NOLOCK)
                                                  LEFT JOIN 
                                                    [d_master].[dbo].[t_custparam] B WITH (NOLOCK)
        	                                      ON 
                                                    A.FC_BRANCH = B.fc_branch 
                                                        AND A.FC_CUSTCODE = B.fc_custcode
                                                  LEFT JOIN 
                                                    [d_master].[dbo].[t_cg3district] C WITH (NOLOCK)
        	                                      ON 
                                                    B.fc_branch = C.fc_branch
                                                        AND B.fc_provcode = C.fc_provcode 
                                                        AND B.fc_citycode = C.fc_citycode
                                                        AND B.fc_districtcode = C.fc_districtcode
                                                  LEFT JOIN 
                                                    [d_master].[dbo].[t_cg4regiON] D WITH (NOLOCK)
        	                                      ON 
                                                    B.fc_branch = D.fc_branch 
                                                        AND B.fc_provcode = D.fc_provcode 
                                                        AND B.fc_citycode = D.fc_citycode 
                                                        AND B.fc_districtcode = D.fc_districtcode 
                                                        AND B.fc_postcode = D.fc_postcode
                                                  LEFT JOIN 
                                                    [d_master].[dbo].[t_ctipe] E WITH (NOLOCK)
        	                                      ON 
                                                    A.FC_CUSTTYPE = E.FC_TIPE
                                                  LEFT JOIN 
                                                    [d_master].[dbo].[t_cjenis] F WITH (NOLOCK)
        	                                      ON 
                                                    A.FC_CUSTTYPE = F.FC_TIPE 
                                                        AND A.FC_CUSTJENIS = F.FC_JENIS
                                                  LEFT JOIN 
                                                    [d_master].[dbo].[t_carea] G WITH (NOLOCK)
        	                                      ON 
                                                    A.FC_CUSTAREA = G.FC_AREA
                                                  LEFT JOIN 
                                                    [d_master].[dbo].[t_custtax] H WITH (NOLOCK)
        	                                      ON A.FC_BRANCH = H.fc_branch 
                                                        AND A.FC_CUSTCODE = H.fc_custcode
                                                  LEFT JOIN 
                                                    [d_transaksi].[dbo].[t_somst] I WITH (NOLOCK)
                                                  ON A.FC_CUSTCODE = I.FC_CUSTCODE 
                                                        AND A.FC_BRANCH = I.FC_BRANCH
                                                Left Join [d_transaksi].[dbo].[t_tsdo_report] J With (Nolock)
        										  ON I.FC_SONO = J.FC_SONO AND I.FC_BRANCH = J.FC_BRANCH 
                                                  AND J.FC_STATUSDELIVERY IN('1','2','6','3')
         										    AND J.FN_NOMORINV = 1
                                                  WHERE 
                                                    A.FC_BRANCH = '$code_stof' 
                                                    AND I.FC_SOTYPE IN ('A', 'B')
                                                    AND A.FC_CUSTTYPE = 'PR'
                                                    AND I.FC_STATUS IN ('I','X')
                                                    AND 
                                                    RIGHT('0'+CAST(DATEPART(YEAR,I.FD_SODATE)AS VARCHAR(4)),4)
                                                    +RIGHT('0'+CAST(DATEPART(MONTH,I.FD_SODATE)AS VARCHAR(2)),2)
                                                    +RIGHT('0'+CAST(DATEPART(DAY,I.FD_SODATE)AS VARCHAR(2)),2)
                                                        ='$result'
                                                    AND J.FC_SONO IS NULL
                                                    ORDER BY FC_SONO");
                    $total_toko_routing = DB::connection('sqlsrv')->select("SELECT COUNT(NOROUTING) AS toko FROM [d_transaksi].[dbo].[routingcustomer] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
                    $kubikasi_routing = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[routingdetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
                    $info_toko = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
                    $toko = DB::connection('sqlsrv')->select("SELECT DISTINCT FC_SONO FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
                    return view('wh/routing/group', [
                        "data" => $data,
                        "isGroup" => false,
                        "routing" => $routing,
                        "kubikasi_load" => $info_toko[0],
                        "toko_load" => $toko,
                        "toko_routing" => $total_toko_routing[0],
                        "kubikasi"     => $kubikasi_routing[0],
                    ]);
                } else {
                    $total_toko_routing = DB::connection('sqlsrv')->select("SELECT COUNT(NOROUTING) AS toko FROM [d_transaksi].[dbo].[routingcustomer] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
                    $kubikasi_routing = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[routingdetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
                    $info_toko = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
                    $toko = DB::connection('sqlsrv')->select("SELECT DISTINCT FC_SONO FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
                    return view('wh/routing/after_kubikasi', [
                        "data" => $load,
                        "kubikasi_load" => $info_toko[0],
                        "toko_load" => $toko,
                        "toko_routing" => $total_toko_routing[0],
                        "kubikasi"     => $kubikasi_routing[0],
                        "isGroup" => false,
                        "routing" => $routing,
                    ]);
                }
            } else {
                $date1 = $cek_tanggal[0]->SETTING_DATE1;
                $date2 = $cek_tanggal[0]->SETTING_DATE2;
                $toko = DB::connection('sqlsrv')->select("SELECT DISTINCT FC_SONO FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
                $kubikasi_routing = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[routingdetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
                $total_toko_routing = DB::connection('sqlsrv')->select("SELECT COUNT(NOROUTING) AS toko FROM [d_transaksi].[dbo].[routingcustomer] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
                $info_toko = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
                $data = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.FC_SONO, A.FD_SODATE, A.FC_CUSTCODE,
                                                     A.SHIPNAME, A.SHIPADDRESS,
                                                     A.FV_CUSTNAME, A.FC_REGIONDESC AS KELURAHAN, 
                                                     SUM(A.KUBIKASI) AS KUBIK 
                                                     FROM [d_transaksi].[dbo].[temporarydetailorders] A WITH (NOLOCK)
                                                     LEFT JOIN [d_transaksi].[dbo].[routingcustomer] B WITH (NOLOCK)
                                                     ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_SONO = B.FC_SONO
                                                     WHERE
                                                     A.FC_BRANCH = '$code_stof'
                                                     AND A.FD_SODATE BETWEEN '$date1' AND '$date2'
                                                     AND B.FC_SONO IS NULL
                                                     GROUP BY A.FC_BRANCH, A.FC_SONO, A.FD_SODATE, A.FC_CUSTCODE, A.SHIPNAME, A.SHIPADDRESS,
                                                     A.FV_CUSTNAME, A.FC_REGIONDESC");
                return view('wh/routing/after_kubikasi', [
                    "data" => $data,
                    "kubikasi_load" => $info_toko[0],
                    "toko_load"     => $toko,
                    "toko_routing" => $total_toko_routing[0],
                    "kubikasi"     => $kubikasi_routing[0],
                    "isGroup" => false,
                    "routing" => $routing,
                ]);
            }
        }
        return redirect('/pilih/' . $routing);
    }

    public function rangetanggal(Request $request)
    {
        // setting untuk menghitung hari
        $start = new DateTime($request->tanggal1);
        $end   = new DateTime($request->tanggal2);
        $range = $end->diff($start);
        $routing = $request->NOROUTING;
        $tgl1  = $request->tanggal1; // menyimpan request tgl1 ke variabel
        $tgl2  = $request->tanggal2; // menyimpan request tgl2 ke variabel
        $user  = Auth::user();
        // setting mengubah date menjadi format string
        $exp   = explode('-', $tgl1);
        $rTgl1 = implode('', $exp);
        $exp2  = explode('-', $tgl2);
        $rTgl2 = implode('', $exp2);
        $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$routing'");
        if ($branch) {
            $code_stof = $branch[0]->CODE_STOF;
            if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                return redirect('/routing-list');
            }
            // proses mengkosongkan setting tanggal berdasarkan login cabang
            DB::connection('sqlsrv')->table('t_settingdates')->where('FC_BRANCH', $code_stof)->delete();
            DB::connection('sqlsrv')->table('temporarydetailorders')->where('FC_BRANCH', $code_stof)->delete();
            // proses pengecakan, apakah range hari lebih besar tanggal 2 terhadap tanggal 1
            if (strtotime($tgl2) > strtotime($tgl1)) {
                if ($range->d > 7) {
                    return redirect()->back()->with('danger', 'Setting Tanggal Melebihi 7 Hari');
                } else {
                    DB::connection('sqlsrv')->table('t_settingdates')->insert([
                        'FC_BRANCH'    => "$code_stof",
                        'SETTING_DATE1' => $rTgl1,
                        'SETTING_DATE2' => $rTgl2
                    ]);
                    $mainserver = DB::connection('other4')->select("SELECT 
                                                                A.FC_BRANCH,
                                                                A.FC_SONO,
                                                                D.FD_SODATE,
                                                                D.FC_CUSTCODE,
                                                                A.FC_STOCKCODE,
                                                                A.FN_QTY,
                                                                A.FN_EXTRA,
                                                                B.FV_STOCKNAME,
                                                                C.FI_UOM,
                                                                E.FV_BRANDNAME, 
                                                                F.FV_CUSTNAME, 
                                                                F.FC_CUSTTYPE,
                                                                F.FC_CUSTJENIS,
                                                                H.fc_regiondesc,
                                                                ISNULL(J.FV_NAME,'') AS SHIPNAME,
                                                                ISNULL (J.FV_SHIPADD1,'') AS SHIPADDRESS 
                                                            FROM 
                                                                [d_transaksi].[dbo].[t_sodtl] A WITH (NOLOCK)
                                                                LEFT JOIN  [d_transaksi].[dbo].[t_somst] D WITH (NOLOCK)
                                                                    ON A.FC_SONO = D.FC_SONO AND A.FC_BRANCH = D.FC_BRANCH
                                                                    LEFT JOIN [d_master].[dbo].[t_custship] J WITH (NOLOCK)
                                                                    ON
                                                                    D.FC_BRANCH = J.FC_BRANCH AND D.FC_CUSTCODE = J.FC_CUSTCODE 
                                                                    AND D.FC_SHIPTO = J.FC_SHIPCODE
                                                                LEFT JOIN [d_master].[dbo].[t_stock] B WITH (NOLOCK)
                                                                    ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_STOCKCODE = B.FC_STOCKCODE
                                                                LEFT JOIN [d_master].[dbo].[t_mpack] C WITH (NOLOCK)
                                                                    ON B.FC_BRAND = C.FC_BRAND AND B.FC_GROUP = C.FC_GROUP AND B.FC_SUBGRP = C.FC_SUBGRP
                                                                    AND B.FC_TYPE = C.FC_TYPE AND B.FC_PACK = C.FC_PACK
                                                                LEFT JOIN [d_master].[dbo].[t_mbrand] E WITH (NOLOCK)
                                                                    ON B.FC_BRAND = E.FC_BRAND
                                                                LEFT JOIN [d_master].[dbo].[t_customer] F WITH (NOLOCK)
                                                                    ON D.FC_CUSTCODE = F.FC_CUSTCODE
                                                                    AND D.FC_BRANCH = F.FC_BRANCH
                                                                LEFT JOIN [d_master].[dbo].[t_custparam] G WITH (NOLOCK)
                                                                    ON D.FC_BRANCH = G.fc_branch 
                                                                    AND D.FC_CUSTCODE = G.fc_custcode
                                                                LEFT JOIN [d_master].[dbo].[t_cg4regiON] H WITH (NOLOCK)
                                                                    ON G.fc_branch = H.fc_branch 
                                                                    AND G.fc_provcode = H.fc_provcode 
                                                                    AND G.fc_citycode = H.fc_citycode 
                                                                    AND G.fc_districtcode = H.fc_districtcode 
                                                                    AND G.fc_postcode = H.fc_postcode
                                                                LEFT JOIN [d_transaksi].[dbo].[t_tsdo_report] I With (Nolock)
                                                                ON D.FC_SONO = I.FC_SONO And D.FC_BRANCH = I.FC_BRANCH 
                                                                AND I.FC_STATUSDELIVERY IN('1','2','6','3')
                                                                    AND I.FN_NOMORINV = 1 
                                                                WHERE A.FC_BRANCH = '$code_stof'
                                                                    AND D.FC_STATUS IN ('I', 'X')
                                                                    AND D.FC_SOTYPE IN ('A','B')                                                                
                                                                    AND F.FC_CUSTTYPE = 'PR'
                                                                    AND D.FD_SODATE BETWEEN '$tgl1' AND '$tgl2'
                                                                    And I.FC_SONO IS NULL
                                                                    ORDER BY D.FC_SONO");
                    if ($mainserver) {
                        $ready = [];
                        $guard = 0;
                        foreach ($mainserver as $main) {
                            $barang = DB::connection('sqlsrv')->select("SELECT KUBIKASI_PCS FROM [d_master].[dbo].[MASTER_KUBIKASI] WITH (NOLOCK) WHERE FC_STOCKCODE = '$main->FC_STOCKCODE'");
                            if ($barang) {
                                $qty      = (int)$main->FN_QTY + (int)$main->FN_EXTRA;
                                $kubikasi = (int)$barang[0]->KUBIKASI_PCS * (int)$qty;
                                array_push($ready, [
                                    'FC_BRANCH'     => $main->FC_BRANCH,
                                    'FV_BRANDNAME'  => $main->FV_BRANDNAME,
                                    'FC_SONO'       => $main->FC_SONO,
                                    'FD_SODATE'     => $main->FD_SODATE,
                                    'FV_CUSTNAME'   => $main->FV_CUSTNAME,
                                    'FC_CUSTCODE'   => $main->FC_CUSTCODE,
                                    'FC_STOCKCODE'  => $main->FC_STOCKCODE,
                                    'FV_STOCKNAME'  => $main->FV_STOCKNAME,
                                    'FC_REGIONDESC' => $main->fc_regiondesc,
                                    'SHIPNAME'      => $main->SHIPNAME,
                                    'SHIPADDRESS'   => $main->SHIPADDRESS,
                                    'FC_CUSTTYPE'   => $main->FC_CUSTTYPE,
                                    'FC_CUSTJENIS'  => $main->FC_CUSTJENIS,
                                    'UoM'           => $main->FI_UOM,
                                    'FN_QTY'        => $main->FN_QTY,
                                    'FN_EXTRA'      => $main->FN_EXTRA,
                                    'KUBIKASI'      => $kubikasi,
                                ]);
                                if ($guard == 100) {
                                    DB::connection('sqlsrv')->table('temporarydetailorders')->insert($ready);
                                    $guard = 0;
                                    $ready = [];
                                }
                                $guard += 1;
                            } else {
                                array_push($ready, [
                                    'FC_BRANCH'     => $main->FC_BRANCH,
                                    'FV_BRANDNAME'  => $main->FV_BRANDNAME,
                                    'FC_SONO'       => $main->FC_SONO,
                                    'FD_SODATE'     => $main->FD_SODATE,
                                    'FV_CUSTNAME'   => $main->FV_CUSTNAME,
                                    'FC_CUSTCODE'   => $main->FC_CUSTCODE,
                                    'FC_STOCKCODE'  => $main->FC_STOCKCODE,
                                    'FV_STOCKNAME'  => $main->FV_STOCKNAME,
                                    'FC_REGIONDESC' => $main->fc_regiondesc,
                                    'SHIPNAME'      => $main->SHIPNAME,
                                    'SHIPADDRESS'   => $main->SHIPADDRESS,
                                    'FC_CUSTTYPE'   => $main->FC_CUSTTYPE,
                                    'FC_CUSTJENIS'  => $main->FC_CUSTJENIS,
                                    'UoM'           => $main->FI_UOM,
                                    'FN_QTY'        => $main->FN_QTY,
                                    'FN_EXTRA'      => $main->FN_EXTRA,
                                    'KUBIKASI'      => 0,
                                ]);
                                if ($guard == 100) {
                                    DB::connection('sqlsrv')->table('temporarydetailorders')->insert($ready);
                                    $guard = 0;
                                    $ready = [];
                                }
                                $guard += 1;
                            }
                        };
                        if ($guard > 0) {
                            DB::connection('sqlsrv')->table('temporarydetailorders')->insert($ready);
                        }
                        return redirect('/pilih/' . $routing);
                    }
                    return redirect('/pilih/' . $routing);
                }
                return redirect('/pilih/' . $routing);
            } else {
                // kondisi ketika tanggal 2 diinput lebih kecil dari tanggal 1
                if ($range->d > 7) {
                    return redirect()->back()->with('danger', 'Setting Tanggal Melebihi 7 Hari');
                } else {
                    DB::connection('sqlsrv')->table('t_settingdates')->insert([
                        'FC_BRANCH'     => $code_stof,
                        'SETTING_DATE1' => $rTgl2,
                        'SETTING_DATE2' => $rTgl1
                    ]);
                    $mainserver = DB::connection('other4')->select("SELECT 
                                                                A.FC_BRANCH,
                                                                A.FC_SONO,
                                                                D.FD_SODATE,
                                                                D.FC_CUSTCODE,
                                                                A.FC_STOCKCODE,
                                                                A.FN_QTY,
                                                                A.FN_EXTRA,
                                                                B.FV_STOCKNAME,
                                                                C.FI_UOM,
                                                                E.FV_BRANDNAME, 
                                                                F.FV_CUSTNAME, 
                                                                F.FC_CUSTTYPE,
                                                                F.FC_CUSTJENIS,
                                                                H.fc_regiondesc,
                                                                ISNULL(J.FV_NAME,'') AS SHIPNAME,
                                                                ISNULL (J.FV_SHIPADD1,'') AS SHIPADDRESS 
                                                            FROM 
                                                                [d_transaksi].[dbo].[t_sodtl] A WITH (NOLOCK)
                                                                LEFT JOIN  [d_transaksi].[dbo].[t_somst] D WITH (NOLOCK)
                                                                    ON A.FC_SONO = D.FC_SONO AND A.FC_BRANCH = D.FC_BRANCH
                                                                    LEFT JOIN [d_master].[dbo].[t_custship] J WITH (NOLOCK)
                                                                    ON
                                                                    D.FC_BRANCH = J.FC_BRANCH AND D.FC_CUSTCODE = J.FC_CUSTCODE 
                                                                    AND D.FC_SHIPTO = J.FC_SHIPCODE
                                                                LEFT JOIN [d_master].[dbo].[t_stock] B WITH (NOLOCK)
                                                                    ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_STOCKCODE = B.FC_STOCKCODE
                                                                LEFT JOIN [d_master].[dbo].[t_mpack] C WITH (NOLOCK)
                                                                    ON B.FC_BRAND = C.FC_BRAND AND B.FC_GROUP = C.FC_GROUP AND B.FC_SUBGRP = C.FC_SUBGRP
                                                                    AND B.FC_TYPE = C.FC_TYPE AND B.FC_PACK = C.FC_PACK
                                                                LEFT JOIN [d_master].[dbo].[t_mbrand] E WITH (NOLOCK)
                                                                    ON B.FC_BRAND = E.FC_BRAND
                                                                LEFT JOIN [d_master].[dbo].[t_customer] F WITH (NOLOCK)
                                                                    ON D.FC_CUSTCODE = F.FC_CUSTCODE
                                                                    AND D.FC_BRANCH = F.FC_BRANCH
                                                                LEFT JOIN [d_master].[dbo].[t_custparam] G WITH (NOLOCK)
                                                                    ON D.FC_BRANCH = G.fc_branch 
                                                                    AND D.FC_CUSTCODE = G.fc_custcode
                                                                LEFT JOIN [d_master].[dbo].[t_cg4regiON] H WITH (NOLOCK)
                                                                    ON G.fc_branch = H.fc_branch 
                                                                    AND G.fc_provcode = H.fc_provcode 
                                                                    AND G.fc_citycode = H.fc_citycode 
                                                                    AND G.fc_districtcode = H.fc_districtcode 
                                                                    AND G.fc_postcode = H.fc_postcode
                                                                LEFT JOIN [d_transaksi].[dbo].[t_tsdo_report] I With (Nolock)
                                                                ON D.FC_SONO = I.FC_SONO And D.FC_BRANCH = I.FC_BRANCH 
                                                                AND I.FC_STATUSDELIVERY IN('1','2','6','3')
                                                                    AND I.FN_NOMORINV = 1 
                                                                WHERE A.FC_BRANCH = '$code_stof'
                                                                    AND D.FC_STATUS IN ('I', 'X')
                                                                    AND D.FC_SOTYPE IN ('A','B')                                                                
                                                                    AND F.FC_CUSTTYPE = 'PR'
                                                                    AND D.FD_SODATE BETWEEN '$tgl2' AND '$tgl1'
                                                                    And I.FC_SONO IS NULL
                                                                    ORDER BY D.FC_SONO");
                    if ($mainserver) {
                        $ready = [];
                        $guard = 0;
                        foreach ($mainserver as $main) {
                            $barang = DB::connection('sqlsrv')->select("SELECT KUBIKASI_PCS FROM [d_master].[dbo].[MASTER_KUBIKASI] WITH (NOLOCK) WHERE FC_STOCKCODE = '$main->FC_STOCKCODE'");
                            if ($barang) {
                                $qty      = (int)$main->FN_QTY + (int)$main->FN_EXTRA;
                                $kubikasi = (int)$barang[0]->KUBIKASI_PCS * (int)$qty;
                                array_push($ready, [
                                    'FC_BRANCH'     => $main->FC_BRANCH,
                                    'FV_BRANDNAME'  => $main->FV_BRANDNAME,
                                    'FC_SONO'       => $main->FC_SONO,
                                    'FD_SODATE'     => $main->FD_SODATE,
                                    'FV_CUSTNAME'   => $main->FV_CUSTNAME,
                                    'FC_CUSTCODE'   => $main->FC_CUSTCODE,
                                    'FC_STOCKCODE'  => $main->FC_STOCKCODE,
                                    'FV_STOCKNAME'  => $main->FV_STOCKNAME,
                                    'FC_REGIONDESC' => $main->fc_regiondesc,
                                    'SHIPNAME'      => $main->SHIPNAME,
                                    'SHIPADDRESS'   => $main->SHIPADDRESS,
                                    'FC_CUSTTYPE'   => $main->FC_CUSTTYPE,
                                    'FC_CUSTJENIS'  => $main->FC_CUSTJENIS,
                                    'UoM'           => $main->FI_UOM,
                                    'FN_QTY'        => $main->FN_QTY,
                                    'FN_EXTRA'      => $main->FN_EXTRA,
                                    'KUBIKASI'      => $kubikasi,
                                ]);
                                if ($guard == 100) {
                                    DB::connection('sqlsrv')->table('temporarydetailorders')->insert($ready);
                                    $guard = 0;
                                    $ready = [];
                                }
                                $guard += 1;
                            } else {
                                array_push($ready, [
                                    'FC_BRANCH'     => $main->FC_BRANCH,
                                    'FV_BRANDNAME'  => $main->FV_BRANDNAME,
                                    'FC_SONO'       => $main->FC_SONO,
                                    'FD_SODATE'     => $main->FD_SODATE,
                                    'FV_CUSTNAME'   => $main->FV_CUSTNAME,
                                    'FC_CUSTCODE'   => $main->FC_CUSTCODE,
                                    'FC_STOCKCODE'  => $main->FC_STOCKCODE,
                                    'FV_STOCKNAME'  => $main->FV_STOCKNAME,
                                    'FC_REGIONDESC' => $main->fc_regiondesc,
                                    'SHIPNAME'      => $main->SHIPNAME,
                                    'SHIPADDRESS'   => $main->SHIPADDRESS,
                                    'FC_CUSTTYPE'   => $main->FC_CUSTTYPE,
                                    'FC_CUSTJENIS'  => $main->FC_CUSTJENIS,
                                    'UoM'           => $main->FI_UOM,
                                    'FN_QTY'        => $main->FN_QTY,
                                    'FN_EXTRA'      => $main->FN_EXTRA,
                                    'KUBIKASI'      => 0,
                                ]);
                                if ($guard == 100) {
                                    DB::connection('sqlsrv')->table('temporarydetailorders')->insert($ready);
                                    $guard = 0;
                                    $ready = [];
                                }
                                $guard += 1;
                            }
                        };
                        if ($guard > 0) {
                            DB::connection('sqlsrv')->table('temporarydetailorders')->insert($ready);
                        }
                        return redirect('/pilih/' . $routing);
                    }
                    return redirect('/pilih/' . $routing);
                }
                return redirect('/pilih/' . $routing);
            }
        }
        return redirect('/pilih/' . $routing);
    }

    public function checkbox(Request $request)
    {
        if (!empty($request->input('FC_SONO'))) {
            $will_insert = [];
            $guard = 0;
            $user = Auth::user();
            $routing = $request->NOROUTING;
            $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$routing'");
            if ($branch) {
                $code_stof = $branch[0]->CODE_STOF;
                if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                    return redirect('/routing-list');
                }
                foreach ($request->FC_SONO as $key => $value) {
                    $data =  DB::connection('sqlsrv')->select("SELECT FC_BRANCH, FC_SONO FROM [d_transaksi].[dbo].[routingcustomer] WITH (NOLOCK) 
                                                          WHERE FC_BRANCH = '$request->FC_BRANCH' AND FC_SONO = '$value'");
                    if (!$data) {
                        $toko = DB::connection('sqlsrv')->select("SELECT DISTINCT FC_BRANCH, FC_SONO, FD_SODATE, FC_CUSTCODE, FV_CUSTNAME, SHIPNAME, SHIPADDRESS, FC_CUSTTYPE, FC_CUSTJENIS, KODE_RAYON,
                                                              FC_REGIONDESC AS KELURAHAN, SUM(KUBIKASI) AS KUBIK
                                                              FROM [d_transaksi].[dbo].[temporarydetailorders] A WITH (NOLOCK)
                                                              WHERE FC_BRANCH = '$code_stof' AND FC_SONO = '$value'
                                                              GROUP BY FC_BRANCH, FC_SONO, FD_SODATE, FC_CUSTCODE, FV_CUSTNAME, FC_REGIONDESC, SHIPNAME, SHIPADDRESS, FC_CUSTTYPE, FC_CUSTJENIS, KODE_RAYON");
                        array_push($will_insert, [
                            'FC_BRANCH'     => $request->FC_BRANCH,
                            'NOROUTING'     => $routing,
                            'FC_SONO'       => $value,
                            'FD_SODATE'     => $toko[0]->FD_SODATE,
                            'FC_CUSTCODE'   => $toko[0]->FC_CUSTCODE,
                            'FV_CUSTNAME'   => $toko[0]->FV_CUSTNAME,
                            'FC_REGIONDESC' => $toko[0]->KELURAHAN,
                            'SHIPNAME'      => $toko[0]->SHIPNAME,
                            'SHIPADDRESS'   => $toko[0]->SHIPADDRESS,
                            'FC_CUSTTYPE'   => $toko[0]->FC_CUSTTYPE,
                            'FC_CUSTJENIS'  => $toko[0]->FC_CUSTJENIS,
                            'KUBIKASI'      => $toko[0]->KUBIK,
                            'KODE_RAYON'    => $toko[0]->KODE_RAYON
                        ]);
                        if ($guard == 100) {
                            DB::connection('sqlsrv')->table('routingcustomer')->insert($will_insert);
                            $guard = 0;
                            $will_insert = [];
                        }
                        $guard += 1;
                    }
                }
                if ($guard > 0) {
                    DB::connection('sqlsrv')->table('routingcustomer')->insert($will_insert);
                    $guard = 0;
                    $will_insert = [];
                }

                $detail = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.FC_SONO, A.FC_STOCKCODE, A.FC_REGIONDESC, A.FV_STOCKNAME, A.FN_QTY, A.FN_EXTRA, A.KUBIKASI, A.KODE_RAYON
                                                       FROM [d_transaksi].[dbo].[temporarydetailorders] A WITH (NOLOCK)
                                                       INNER JOIN [d_transaksi].[dbo].[routingcustomer] B WITH (NOLOCK)
                                                       ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_SONO = B.FC_SONO");
                if ($detail) {
                    $result = [];
                    $guard = 0;
                    foreach ($detail as $d) {
                        $cek_detail = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, FC_SONO, FC_STOCKCODE FROM [d_transaksi].[dbo].[routingdetailorders] WITH (NOLOCK)
                                                            WHERE FC_BRANCH = '$code_stof' AND FC_SONO = '$d->FC_SONO' AND FC_STOCKCODE = '$d->FC_STOCKCODE'");
                        if (!$cek_detail) {
                            array_push($result, [
                                'FC_BRANCH'    => $d->FC_BRANCH,
                                'NOROUTING'    => $routing,
                                'FC_SONO'      => $d->FC_SONO,
                                'FC_STOCKCODE' => $d->FC_STOCKCODE,
                                'FV_STOCKNAME' => $d->FV_STOCKNAME,
                                'FC_REGIONDESC' => $d->FC_REGIONDESC,
                                'FN_QTY'       => $d->FN_QTY,
                                'FN_EXTRA'     => $d->FN_EXTRA,
                                'KUBIKASI'     => $d->KUBIKASI,
                                'CONFIRM'      => 'NO',
                                'KODE_RAYON'   => $d->KODE_RAYON
                            ]);
                        }
                        if ($guard == 100) {
                            DB::connection('sqlsrv')->table('routingdetailorders')->insert($result);
                            $result = [];
                            $guard  = 0;
                        }
                        $guard += 1;
                    }
                    if ($guard > 0) {
                        DB::connection('sqlsrv')->table('routingdetailorders')->insert($result);
                        $result = [];
                        $guard  = 0;
                    }
                } else {
                    return redirect('/pilih/' . $routing);
                }
                return redirect('/pilih/' . $routing);
            }
            return redirect('/pilih/' . $routing);
        }
        return redirect('/pilih/' . $request->NOROUTING);
    }

    // dalam proses development belum semua nya benar
    public function detail_barang($NOROUTING)
    {
        $user = Auth::user();
        $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$NOROUTING'");
        if ($branch) {
            $code_stof = $branch[0]->CODE_STOF;
            if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                return redirect('/routing-list');
            }
            // ketika lihat detail barang dengan paramater field NOROUTING.
            $cek_routing = DB::connection('sqlsrv')->select("SELECT NOROUTING, CONFIRM FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$NOROUTING' AND CODE_STOF = '$code_stof'");

            // jika data kosong, maka redirect ke halaman /routing-list
            if (!$cek_routing) {
                return redirect('/routing-list');
            }

            // jika user, sengaja mengubah norouting di url, ketika get data tidak ada
            if (!$NOROUTING) {
                return redirect('/routing-list');
            }
            $data = DB::connection('sqlsrv')->select("SELECT A.FC_STOCKCODE, A.FV_STOCKNAME, 
                                                     SUM(A.FN_QTY) AS QTY, SUM(A.FN_EXTRA) AS EXTRA, SUM(A.KUBIKASI) AS KUBIK
                                                     FROM [d_transaksi].[dbo].[routingdetailorders] A WITH (NOLOCK)
                                                     WHERE A.FC_BRANCH = '$code_stof' AND A.NOROUTING = '$NOROUTING'
                                                     GROUP BY A.FC_STOCKCODE, A.FV_STOCKNAME");

            return view('wh/routing/detail_barang_routing', [
                "barang" => $data,
                "routing" => $NOROUTING,
                "confirm" => $cek_routing[0]
            ]);
        }
        return redirect('/pilih/' . $NOROUTING);
    }

    public function detail_toko_routing($NOROUTING)
    {
        $user = Auth::user();
        $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$NOROUTING'");
        if ($branch) {
            $code_stof = $branch[0]->CODE_STOF;
            if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                return redirect('/routing-list');
            }
            $cek_routing = DB::connection('sqlsrv')->select("SELECT NOROUTING, CONFIRM FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$NOROUTING' AND CODE_STOF = '$code_stof'");
            $data = DB::connection('sqlsrv')->select("SELECT A.*, B.lat, B.long FROM [d_transaksi].[dbo].[routingcustomer] A WITH (NOLOCK)
                                                      LEFT JOIN [CSAREPORT].[dbo].[locations] B WITH (NOLOCK)
                                                      ON A.FC_BRANCH COLLATE DATABASE_DEFAULT = B.branch_id COLLATE DATABASE_DEFAULT AND A.FC_CUSTCODE COLLATE DATABASE_DEFAULT = B.customer_id COLLATE DATABASE_DEFAULT
                                                     WHERE NOROUTING = '$NOROUTING'");
            if (!$data) {
                return redirect('/pilih/' . $NOROUTING);
            }
            return view('wh/routing/detail_toko_routing', [
                "kubikasi" => $data,
                "pilih"    => true,
                "routing"  => $NOROUTING,
                "confirm"  => $cek_routing[0]
            ]);
        }
        return redirect('/pilih/' . $NOROUTING);
    }

    public function reset()
    {
        DB::connection('sqlsrv')->table('routingcustomer')->delete();
        DB::connection('sqlsrv')->table('temporaryorders')->delete();
        DB::connection('sqlsrv')->table('temporarydetailorders')->delete();
        DB::connection('sqlsrv')->table('routingdetailorders')->delete();
        DB::connection('sqlsrv')->table('t_settingdates')->delete();
        DB::connection('sqlsrv')->table('routers')->delete();
        return redirect('/routing-list');
    }

    public function load_monthly(Request $request)
    {
        $routing = $request->norouting;
        $user = Auth::user();
        $dt = Carbon::now();
        $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$routing'");
        if ($branch) {
            if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                return redirect('/routing-list');
            }
            $code_stof = $branch[0]->CODE_STOF;
            DB::connection('sqlsrv')->table('temporarydetailorders')->where('FC_BRANCH', $code_stof)->delete();
            DB::connection('sqlsrv')->table('t_settingdates')->where('FC_BRANCH', $code_stof)->delete();
            $month = date('Ym', strtotime($dt));
            $data = DB::connection('other4')->select("SELECT 
                                                A.FC_BRANCH,
                                                A.FC_SONO,
                                                D.FD_SODATE,
                                                D.FC_CUSTCODE,
                                                A.FC_STOCKCODE,
                                                A.FN_QTY,
                                                A.FN_EXTRA,
                                                B.FV_STOCKNAME,
                                                C.FI_UOM,
                                                E.FV_BRANDNAME,
                                                F.FV_CUSTNAME,
                                                F.FC_CUSTTYPE,
                                                F.FC_CUSTJENIS,
                                                H.fc_regiondesc,
                                                ISNULL(J.FV_NAME,'') AS SHIPNAME,
                                                ISNULL (J.FV_SHIPADD1,'') AS SHIPADDRESS 
                                            FROM 
                                                [d_transaksi].[dbo].[t_sodtl] A WITH (NOLOCK)
                                                INNER JOIN  [d_transaksi].[dbo].[t_somst] D WITH (NOLOCK)
                                                    ON A.FC_SONO = D.FC_SONO AND A.FC_BRANCH = D.FC_BRANCH
                                                    LEFT JOIN [d_master].[dbo].[t_custship] J WITH (NOLOCK)
                                                    ON
                                                    D.FC_BRANCH = J.FC_BRANCH AND D.FC_CUSTCODE = J.FC_CUSTCODE 
                                                    AND D.FC_SHIPTO = J.FC_SHIPCODE
                                                LEFT JOIN [d_master].[dbo].[t_stock] B WITH (NOLOCK)
                                                    ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_STOCKCODE = B.FC_STOCKCODE
                                                LEFT JOIN [d_master].[dbo].[t_mpack] C WITH (NOLOCK)
                                                    ON B.FC_BRAND = C.FC_BRAND AND B.FC_GROUP = C.FC_GROUP AND B.FC_SUBGRP = C.FC_SUBGRP
                                                    AND B.FC_TYPE = C.FC_TYPE AND B.FC_PACK = C.FC_PACK
                                                LEFT JOIN [d_master].[dbo].[t_mbrand] E WITH (NOLOCK)
                                                    ON B.FC_BRAND = E.FC_BRAND
                                                LEFT JOIN [d_master].[dbo].[t_customer] F WITH (NOLOCK)
                                                    ON D.FC_CUSTCODE = F.FC_CUSTCODE
                                                    AND D.FC_BRANCH = F.FC_BRANCH
                                                LEFT JOIN [d_master].[dbo].[t_custparam] G WITH (NOLOCK)
                                                    ON D.FC_BRANCH = G.fc_branch 
                                                    AND D.FC_CUSTCODE = G.fc_custcode
                                                LEFT JOIN [d_master].[dbo].[t_cg4regiON] H WITH (NOLOCK)
                                                    ON G.fc_branch = H.fc_branch 
                                                    AND G.fc_provcode = H.fc_provcode 
                                                    AND G.fc_citycode = H.fc_citycode 
                                                    AND G.fc_districtcode = H.fc_districtcode 
                                                    AND G.fc_postcode = H.fc_postcode
                                                WHERE 
                                                    D.FC_STATUS IN ('I', 'X')
                                                    AND 
                                                    D.FC_SOTYPE IN ('A','B')
                                                    AND 
                                                        D.FN_ITEMDO < 1
                                                    AND
                                                        F.FC_CUSTTYPE = 'PR'
                                                    AND
                                                    A.FC_BRANCH = '$code_stof'
                                                    AND B.FC_HOLD = 'NO'
                                                    AND
                                                    RIGHT('0'+CAST(DATEPART(YEAR,D.FD_SODATE)AS VARCHAR(4)),4)
                                                    +RIGHT('0'+CAST(DATEPART(MONTH,D.FD_SODATE)AS VARCHAR(2)),2)
                                                        ='$month'");
            if ($data) {
                $ready = [];
                $guard = 0;
                foreach ($data as $main) {
                    $barang = DB::connection('sqlsrv')->select("SELECT KUBIKASI_PCS FROM [d_master].[dbo].[MASTER_KUBIKASI] WITH (NOLOCK) WHERE FC_STOCKCODE = '$main->FC_STOCKCODE'");
                    $rayon = DB::connection('CSAREPORT')->select("SELECT kode_rayon FROM [CSAREPORT].[dbo].[t_rayon_detail] WITH (NOLOCK) WHERE fc_custcode = '$main->FC_CUSTCODE'");
                    if ($barang) {
                        $qty      = (int)$main->FN_QTY + (int)$main->FN_EXTRA;
                        $kubikasi = (int)$barang[0]->KUBIKASI_PCS * (int)$qty;
                        if ($rayon) {
                            array_push($ready, [
                                'FC_BRANCH'     => $main->FC_BRANCH,
                                'FV_BRANDNAME'  => $main->FV_BRANDNAME,
                                'FC_SONO'       => $main->FC_SONO,
                                'FD_SODATE'     => $main->FD_SODATE,
                                'FV_CUSTNAME'   => $main->FV_CUSTNAME,
                                'FC_CUSTCODE'   => $main->FC_CUSTCODE,
                                'FC_STOCKCODE'  => $main->FC_STOCKCODE,
                                'FV_STOCKNAME'  => $main->FV_STOCKNAME,
                                'FC_REGIONDESC' => $main->fc_regiondesc,
                                'SHIPNAME'      => $main->SHIPNAME,
                                'SHIPADDRESS'   => $main->SHIPADDRESS,
                                'FC_CUSTTYPE'   => $main->FC_CUSTTYPE,
                                'FC_CUSTJENIS'  => $main->FC_CUSTJENIS,
                                'UoM'           => $main->FI_UOM,
                                'FN_QTY'        => $main->FN_QTY,
                                'FN_EXTRA'      => $main->FN_EXTRA,
                                'KUBIKASI'      => $kubikasi,
                                'KODE_RAYON'    => $rayon[0]->kode_rayon
                            ]);
                            if ($guard == 100) {
                                DB::connection('sqlsrv')->table('temporarydetailorders')->insert($ready);
                                $guard = 0;
                                $ready = [];
                            }
                            $guard += 1;
                        } else {
                            array_push($ready, [
                                'FC_BRANCH'     => $main->FC_BRANCH,
                                'FV_BRANDNAME'  => $main->FV_BRANDNAME,
                                'FC_SONO'       => $main->FC_SONO,
                                'FD_SODATE'     => $main->FD_SODATE,
                                'FV_CUSTNAME'   => $main->FV_CUSTNAME,
                                'FC_CUSTCODE'   => $main->FC_CUSTCODE,
                                'FC_STOCKCODE'  => $main->FC_STOCKCODE,
                                'FV_STOCKNAME'  => $main->FV_STOCKNAME,
                                'FC_REGIONDESC' => $main->fc_regiondesc,
                                'SHIPNAME'      => $main->SHIPNAME,
                                'SHIPADDRESS'   => $main->SHIPADDRESS,
                                'FC_CUSTTYPE'   => $main->FC_CUSTTYPE,
                                'FC_CUSTJENIS'  => $main->FC_CUSTJENIS,
                                'UoM'           => $main->FI_UOM,
                                'FN_QTY'        => $main->FN_QTY,
                                'FN_EXTRA'      => $main->FN_EXTRA,
                                'KUBIKASI'      => $kubikasi,
                                'KODE_RAYON'    => 'Belum Ada'
                            ]);
                            if ($guard == 100) {
                                DB::connection('sqlsrv')->table('temporarydetailorders')->insert($ready);
                                $guard = 0;
                                $ready = [];
                            }
                            $guard += 1;
                        }
                    } else {
                        if ($rayon) {
                            array_push($ready, [
                                'FC_BRANCH'     => $main->FC_BRANCH,
                                'FV_BRANDNAME'  => $main->FV_BRANDNAME,
                                'FC_SONO'       => $main->FC_SONO,
                                'FD_SODATE'     => $main->FD_SODATE,
                                'FV_CUSTNAME'   => $main->FV_CUSTNAME,
                                'FC_CUSTCODE'   => $main->FC_CUSTCODE,
                                'FC_STOCKCODE'  => $main->FC_STOCKCODE,
                                'FV_STOCKNAME'  => $main->FV_STOCKNAME,
                                'FC_REGIONDESC' => $main->fc_regiondesc,
                                'SHIPNAME'      => $main->SHIPNAME,
                                'SHIPADDRESS'   => $main->SHIPADDRESS,
                                'FC_CUSTTYPE'   => $main->FC_CUSTTYPE,
                                'FC_CUSTJENIS'  => $main->FC_CUSTJENIS,
                                'UoM'           => $main->FI_UOM,
                                'FN_QTY'        => $main->FN_QTY,
                                'FN_EXTRA'      => $main->FN_EXTRA,
                                'KUBIKASI'      => 0,
                                'KODE_RAYON'    => $rayon[0]->kode_rayon
                            ]);
                            if ($guard == 100) {
                                DB::connection('sqlsrv')->table('temporarydetailorders')->insert($ready);
                                $guard = 0;
                                $ready = [];
                            }
                            $guard += 1;
                        }
                        array_push($ready, [
                            'FC_BRANCH'     => $main->FC_BRANCH,
                            'FV_BRANDNAME'  => $main->FV_BRANDNAME,
                            'FC_SONO'       => $main->FC_SONO,
                            'FD_SODATE'     => $main->FD_SODATE,
                            'FV_CUSTNAME'   => $main->FV_CUSTNAME,
                            'FC_CUSTCODE'   => $main->FC_CUSTCODE,
                            'FC_STOCKCODE'  => $main->FC_STOCKCODE,
                            'FV_STOCKNAME'  => $main->FV_STOCKNAME,
                            'FC_REGIONDESC' => $main->fc_regiondesc,
                            'SHIPNAME'      => $main->SHIPNAME,
                            'SHIPADDRESS'   => $main->SHIPADDRESS,
                            'FC_CUSTTYPE'   => $main->FC_CUSTTYPE,
                            'FC_CUSTJENIS'  => $main->FC_CUSTJENIS,
                            'UoM'           => $main->FI_UOM,
                            'FN_QTY'        => $main->FN_QTY,
                            'FN_EXTRA'      => $main->FN_EXTRA,
                            'KUBIKASI'      => 0,
                            "KODE_RAYON"    => 'Belum Ada'
                        ]);
                        if ($guard == 100) {
                            DB::connection('sqlsrv')->table('temporarydetailorders')->insert($ready);
                            $guard = 0;
                            $ready = [];
                        }
                        $guard += 1;
                    }
                };
                if ($guard > 0) {
                    DB::connection('sqlsrv')->table('temporarydetailorders')->insert($ready);
                }
                return redirect('/pilih/' . $routing);
            }
            return redirect('/pilih/' . $routing);
        }
    }

    public function confirm(Request $request)
    {
        $user = Auth::user();
        if (isset($_POST["confirm"])) {
            $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$request->norouting'");
            if ($branch) {
                $code_stof = $branch[0]->CODE_STOF;
                if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                    return redirect('/routing-list');
                }
                DB::connection('sqlsrv')->update("UPDATE [d_transaksi].[dbo].[routers]
                                                  SET CONFIRM = 'YES'
                                                  WHERE NOROUTING = '$request->norouting' AND CODE_STOF = '$code_stof'");
                DB::connection('sqlsrv')->update("UPDATE [d_transaksi].[dbo].[routingdetailorders]
                                                  SET CONFIRM = 'YES'
                                                  WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$request->norouting'");
                return redirect('/routing-list');
            }
        } else {
            return redirect('/routing-list');
        }
    }

    public function delete_toko(Request $request)
    {
        if (!isset($_POST['delete'])) {
            return redirect('/routing-list');
        }
        DB::connection('sqlsrv')->delete("DELETE FROM [d_transaksi].[dbo].[routingcustomer]
                                          WHERE NOROUTING = '$request->norouting' 
                                          AND FC_BRANCH = '$request->fc_branch' 
                                          AND FC_SONO = '$request->fc_sono'");

        DB::connection('sqlsrv')->delete("DELETE FROM [d_transaksi].[dbo].[routingdetailorders]
                                          WHERE NOROUTING = '$request->norouting' 
                                          AND FC_BRANCH = '$request->fc_branch' 
                                          AND FC_SONO = '$request->fc_sono'");
        return redirect('/detail-toko-routing/' . $request->norouting);
    }

    public function filter_by(Request $request)
    {
        if (!isset($_POST['filter'])) {
            return redirect('/routing-list');
        }

        $routing = $request->norouting;
        $user = Auth::user();
        $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$routing'");
        if ($branch) {
            $code_stof = $branch[0]->CODE_STOF;
            if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                return redirect('/routing-list');
            }
            $toko = DB::connection('sqlsrv')->select("SELECT DISTINCT FC_SONO FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
            $kubikasi_routing = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[routingdetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
            $total_toko_routing = DB::connection('sqlsrv')->select("SELECT COUNT(NOROUTING) AS toko FROM [d_transaksi].[dbo].[routingcustomer] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
            $info_toko = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
            $data = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.FC_REGIONDESC AS KELURAHAN, SUM(A.KUBIKASI) AS KUBIK 
                                                      FROM [d_transaksi].[dbo].[temporarydetailorders] A WITH (NOLOCK)
                                                      LEFT JOIN [d_transaksi].[dbo].[routingcustomer] B WITH (NOLOCK)
                                                      ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_SONO = B.FC_SONO
                                                      WHERE A.FC_BRANCH = '$code_stof'
                                                      AND B.FC_SONO IS NULL
                                                      GROUP BY A.FC_BRANCH, A.FC_REGIONDESC");
            return view('wh/routing/kelurahan', [
                "data" => $data,
                "kubikasi_load" => $info_toko[0],
                "toko_load"     => $toko,
                "toko_routing" => $total_toko_routing[0],
                "kubikasi"     => $kubikasi_routing[0],
                "isGroup" => false,
                "routing" => $routing,
            ]);
        }
    }

    public function filter_byy(Request $request)
    {
        if (!isset($_POST['filter'])) {
            return redirect('/routing-list');
        }

        $filter = $request->filter_by;
        if ($filter == 'GT') {
            $routing = $request->norouting;
            $user = Auth::user();
            $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$routing'");
            if ($branch) {
                $code_stof = $branch[0]->CODE_STOF;
                if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                    return redirect('/routing-list');
                }
                $toko = DB::connection('sqlsrv')->select("SELECT DISTINCT FC_SONO FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
                $kubikasi_routing = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[routingdetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
                $total_toko_routing = DB::connection('sqlsrv')->select("SELECT COUNT(NOROUTING) AS toko FROM [d_transaksi].[dbo].[routingcustomer] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
                $info_toko = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
                $data = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.FC_SONO, A.FD_SODATE, A.KODE_RAYON,
                                                      A.SHIPNAME, A.SHIPADDRESS,
                                                      A.FC_CUSTCODE, A.FV_CUSTNAME, A.FC_REGIONDESC AS KELURAHAN, SUM(A.KUBIKASI) AS KUBIK
                                                      FROM [d_transaksi].[dbo].[temporarydetailorders] A WITH (NOLOCK) 
                                                      LEFT JOIN [d_transaksi].[dbo].[routingcustomer] B WITH (NOLOCK)
                                                      ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_SONO = B.FC_SONO
                                                      WHERE A.FC_BRANCH = '$code_stof' 
                                                      AND A.FC_CUSTTYPE = 'PR'
                                                      AND A.FC_CUSTJENIS NOT IN ('AI', 'AF', 'AC')
                                                      AND B.FC_SONO IS NULL
                                                      GROUP BY A.FC_BRANCH, A.FC_SONO, A.FD_SODATE, A.KODE_RAYON, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FC_REGIONDESC, A.SHIPNAME, A.SHIPADDRESS
                                                      ORDER BY FC_SONO");
                return view('wh/routing/after_kubikasi', [
                    "data" => $data,
                    "kubikasi_load" => $info_toko[0],
                    "toko_load"     => $toko,
                    "toko_routing" => $total_toko_routing[0],
                    "kubikasi"     => $kubikasi_routing[0],
                    "isGroup" => false,
                    "routing" => $routing,
                ]);
            }
        } elseif ($filter == 'MT') {
            $routing = $request->norouting;
            $user = Auth::user();
            $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$routing'");
            if ($branch) {
                $code_stof = $branch[0]->CODE_STOF;
                if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                    return redirect('/routing-list');
                }
                $toko = DB::connection('sqlsrv')->select("SELECT DISTINCT FC_SONO FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
                $kubikasi_routing = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[routingdetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
                $total_toko_routing = DB::connection('sqlsrv')->select("SELECT COUNT(NOROUTING) AS toko FROM [d_transaksi].[dbo].[routingcustomer] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
                $info_toko = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
                $data = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.FC_SONO, A.FD_SODATE, A.KODE_RAYON,
                                                      A.SHIPNAME, A.SHIPADDRESS,
                                                      A.FC_CUSTCODE, A.FV_CUSTNAME, A.FC_REGIONDESC AS KELURAHAN, SUM(A.KUBIKASI) AS KUBIK
                                                      FROM [d_transaksi].[dbo].[temporarydetailorders] A WITH (NOLOCK) 
                                                      LEFT JOIN [d_transaksi].[dbo].[routingcustomer] B WITH (NOLOCK)
                                                      ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_SONO = B.FC_SONO
                                                      WHERE A.FC_BRANCH = '$code_stof' 
                                                      AND A.FC_CUSTTYPE = 'PR'
                                                      AND A.FC_CUSTJENIS IN ('AI', 'AF', 'AC')
                                                      AND B.FC_SONO IS NULL
                                                      GROUP BY A.FC_BRANCH, A.KODE_RAYON, A.FC_SONO, A.FD_SODATE, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FC_REGIONDESC, A.SHIPNAME, A.SHIPADDRESS
                                                      ORDER BY FC_SONO");
                return view('wh/routing/after_kubikasi', [
                    "data" => $data,
                    "kubikasi_load" => $info_toko[0],
                    "toko_load"     => $toko,
                    "toko_routing" => $total_toko_routing[0],
                    "kubikasi"     => $kubikasi_routing[0],
                    "isGroup" => false,
                    "routing" => $routing,
                ]);
            }
        } elseif ($filter == 'RAYON') {
            $routing = $request->norouting;
            $user = Auth::user();
            $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$routing'");
            if ($branch) {
                $code_stof = $branch[0]->CODE_STOF;
                if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                    return redirect('/routing-list');
                }
                $toko = DB::connection('sqlsrv')->select("SELECT DISTINCT FC_SONO FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
                $kubikasi_routing = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[routingdetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
                $total_toko_routing = DB::connection('sqlsrv')->select("SELECT COUNT(NOROUTING) AS toko FROM [d_transaksi].[dbo].[routingcustomer] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
                $info_toko = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
                $data = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.KODE_RAYON AS RAYON, SUM(A.KUBIKASI) AS KUBIK
                                                      FROM [d_transaksi].[dbo].[temporarydetailorders] A WITH (NOLOCK)
                                                      LEFT JOIN [d_transaksi].[dbo].[routingcustomer] B WITH (NOLOCK)
                                                      ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_SONO = B.FC_SONO
                                                      WHERE A.FC_BRANCH = '$code_stof'
                                                      AND B.FC_SONO IS NULL
                                                      GROUP BY A.FC_BRANCH, A.KODE_RAYON");
                return view('wh/routing/kelurahan', [
                    "data" => $data,
                    "kubikasi_load" => $info_toko[0],
                    "toko_load"     => $toko,
                    "toko_routing" => $total_toko_routing[0],
                    "kubikasi"     => $kubikasi_routing[0],
                    "isGroup" => false,
                    "routing" => $routing,
                ]);
            }
        } else {
            $routing = $request->norouting;
            $user = Auth::user();
            $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$routing'");
            if ($branch) {
                $code_stof = $branch[0]->CODE_STOF;
                if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                    return redirect('/routing-list');
                }
                $toko = DB::connection('sqlsrv')->select("SELECT DISTINCT FC_SONO FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
                $kubikasi_routing = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[routingdetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
                $total_toko_routing = DB::connection('sqlsrv')->select("SELECT COUNT(NOROUTING) AS toko FROM [d_transaksi].[dbo].[routingcustomer] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
                $info_toko = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
                $data = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.FC_REGIONDESC AS KELURAHAN, SUM(A.KUBIKASI) AS KUBIK 
                                                      FROM [d_transaksi].[dbo].[temporarydetailorders] A WITH (NOLOCK)
                                                      LEFT JOIN [d_transaksi].[dbo].[routingcustomer] B WITH (NOLOCK)
                                                      ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_SONO = B.FC_SONO
                                                      WHERE A.FC_BRANCH = '$code_stof'
                                                      AND B.FC_SONO IS NULL
                                                      GROUP BY A.FC_BRANCH, A.FC_REGIONDESC");
                return view('wh/routing/kelurahan', [
                    "data" => $data,
                    "kubikasi_load" => $info_toko[0],
                    "toko_load"     => $toko,
                    "toko_routing" => $total_toko_routing[0],
                    "kubikasi"     => $kubikasi_routing[0],
                    "isGroup" => false,
                    "routing" => $routing,
                ]);
            }
        }
    }

    public function detail_kelurahan(Request $request)
    {
        if (!isset($_POST['detail_kelurahan'])) {
            return redirect('/routing-list');
        }

        $user      = Auth::user();
        $kelurahan = $request->FC_REGIONDESC;
        $routing   = $request->norouting;
        $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$routing'");
        if ($branch) {
            $code_stof = $branch[0]->CODE_STOF;
            if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                return redirect('/routing-list');
            }
            $load = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.FC_SONO, A.FD_SODATE,
                                                          A.FC_CUSTCODE, A.FV_CUSTNAME, A.FC_REGIONDESC AS KELURAHAN, SUM(A.KUBIKASI) AS KUBIK,
                                                          A.SHIPNAME, A.SHIPADDRESS
                                                          FROM [d_transaksi].[dbo].[temporarydetailorders] A WITH (NOLOCK) 
                                                          LEFT JOIN [d_transaksi].[dbo].[routingcustomer] B WITH (NOLOCK)
                                                          ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_SONO = B.FC_SONO
                                                          WHERE A.FC_BRANCH = '$code_stof' 
                                                          AND A.FC_REGIONDESC = '$kelurahan'
                                                          AND B.FC_SONO IS NULL
                                                          GROUP BY A.FC_BRANCH, A.FC_SONO, A.FD_SODATE, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FC_REGIONDESC, A.SHIPNAME, A.SHIPADDRESS
                                                          ORDER BY FC_SONO 
                                                          ");
            $total_toko_routing = DB::connection('sqlsrv')->select("SELECT COUNT(NOROUTING) AS toko FROM [d_transaksi].[dbo].[routingcustomer] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
            $kubikasi_routing = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[routingdetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof' AND NOROUTING = '$routing'");
            $info_toko = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
            $toko = DB::connection('sqlsrv')->select("SELECT DISTINCT FC_SONO FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$code_stof'");
            return view('wh/routing/after_kubikasi', [
                "data" => $load,
                "kubikasi_load" => $info_toko[0],
                "toko_load" => $toko,
                "toko_routing" => $total_toko_routing[0],
                "kubikasi"     => $kubikasi_routing[0],
                "isGroup" => false,
                "routing" => $routing,
            ]);
        }
    }

    public function pilih_kelurahan(Request $request)
    {
        if (!isset($_POST['pilih_kelurahan'])) {
            return redirect('/pilih/' . $request->norouting);
        }
        $user      = Auth::user();
        $routing   = $request->norouting;
        $fc_region = $request->FC_REGIONDESC;
        $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$routing'");
        if ($branch) {
            $code_stof = $branch[0]->CODE_STOF;
            if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                return redirect('/routing-list');
            }
            $data = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, FC_SONO,
                                                             FC_STOCKCODE, FV_STOCKNAME, KODE_RAYON,
                                                             FC_REGIONDESC, FN_QTY, FN_EXTRA, KUBIKASI FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK)
                                                      WHERE FC_REGIONDESC = '$fc_region'");
            $toko = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.FC_SONO, A.FD_SODATE, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FC_REGIONDESC AS KELURAHAN, SUM(A.KUBIKASI) AS KUBIKASI, A.KODE_RAYON
                                                      FROM [d_transaksi].[dbo].[temporarydetailorders] A WITH (NOLOCK)
                                                      WHERE A.FC_BRANCH = '$code_stof' AND A.FC_REGIONDESC = '$fc_region'
                                                      GROUP BY A.FC_BRANCH, A.FC_SONO, A.FD_SODATE, A.FC_CUSTCODE, A.FV_CUSTNAME, A.FC_REGIONDESC, A.KODE_RAYON");
            if ($toko) {
                $will_insert = [];
                $guard       = 0;
                foreach ($toko as $d) {
                    $cek_routing = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, FC_SONO, FC_CUSTCODE FROM [d_transaksi].[dbo].[routingcustomer] WITH (NOLOCK)
                                                                     WHERE FC_BRANCH = '$d->FC_BRANCH' AND FC_SONO = '$d->FC_SONO' AND FC_CUSTCODE = '$d->FC_CUSTCODE'");
                    if (!$cek_routing) {
                        array_push($will_insert, [
                            'FC_BRANCH'     => $d->FC_BRANCH,
                            'NOROUTING'     => $routing,
                            'FC_SONO'       => $d->FC_SONO,
                            'FD_SODATE'     => $d->FD_SODATE,
                            'FC_CUSTCODE'   => $d->FC_CUSTCODE,
                            'FV_CUSTNAME'   => $d->FV_CUSTNAME,
                            'FC_REGIONDESC' => $d->KELURAHAN,
                            'KUBIKASI'      => $d->KUBIKASI,
                            'KODE_RAYON'    => $d->KODE_RAYON
                        ]);
                    }
                    if ($guard == 100) {
                        DB::connection('sqlsrv')->table('routingcustomer')->insert($will_insert);
                        $guard = 0;
                        $will_insert = [];
                    }
                    $guard += 1;
                }
                if ($guard > 0) {
                    DB::connection('sqlsrv')->table('routingcustomer')->insert($will_insert);
                    $guard = 0;
                    $will_insert = [];
                }
            }
            if ($data) {
                $result = [];
                $guard = 0;
                foreach ($data as $d) {
                    $cek_detail = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, FC_SONO, FC_STOCKCODE FROM [d_transaksi].[dbo].[routingdetailorders] WITH (NOLOCK)
                                                                    WHERE FC_BRANCH = '$code_stof' AND FC_SONO = '$d->FC_SONO' AND FC_STOCKCODE = '$d->FC_STOCKCODE'");
                    if (!$cek_detail) {
                        array_push($result, [
                            'FC_BRANCH'    => $d->FC_BRANCH,
                            'NOROUTING'    => $routing,
                            'FC_SONO'      => $d->FC_SONO,
                            'FC_STOCKCODE' => $d->FC_STOCKCODE,
                            'FV_STOCKNAME' => $d->FV_STOCKNAME,
                            'FC_REGIONDESC' => $fc_region,
                            'FN_QTY'       => $d->FN_QTY,
                            'FN_EXTRA'     => $d->FN_EXTRA,
                            'KUBIKASI'     => $d->KUBIKASI,
                            'CONFIRM'      => 'NO',
                            'KODE_RAYON'   => $d->KODE_RAYON
                        ]);
                    }
                    if ($guard == 100) {
                        DB::connection('sqlsrv')->table('routingdetailorders')->insert($result);
                        $result = [];
                        $guard  = 0;
                    }
                    $guard += 1;
                }
                if ($guard > 0) {
                    DB::connection('sqlsrv')->table('routingdetailorders')->insert($result);
                    $result = [];
                    $guard  = 0;
                }
            }
            return redirect('/pilih/' . $routing);
        }
        return redirect('/pilih/' . $routing);
    }

    public function filter_kode(Request $request)
    {
        if (!isset($_POST['filter_kode'])) {
            return redirect('/routing-list');
        }
        $user  = Auth::user();
        $kode    = $request->FC_CUSTCODE;
        $routing = $request->norouting;
        $data   = DB::connection('sqlsrv')->select("SELECT A.FC_BRANCH, A.FV_BRANDNAME, A.FC_SONO, A.FD_SODATE, 
                                                    A.FV_CUSTNAME, A.FC_CUSTCODE, A.FV_STOCKNAME, 
                                                    A.FC_STOCKCODE, A.FC_REGIONDESC, A.UoM, A.FN_QTY, B.FC_SONO,
                                                    A.FN_EXTRA, A.KUBIKASI FROM [d_transaksi].[dbo].[temporarydetailorders] A WITH (NOLOCK)
                                                    INNER JOIN [d_transaksi].[dbo].[routingcustomer] B WITH (NOLOCK)
                                                    ON A.FC_BRANCH = B.FC_BRANCH AND A.FC_CUSTCODE = B.FC_CUSTCODE AND A.FC_SONO = B.FC_SONO
                                                    WHERE A.FC_CUSTCODE = '$kode' AND A.FC_BRANCH = '$user->fc_branch'
                                                    AND A.FC_SONO IS NULL
                                                    ");
        dd($data);
        $total_toko_routing = DB::connection('sqlsrv')->select("SELECT COUNT(NOROUTING) AS toko FROM [d_transaksi].[dbo].[routingcustomer] WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch' AND NOROUTING = '$routing'");
        $kubikasi_routing = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[routingdetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch' AND NOROUTING = '$routing'");
        $info_toko = DB::connection('sqlsrv')->select("SELECT SUM(KUBIKASI) AS KUBIK FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch'");
        $toko = DB::connection('sqlsrv')->select("SELECT DISTINCT FC_SONO FROM [d_transaksi].[dbo].[temporarydetailorders] WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch'");
        return view('wh/routing/after_kubikasi', [
            "data" => $data,
            "kubikasi_load" => $info_toko[0],
            "toko_load"     => $toko,
            "toko_routing" => $total_toko_routing[0],
            "kubikasi"     => $kubikasi_routing[0],
            "isGroup" => false,
            "routing" => $routing,
        ]);
    }

    public function cetak_toko($NOROUTING)
    {
        $user = Auth::user();
        $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$NOROUTING'");
        if ($branch) {
            $code_stof = $branch[0]->CODE_STOF;
            if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                return redirect('/routing-list');
            }
            $cek_routing = DB::connection('sqlsrv')->select("SELECT NOROUTING, CODE_STOF, NOPOL, CONFIRM FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$NOROUTING' AND CODE_STOF = '$code_stof'");
            $data = DB::connection('sqlsrv')->select("SELECT A.*, B.lat, B.long FROM [d_transaksi].[dbo].[routingcustomer] A WITH (NOLOCK)
                                                      LEFT JOIN [CSAREPORT].[dbo].[locations] B WITH (NOLOCK)
                                                      ON A.FC_BRANCH COLLATE DATABASE_DEFAULT = B.branch_id COLLATE DATABASE_DEFAULT AND A.FC_CUSTCODE COLLATE DATABASE_DEFAULT = B.customer_id COLLATE DATABASE_DEFAULT
                                                     WHERE NOROUTING = '$NOROUTING'");
            if (!$data) {
                return redirect('/pilih/' . $NOROUTING);
            }
            return view('wh/routing/cetak_toko', [
                "kubikasi" => $data,
                "pilih"    => true,
                "routing"  => $NOROUTING,
                "confirm"  => $cek_routing[0]
            ]);
        }
        return redirect('/pilih/' . $NOROUTING);
    }

    public function cetak_barang($NOROUTING)
    {
        $user = Auth::user();
        $branch = DB::connection('sqlsrv')->select("SELECT FC_BRANCH, CODE_STOF FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$NOROUTING'");
        if ($branch) {
            $code_stof = $branch[0]->CODE_STOF;
            if ($branch[0]->FC_BRANCH != $user->fc_branch) {
                return redirect('/routing-list');
            }
            // ketika lihat detail barang dengan paramater field NOROUTING.
            $cek_routing = DB::connection('sqlsrv')->select("SELECT NOROUTING, CONFIRM, CODE_STOF, NOPOL FROM [d_transaksi].[dbo].[routers] WITH (NOLOCK) WHERE NOROUTING = '$NOROUTING' AND CODE_STOF = '$code_stof'");

            // jika data kosong, maka redirect ke halaman /routing-list
            if (!$cek_routing) {
                return redirect('/routing-list');
            }

            // jika user, sengaja mengubah norouting di url, ketika get data tidak ada
            if (!$NOROUTING) {
                return redirect('/routing-list');
            }
            $data = DB::connection('sqlsrv')->select("SELECT A.FC_STOCKCODE, A.FV_STOCKNAME, 
                                                     SUM(A.FN_QTY) AS QTY, SUM(A.FN_EXTRA) AS EXTRA, SUM(A.KUBIKASI) AS KUBIK
                                                     FROM [d_transaksi].[dbo].[routingdetailorders] A WITH (NOLOCK)
                                                     WHERE A.FC_BRANCH = '$code_stof' AND A.NOROUTING = '$NOROUTING'
                                                     GROUP BY A.FC_STOCKCODE, A.FV_STOCKNAME");

            return view('wh/routing/cetak_barang', [
                "barang" => $data,
                "routing" => $NOROUTING,
                "confirm" => $cek_routing[0]
            ]);
        }
        return redirect('/pilih/' . $NOROUTING);
    }
}
