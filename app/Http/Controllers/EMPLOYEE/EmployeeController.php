<?php

namespace App\Http\Controllers\EMPLOYEE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;

class EmployeeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $emp = DB::connection('other')->select("SELECT * FROM [d_master].[dbo].[karyawan] WITH (NOLOCK)
                                                    WHERE FC_BRANCH = '$user->fc_branch'");
        return view('employee/index', [
            "data" => $emp
        ]);
    }

    public function input()
    {
        return view('employee/input');
    }

    public function store(Request $request)
    {

        if (!isset($_POST['create_employee'])) {
            return redirect('/employee');
        }

        $branch = Auth::user()->fc_branch;
        $title  = strtoupper($request->FC_TITLENAME);
        $name   = strtoupper($request->FC_NAME);
        $nik    = strtoupper($request->FC_NIK);
        $finger = strtoupper($request->FC_FINGERBADGENO);

        $check_emp = DB::connection('other')->select("SELECT FC_NAME, FC_NIK, FC_FINGERBADGENO FROM [d_master].[dbo].[karyawan] WITH (NOLOCK) 
                                                          WHERE FC_NAME = '$name' AND FC_NIK = '$nik' AND FC_FINGERBADGENO = '$finger'");
        if (!$check_emp) {
            DB::connection('other')->table('karyawan')->insert([
                "FC_BRANCH"        => $branch,
                "FC_TITLENAME"     => $title,
                "FC_NAME"          => $name,
                "FC_NIK"           => $nik,
                "FC_FINGERBADGENO" => $finger
            ]);
            // session()->flash('success', 'Input Successfulyy');
            return redirect()->back()->with('success', 'Input Successfulyy');
        }

        return redirect()->back()->with('session', 'Data Sudah Ada');
    }
}
