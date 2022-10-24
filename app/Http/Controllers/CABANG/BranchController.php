<?php

namespace App\Http\Controllers\CABANG;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        $data = DB::connection('other')->select("SELECT CODE_STOF, FC_BRANCH, SATELITE_OFFICE FROM [d_master].[dbo].[t_dc_details]");
        return view('cabang/satelite', [
            "data" => $data
        ]);
    }
}
