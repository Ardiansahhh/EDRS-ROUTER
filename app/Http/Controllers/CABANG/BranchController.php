<?php

namespace App\Http\Controllers\CABANG;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    public function index()
    {
        $wh = DB::connection('other3')->select("SELECT FC_BRANCH, FV_NAME FROM [d_master].[dbo].[t_cabang]");
        return view('cabang/index', [
            "wh" => $wh
        ]);
    }

    public function setting_dc(Request $request)
    {
        DB::connection('other')->table('t_dc')->insert([
            'FC_BRANCH'  => $request->FC_BRANCH,
            'created_at' => Carbon::now()
        ]);
        return redirect('/cabang');
    }

    public function input_sto($fc_branch)
    {
        return view('cabang/input_sto', [
            'FC_BRANCH' => $fc_branch,
        ]);
    }

    public function store(Request $request)
    {
        $code   = $request->CODE_STOF;
        $name   = $request->name;
        $branch = $request->FC_BRANCH;
        $check = DB::connection('other')->select("SELECT CODE_STOF, FC_BRANCH 
                                                FROM [d_master].[dbo].[t_dc_details] WITH (NOLOCK)
                                                WHERE CODE_STOF = '$code' 
                                                AND FC_BRANCH = '$branch'");
        if (!$check) {
            DB::connection('other')->table('t_dc_details')->insert([
                "CODE_STOF" => $code,
                "FC_BRANCH" => $branch,
                "SATELITE_OFFICE" => $name
            ]);
        }
        return redirect('/cabang');
    }

    public function satelite($fc_branch)
    {
        $data = DB::connection('other')->select("SELECT CODE_STOF, FC_BRANCH, SATELITE_OFFICE FROM [d_master].[dbo].[t_dc_details] WHERE FC_BRANCH = '$fc_branch'");
        return view('cabang/satelite', [
            "data" => $data
        ]);
    }

    public function setup_gt($code_stof, $branch)
    {
        $brand = DB::connection('other3')->select("SELECT A.FC_BRAND, A.FV_BRANDNAME FROM [d_master].[dbo].[t_mbrand] A WITH (NOLOCK)
                                                   WHERE A.fc_holdpo = 'NO' AND FI_ACTIVE = 1");
        return view('cabang/setup_gt', [
            "data" => $brand,
            "code_stof" => $code_stof,
            "branch"    => $branch
        ]);
    }

    public function setup_mt($code_stof, $branch)
    {
        $brand = DB::connection('other3')->select("SELECT A.FC_BRAND, A.FV_BRANDNAME FROM [d_master].[dbo].[t_mbrand] A WITH (NOLOCK)
                                                   WHERE A.fc_holdpo = 'NO' AND FI_ACTIVE = 1");
        return view('cabang/setup_mt', [
            "data" => $brand,
            "code_stof" => $code_stof,
            "branch"    => $branch
        ]);
    }

    public function set_all(Request $request)
    {
        $user = Auth::user();
        $gt   = $request->GT;
        $mt   = $request->MT;
        if (!$gt && !$mt) {
            return redirect('/setup-customer/' . $request->CODE_STOF . '/' . $request->FC_BRANCH)->with('warning', 'Outlet Belum Dipilih');
        } else {
            if ($gt && !$mt) {
                $data = [
                    "FC_BRANCH"   => $request->FC_BRANCH,
                    "BRAND"       => $request->BRAND,
                    "TIPE_OUTLET" => "GT",
                    "CODE_STOF"   => $request->CODE_STOF
                ];
                DB::connection('other')->table('t_setup_customer')->insert($data);
                return redirect('/setup-customer/' . $request->CODE_STOF . '/' . $request->FC_BRANCH)->with('session', 'Outlet Berhasil Di setting');
            } elseif (!$gt && $mt) {
                $data = [
                    "FC_BRANCH"   => $request->FC_BRANCH,
                    "BRAND"       => $request->BRAND,
                    "TIPE_OUTLET" => "MT",
                    "CODE_STOF"   => $request->CODE_STOF
                ];
                DB::connection('other')->table('t_setup_customer')->insert($data);
                return redirect('/setup-customer/' . $request->CODE_STOF . '/' . $request->FC_BRANCH)->with('session', 'Outlet Berhasil Di setting');
            } else {
                $data1 = [
                    "FC_BRANCH"   => $request->FC_BRANCH,
                    "BRAND"       => $request->BRAND,
                    "TIPE_OUTLET" => "GT",
                    "CODE_STOF"   => $request->CODE_STOF
                ];
                $data2 = [
                    "FC_BRANCH"   => $request->FC_BRANCH,
                    "BRAND"       => $request->BRAND,
                    "TIPE_OUTLET" => "MT",
                    "CODE_STOF"   => $request->CODE_STOF
                ];
                DB::connection('other')->table('t_setup_customer')->insert($data1);
                DB::connection('other')->table('t_setup_customer')->insert($data2);
                return redirect('/setup-customer/' . $request->CODE_STOF . '/' . $request->FC_BRANCH)->with('session', 'Outlet Berhasil Di setting');
            }
        }
    }

    public function set_gt(Request $request)
    {
        $check_gt = DB::connection('other')->select("SELECT * FROM [d_master].[dbo].[t_setup_customer] WHERE TIPE_OUTLET = 'GT' AND CODE_STOF = '$request->CODE_STOF'");
        if ($check_gt) {
            DB::connection('other')->delete("DELETE FROM [d_master].[dbo].[t_setup_customer] WHERE TIPE_OUTLET = 'GT' AND CODE_STOF = '$request->CODE_STOF' AND FC_BRANCH = '$request->FC_BRANCH'");
        }

        if (!$request->BRAND) {
            return redirect('/setup-gt/' . $request->CODE_STOF . '/' . $request->FC_BRANCH)->with('session', 'Data Sudah Direset');
        }
        $insert = [];
        $guard  = 0;
        foreach ($request->BRAND as $key => $value) {
            $brand = explode('-', $value);
            array_push($insert, [
                "FC_BRANCH"   => $request->FC_BRANCH,
                "BRAND"       => $brand[1],
                "TIPE_OUTLET" => 'GT',
                "CODE_STOF"   => $request->CODE_STOF,
                "CODE_BRAND"  => $brand[0]
            ]);
            if ($guard == 100) {
                DB::connection('other')->table('t_setup_customer')->insert($insert);
                $insert = [];
                $guard  = 0;
            }
            $guard += 1;
        }
        if ($guard > 0) {
            DB::connection('other')->table('t_setup_customer')->insert($insert);
            $insert = [];
            $guard  = 0;
        }
        return redirect('/setup-gt/' . $request->CODE_STOF . '/' . $request->FC_BRANCH)->with('session', 'Outlet Berhasil Di setting');
    }

    public function set_mt(Request $request)
    {
        $check_mt = DB::connection('other')->select("SELECT * FROM [d_master].[dbo].[t_setup_customer] WHERE TIPE_OUTLET = 'MT' AND CODE_STOF = '$request->CODE_STOF'");
        if ($check_mt) {
            DB::connection('other')->delete("DELETE FROM [d_master].[dbo].[t_setup_customer] WHERE TIPE_OUTLET = 'MT' AND CODE_STOF = '$request->CODE_STOF' AND FC_BRANCH = '$request->FC_BRANCH'");
        }

        if (!$request->BRAND) {
            return redirect('/setup-gt/' . $request->CODE_STOF . '/' . $request->FC_BRANCH)->with('session', 'Data Sudah Direset');
        }
        $insert = [];
        $guard  = 0;
        foreach ($request->BRAND as $key => $value) {
            $brand = explode('-', $value);
            array_push($insert, [
                "FC_BRANCH"   => $request->FC_BRANCH,
                "BRAND"       => $brand[1],
                "TIPE_OUTLET" => 'MT',
                "CODE_STOF"   => $request->CODE_STOF,
                "CODE_BRAND"  => $brand[0]
            ]);
            if ($guard == 100) {
                DB::connection('other')->table('t_setup_customer')->insert($insert);
                $insert = [];
                $guard  = 0;
            }
            $guard += 1;
        }
        if ($guard > 0) {
            DB::connection('other')->table('t_setup_customer')->insert($insert);
            $insert = [];
            $guard  = 0;
        }
        return redirect('/setup-mt/' . $request->CODE_STOF . '/' . $request->FC_BRANCH)->with('session', 'Outlet Berhasil Di setting');
    }
}
