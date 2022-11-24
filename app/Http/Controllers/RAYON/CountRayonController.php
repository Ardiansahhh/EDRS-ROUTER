<?php

namespace App\Http\Controllers\RAYON;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CountRayonController extends Controller
{

    public function index()
    {
        $user  = Auth::user();
        $dc    = DB::connection('other')->select("SELECT * FROM [d_master].[dbo].[t_dc] WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch'");
        if ($dc) {
            $data = $this->getData();
            return view('count/index', [
                'data' => $data,
                'kubikasi' => $this->countKubikasi(),
                'jumlah_toko' => $this->jumlahTokoDC()
            ]);
        } else {
            $data = $this->getData();
            return view('count/index_branch', [
                'data' => $data,
                'kubikasi' => $this->countKubikasi()
            ]);
        }
    }

    public function getData()
    {
        $user  = Auth::user();
        $dt    = Carbon::now();
        $month = date('Ym', strtotime($dt));
        $dc    = DB::connection('other')->select("SELECT * FROM [d_master].[dbo].[t_dc] WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch'");
        if ($dc) {
            $data  = DB::connection('other4')->select("SELECT ZX.FC_BRANCH, SUM(ZX.KUBIKASI) / 1000000 AS KUBIKASI FROM
                                                    (SELECT 
                                                    A.FC_BRANCH,
                                                    A.FC_SONO,
                                                    D.FD_SODATE,
                                                    D.FC_CUSTCODE,
                                                    CASE 
                                                        WHEN D.FC_SHIPTO IS NULL THEN '0'
                                                        WHEN D.FC_SHIPTO = '' THEN '0'
                                                    ELSE D.FC_SHIPTO
                                                    END AS SHIPTO,
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
                                                    ISNULL (J.FV_SHIPADD1,'') AS SHIPADDRESS,
                                                    CASE 
                                                        WHEN X.KUBIKASI_PCS IS NOT NULL THEN X.KUBIKASI_PCS*A.FN_QTY
                                                        ELSE 0
                                                    END AS KUBIKASI,
                                                    CASE
                                                        WHEN Z.kode_rayon IS NOT NULL THEN Z.kode_rayon 
                                                        ELSE 'Belum Ada'
                                                    END AS KODE_RAYON,
                                                    Z.fv_custcity
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
                                                    LEFT JOIN [192.169.1.21].[d_master].[dbo].[MASTER_KUBIKASI] X WITH (NOLOCK)
                                                        ON A.FC_STOCKCODE COLLATE DATABASE_DEFAULT = X.FC_STOCKCODE COLLATE DATABASE_DEFAULT
                                                    LEFT JOIN [192.169.1.21].[CSAREPORT].[dbo].[t_rayon_detail] Z WITH (NOLOCK)
                                                    ON A.FC_BRANCH COLLATE DATABASE_DEFAULT = Z.code_stof COLLATE DATABASE_DEFAULT AND D.FC_CUSTCODE COLLATE DATABASE_DEFAULT = Z.fc_custcode COLLATE DATABASE_DEFAULT
                                                    WHERE 
                                                        D.FC_STATUS IN ('I', 'X')
                                                        AND 
                                                        D.FC_SOTYPE IN ('A','B')
                                                        AND 
                                                            D.FN_ITEMDO < 1
                                                        AND
                                                            F.FC_CUSTTYPE = 'PR'
                                                        AND
                                                        A.FC_BRANCH IN (SELECT CODE_STOF FROM [192.169.1.21].[d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$user->fc_branch')
                                                        AND B.FC_HOLD = 'NO'
                                                        AND
                                                        RIGHT('0'+CAST(DATEPART(YEAR,D.FD_SODATE)AS VARCHAR(4)),4)
                                                        +RIGHT('0'+CAST(DATEPART(MONTH,D.FD_SODATE)AS VARCHAR(2)),2)
                                                            ='$month') AS ZX GROUP BY ZX.FC_BRANCH");
            return $data;
        } else {
            $data  = DB::connection('other4')->select("SELECT ZX.FC_BRANCH, ZX.FC_SONO, ZX.KODE_RAYON, ZX.FC_CUSTCODE, ZX.FV_CUSTNAME, SUM(ZX.KUBIKASI) / 1000000 AS KUBIKASI FROM
                                                    (SELECT 
                                                    A.FC_BRANCH,
                                                    A.FC_SONO,
                                                    D.FD_SODATE,
                                                    D.FC_CUSTCODE,
                                                    CASE 
                                                        WHEN D.FC_SHIPTO IS NULL THEN '0'
                                                        WHEN D.FC_SHIPTO = '' THEN '0'
                                                    ELSE D.FC_SHIPTO
                                                    END AS SHIPTO,
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
                                                    ISNULL (J.FV_SHIPADD1,'') AS SHIPADDRESS,
                                                    CASE 
                                                        WHEN X.KUBIKASI_PCS IS NOT NULL THEN X.KUBIKASI_PCS*A.FN_QTY
                                                        ELSE 0
                                                    END AS KUBIKASI,
                                                    CASE
                                                        WHEN Z.kode_rayon IS NOT NULL THEN Z.kode_rayon 
                                                        ELSE 'Belum Ada'
                                                    END AS KODE_RAYON,
                                                    Z.fv_custcity
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
                                                    LEFT JOIN [192.169.1.21].[d_master].[dbo].[MASTER_KUBIKASI] X WITH (NOLOCK)
                                                        ON A.FC_STOCKCODE COLLATE DATABASE_DEFAULT = X.FC_STOCKCODE COLLATE DATABASE_DEFAULT
                                                    LEFT JOIN [192.169.1.21].[CSAREPORT].[dbo].[t_rayon_detail] Z WITH (NOLOCK)
                                                    ON A.FC_BRANCH COLLATE DATABASE_DEFAULT = Z.code_stof COLLATE DATABASE_DEFAULT AND D.FC_CUSTCODE COLLATE DATABASE_DEFAULT = Z.fc_custcode COLLATE DATABASE_DEFAULT
                                                    WHERE 
                                                        D.FC_STATUS IN ('I', 'X')
                                                        AND 
                                                        D.FC_SOTYPE IN ('A','B')
                                                        AND 
                                                            D.FN_ITEMDO < 1
                                                        AND
                                                            F.FC_CUSTTYPE = 'PR'
                                                        AND
                                                        A.FC_BRANCH = '$user->fc_branch'
                                                        AND B.FC_HOLD = 'NO'
                                                        AND
                                                        RIGHT('0'+CAST(DATEPART(YEAR,D.FD_SODATE)AS VARCHAR(4)),4)
                                                        +RIGHT('0'+CAST(DATEPART(MONTH,D.FD_SODATE)AS VARCHAR(2)),2)
                                                            ='$month') AS ZX GROUP BY ZX.FC_BRANCH, ZX.FC_SONO, ZX.FC_CUSTCODE, ZX.FV_CUSTNAME, ZX.KODE_RAYON");
            return $data;
        }
    }

    public function countKubikasi()
    {
        $user  = Auth::user();
        $dt    = Carbon::now();
        $month = date('Ym', strtotime($dt));
        $dc    = DB::connection('other')->select("SELECT * FROM [d_master].[dbo].[t_dc] WITH (NOLOCK) WHERE FC_BRANCH = '$user->fc_branch'");
        if ($dc) {
            $data  = DB::connection('other4')->select("SELECT SUM(ZX.KUBIKASI) / 1000000 AS KUBIKASI FROM
                                                        (SELECT 
                                                        A.FC_BRANCH,
                                                        A.FC_SONO,
                                                        D.FD_SODATE,
                                                        D.FC_CUSTCODE,
                                                        CASE 
                                                            WHEN D.FC_SHIPTO IS NULL THEN '0'
                                                            WHEN D.FC_SHIPTO = '' THEN '0'
                                                        ELSE D.FC_SHIPTO
                                                        END AS SHIPTO,
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
                                                        ISNULL (J.FV_SHIPADD1,'') AS SHIPADDRESS,
                                                        CASE 
                                                            WHEN X.KUBIKASI_PCS IS NOT NULL THEN X.KUBIKASI_PCS*A.FN_QTY
                                                            ELSE 0
                                                        END AS KUBIKASI,
                                                        CASE
                                                            WHEN Z.kode_rayon IS NOT NULL THEN Z.kode_rayon 
                                                            ELSE 'Belum Ada'
                                                        END AS KODE_RAYON,
                                                        Z.fv_custcity
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
                                                        LEFT JOIN [192.169.1.21].[d_master].[dbo].[MASTER_KUBIKASI] X WITH (NOLOCK)
                                                            ON A.FC_STOCKCODE COLLATE DATABASE_DEFAULT = X.FC_STOCKCODE COLLATE DATABASE_DEFAULT
                                                        LEFT JOIN [192.169.1.21].[CSAREPORT].[dbo].[t_rayon_detail] Z WITH (NOLOCK)
                                                        ON A.FC_BRANCH COLLATE DATABASE_DEFAULT = Z.code_stof COLLATE DATABASE_DEFAULT AND D.FC_CUSTCODE COLLATE DATABASE_DEFAULT = Z.fc_custcode COLLATE DATABASE_DEFAULT
                                                        WHERE 
                                                            D.FC_STATUS IN ('I', 'X')
                                                            AND 
                                                            D.FC_SOTYPE IN ('A','B')
                                                            AND 
                                                                D.FN_ITEMDO < 1
                                                            AND
                                                                F.FC_CUSTTYPE = 'PR'
                                                            AND
                                                            A.FC_BRANCH IN (SELECT CODE_STOF FROM [192.169.1.21].[d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$user->fc_branch')
                                                            AND B.FC_HOLD = 'NO'
                                                            AND
                                                            RIGHT('0'+CAST(DATEPART(YEAR,D.FD_SODATE)AS VARCHAR(4)),4)
                                                            +RIGHT('0'+CAST(DATEPART(MONTH,D.FD_SODATE)AS VARCHAR(2)),2)
                                                                ='$month') AS ZX");
            return $data;
        } else {
            $data  = DB::connection('other4')->select("SELECT ZX.FC_BRANCH, SUM(ZX.KUBIKASI) / 1000000 AS KUBIKASI FROM
                                                        (SELECT 
                                                        A.FC_BRANCH,
                                                        A.FC_SONO,
                                                        D.FD_SODATE,
                                                        D.FC_CUSTCODE,
                                                        CASE 
                                                            WHEN D.FC_SHIPTO IS NULL THEN '0'
                                                            WHEN D.FC_SHIPTO = '' THEN '0'
                                                        ELSE D.FC_SHIPTO
                                                        END AS SHIPTO,
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
                                                        ISNULL (J.FV_SHIPADD1,'') AS SHIPADDRESS,
                                                        CASE 
                                                            WHEN X.KUBIKASI_PCS IS NOT NULL THEN X.KUBIKASI_PCS*A.FN_QTY
                                                            ELSE 0
                                                        END AS KUBIKASI,
                                                        CASE
                                                            WHEN Z.kode_rayon IS NOT NULL THEN Z.kode_rayon 
                                                            ELSE 'Belum Ada'
                                                        END AS KODE_RAYON,
                                                        Z.fv_custcity
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
                                                        LEFT JOIN [192.169.1.21].[d_master].[dbo].[MASTER_KUBIKASI] X WITH (NOLOCK)
                                                            ON A.FC_STOCKCODE COLLATE DATABASE_DEFAULT = X.FC_STOCKCODE COLLATE DATABASE_DEFAULT
                                                        LEFT JOIN [192.169.1.21].[CSAREPORT].[dbo].[t_rayon_detail] Z WITH (NOLOCK)
                                                        ON A.FC_BRANCH COLLATE DATABASE_DEFAULT = Z.code_stof COLLATE DATABASE_DEFAULT AND D.FC_CUSTCODE COLLATE DATABASE_DEFAULT = Z.fc_custcode COLLATE DATABASE_DEFAULT
                                                        WHERE 
                                                            D.FC_STATUS IN ('I', 'X')
                                                            AND 
                                                            D.FC_SOTYPE IN ('A','B')
                                                            AND 
                                                                D.FN_ITEMDO < 1
                                                            AND
                                                                F.FC_CUSTTYPE = 'PR'
                                                            AND
                                                            A.FC_BRANCH = '$user->fc_branch'
                                                            AND B.FC_HOLD = 'NO'
                                                            AND
                                                            RIGHT('0'+CAST(DATEPART(YEAR,D.FD_SODATE)AS VARCHAR(4)),4)
                                                            +RIGHT('0'+CAST(DATEPART(MONTH,D.FD_SODATE)AS VARCHAR(2)),2)
                                                                ='$month') AS ZX GROUP BY ZX.FC_BRANCH");
            return $data;
        }
    }

    public function jumlahTokoDC()
    {
        $user  = Auth::user();
        $dt    = Carbon::now();
        $month = date('Ym', strtotime($dt));
        $data  = DB::connection('other4')->select("SELECT DISTINCT ZX.FC_SONO FROM
                                                        (SELECT 
                                                        A.FC_BRANCH,
                                                        A.FC_SONO,
                                                        D.FD_SODATE,
                                                        D.FC_CUSTCODE,
                                                        CASE 
                                                            WHEN D.FC_SHIPTO IS NULL THEN '0'
                                                            WHEN D.FC_SHIPTO = '' THEN '0'
                                                        ELSE D.FC_SHIPTO
                                                        END AS SHIPTO,
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
                                                        ISNULL (J.FV_SHIPADD1,'') AS SHIPADDRESS,
                                                        CASE 
                                                            WHEN X.KUBIKASI_PCS IS NOT NULL THEN X.KUBIKASI_PCS*A.FN_QTY
                                                            ELSE 0
                                                        END AS KUBIKASI,
                                                        CASE
                                                            WHEN Z.kode_rayon IS NOT NULL THEN Z.kode_rayon 
                                                            ELSE 'Belum Ada'
                                                        END AS KODE_RAYON,
                                                        Z.fv_custcity
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
                                                        LEFT JOIN [192.169.1.21].[d_master].[dbo].[MASTER_KUBIKASI] X WITH (NOLOCK)
                                                            ON A.FC_STOCKCODE COLLATE DATABASE_DEFAULT = X.FC_STOCKCODE COLLATE DATABASE_DEFAULT
                                                        LEFT JOIN [192.169.1.21].[CSAREPORT].[dbo].[t_rayon_detail] Z WITH (NOLOCK)
                                                        ON A.FC_BRANCH COLLATE DATABASE_DEFAULT = Z.code_stof COLLATE DATABASE_DEFAULT AND D.FC_CUSTCODE COLLATE DATABASE_DEFAULT = Z.fc_custcode COLLATE DATABASE_DEFAULT
                                                        WHERE 
                                                            D.FC_STATUS IN ('I', 'X')
                                                            AND 
                                                            D.FC_SOTYPE IN ('A','B')
                                                            AND 
                                                                D.FN_ITEMDO < 1
                                                            AND
                                                                F.FC_CUSTTYPE = 'PR'
                                                            AND
                                                            A.FC_BRANCH IN (SELECT CODE_STOF FROM [192.169.1.21].[d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$user->fc_branch')
                                                            AND B.FC_HOLD = 'NO'
                                                            AND
                                                            RIGHT('0'+CAST(DATEPART(YEAR,D.FD_SODATE)AS VARCHAR(4)),4)
                                                            +RIGHT('0'+CAST(DATEPART(MONTH,D.FD_SODATE)AS VARCHAR(2)),2)
                                                                ='$month') AS ZX");
        return $data;
    }
}
