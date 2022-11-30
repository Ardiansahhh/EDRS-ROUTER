<?php use Illuminate\Support\Facades\DB; ?>
@extends('main')
@section('contents')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    @if (session()->has('session'))
                        <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                            {{ session('session') }}
                        </div>
                    @endif
                    @if (session()->has('warning'))
                        <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                            {{ session('warning') }}
                        </div>
                    @endif
                    <div class="card">
                        <div class="card-body">
                            <a href="/satelite/{{ $branch }}" class="btn btn-success mb-3">Kembali</a>
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>CODE BRAND</th>
                                        <th>NAMA BRAND</th>
                                        <th>GT <input type="checkbox" onchange="checkAllGT(this)"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td>{{ $no }}</td>
                                            <td>{{ $item->FC_BRAND }}</td>
                                            <td>{{ $item->FV_BRANDNAME }}</td>
                                            <form action="/set-gt" method="post">
                                                @csrf
                                                <td>
                                                    <?php $check = DB::connection('other')->select("SELECT * FROM [d_master].[dbo].[t_setup_customer] WHERE FC_BRANCH = '$branch' AND CODE_BRAND = '$item->FC_BRAND' AND CODE_STOF = '$code_stof' AND TIPE_OUTLET = 'GT'"); ?>
                                                    <input type="checkbox" class="GT" name="BRAND[]"
                                                        value="{{ $item->FC_BRAND . '-' . $item->FV_BRANDNAME }}"
                                                        <?php if ($check) {
                                                            echo 'checked';
                                                        } ?>>
                                                </td>
                                                <input type="hidden" name="CODE_STOF" value="{{ $code_stof }}">
                                                <input type="hidden" name="FC_BRANCH" value="{{ $branch }}">
                                        </tr>
                                        <?php $no++; ?>
                                    @endforeach
                                </tbody>
                            </table>
                            <button type="submit" class="btn btn-primary mt-3" style="float:right">Simpan</button>
                            </form>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
        </div><!-- /.container-fluid -->
    </section>
@endsection
