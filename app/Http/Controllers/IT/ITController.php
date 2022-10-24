<?php

namespace App\Http\Controllers\IT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ITController extends Controller
{

    public function index()
    {
        $data = DB::connection('other')->select("SELECT A.fc_branch, A.finger_code, A.name, A.email, A.level 
                                                FROM [d_master].[dbo].[users] A WITH (NOLOCK)");
        return view('access/index', [
            "data" => $data
        ]);
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

    public function input()
    {
        $data = DB::connection('other3')->select("SELECT FC_BRANCH, FV_NAME FROM [d_master].[dbo].[t_cabang]");
        return view('access/input', [
            "data" => $data
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fc_branch' => 'required',
            'finger_code' => 'required',
            'name'  => 'required|max:255',
            'email' => 'required|max:255|email:dns',
            'level' => 'required'
        ]);

        $cek = DB::connection('other')->select("SELECT email FROM [d_master].[dbo].[users] WHERE email = '$request->email'");
        if ($cek) {
            return redirect('/input-access')->with('danger', 'Email Sudah digunakan');
        }
        $validated['password'] = bcrypt($request->finger_code);
        DB::connection('other')->table('users')->insert($validated);
        return redirect('/input-access')->with('success', 'Register Successfully');
    }
}
