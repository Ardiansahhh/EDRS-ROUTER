<?php

namespace App\Http\Controllers\VEHICLE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;

class VehicleController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $vehicle = DB::connection('other')->select("SELECT * FROM [d_master].[dbo].[kendaraan] WITH (NOLOCK)
                                                    WHERE FC_BRANCH = '$user->fc_branch'");
        return view('vehicle/index', [
            "data" => $vehicle
        ]);
    }

    public function input()
    {
        return view('vehicle/input');
    }

    public function store(Request $request)
    {
        if (!isset($_POST['create_vehicle'])) {
            return redirect('/vehicle');
        }

        $branch = Auth::user()->fc_branch;
        $nopol  = strtoupper($request->NOPOL);
        $tipe   = $request->TIPE;
        $vendor =  strtoupper($request->VENDOR);
        $kubikasi = $request->KUBIKASI;

        $check_vehicle = DB::connection('other')->select("SELECT FC_BRANCH, NOPOL FROM [d_master].[dbo].[kendaraan] WITH (NOLOCK) 
                                                          WHERE FC_BRANCH = '$branch' AND NOPOL = '$nopol'");
        if (!$check_vehicle) {
            DB::connection('other')->table('kendaraan')->insert([
                "FC_BRANCH" => $branch,
                "NOPOL"     => $nopol,
                "VENDOR"    => $vendor,
                "TIPE"      => $tipe,
                "KUBIKASI"  => $kubikasi
            ]);
            // session()->flash('success', 'Input Successfulyy');
            return redirect()->back()->with('success', 'Input Successfulyy');
        }

        return redirect()->back()->with('session', 'Data Sudah Ada');
    }
}
