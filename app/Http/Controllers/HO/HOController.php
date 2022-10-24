<?php

namespace App\Http\Controllers\HO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HOController extends Controller
{
    public function index()
    {
        $data = DB::connection('sqlsrv')->select("SELECT A.NOROUTING, A.NAME, A.FC_BRANCH, A.DATE, A.CODE_STOF, A.NOPOL, A.CONFIRM, A.FC_NIK, B.FC_NAME, SUM(C.KUBIKASI) AS KUBIK, D.KUBIKASI
                                                 FROM [d_transaksi].[dbo].[routers] A WITH (NOLOCK)
                                                 LEFT JOIN [d_master].[dbo].[karyawan] B WITH (NOLOCK)
                                                 ON A.FC_NIK = B.FC_NIK
                                                 LEFT JOIN [d_transaksi].[dbo].[routingcustomer] C WITH (NOLOCK)
                                                 ON A.NOROUTING = C.NOROUTING
                                                 LEFT JOIN [d_master].[dbo].[kendaraan] D WITH (NOLOCK)
                                                 ON A.NOPOL = D.NOPOL
                                                 GROUP BY A.NOROUTING, A.NAME, A.FC_BRANCH, A.DATE, A.CODE_STOF, A.NOPOL, A.CONFIRM, A.FC_NIK, B.FC_NAME, D.KUBIKASI
                                                 ORDER BY A.NOROUTING ASC");

        return view('ho/list', [
            "data" => $data
        ]);
    }

    public function routing()
    {
        $data = DB::connection('sqlsrv')->select("SELECT A.NOROUTING, A.NAME, A.FC_BRANCH, A.DATE, A.CODE_STOF, A.NOPOL, A.CONFIRM, A.FC_NIK, B.FC_NAME, SUM(C.KUBIKASI) AS KUBIK, D.KUBIKASI
                                                 FROM [d_transaksi].[dbo].[routers] A WITH (NOLOCK)
                                                 LEFT JOIN [d_master].[dbo].[karyawan] B WITH (NOLOCK)
                                                 ON A.FC_NIK = B.FC_NIK
                                                 LEFT JOIN [d_transaksi].[dbo].[routingcustomer] C WITH (NOLOCK)
                                                 ON A.NOROUTING = C.NOROUTING
                                                 LEFT JOIN [d_master].[dbo].[kendaraan] D WITH (NOLOCK)
                                                 ON A.NOPOL = D.NOPOL
                                                 GROUP BY A.NOROUTING, A.NAME, A.FC_BRANCH, A.DATE, A.CODE_STOF, A.NOPOL, A.CONFIRM, A.FC_NIK, B.FC_NAME, D.KUBIKASI
                                                 ORDER BY A.NOROUTING ASC");

        return view('ho/list', [
            "data" => $data
        ]);
    }

    public function check_barang()
    {
        $data = DB::connection('sqlsrv')->select("SELECT DISTINCT A.FC_STOCKCODE, A.FV_STOCKNAME, B.CODE_STOF
                                                  FROM [d_transaksi].[dbo].[routingdetailorders] A WITH (NOLOCK)
                                                  LEFT JOIN [d_transaksi].[dbo].[routers] B WITH (NOLOCK)
                                                  ON A.NOROUTING = B.NOROUTING AND A.FC_BRANCH = B.CODE_STOF
                                                  WHERE B.CONFIRM = 'NO' AND A.KUBIKASI = 0");
        return view('ho/detail_barang', [
            "barang" => $data
        ]);
    }

    public function input_kubikasi(Request $request)
    {
        if (!isset($_POST['btn_filter'])) {
            return redirect('/HO');
        }

        $branch    = $request->FC_BRANCH;
        $stockcode = $request->FC_STOCKCODE;
        $data = DB::connection('other3')->select("SELECT A.FC_BRANCH, A.FC_STOCKCODE, A.FV_STOCKNAME, B.FI_UOM, C.FV_BRANDNAME 
                                                  FROM [d_master].[dbo].[t_stock] A WITH (NOLOCK)
                                                  LEFT JOIN [d_master].[dbo].[t_mpack] B WITH (NOLOCK) ON
                                                  A.FC_BRAND = B.FC_BRAND AND 
                                                  A.FC_GROUP = B.FC_GROUP AND 
                                                  A.FC_SUBGRP = B.FC_SUBGRP AND
                                                  A.FC_TYPE = B.FC_TYPE AND
                                                  A.FC_PACK = B.FC_PACK
                                                  LEFT JOIN [d_master].[dbo].[t_mbrand] C WITH (NOLOCK) ON
                                                  A.FC_BRAND = C.FC_BRAND
                                                  WHERE A.FC_BRANCH = '$branch' AND A.FC_STOCKCODE = '$stockcode'")[0];
        return view('ho/input', [
            "data" => $data,
            'barang' => $request
        ]);
    }
}
