<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RayonModel extends Model
{
    use HasFactory;

    function getTemporary($code)
    {
        $user = Auth::user();
        $data = DB::connection('CSAREPORT')->select("SELECT * FROM  [CSAREPORT].[dbo].[t_temporary_customer] WHERE FC_CUSTCODE = '$code'");
        return $data;
    }
}
