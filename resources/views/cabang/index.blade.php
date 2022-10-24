@extends('main')
<?php
use Illuminate\Support\Facades\DB;
?>
@section('contents')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        {{-- <div class="card-header">
                            <a href="/set" class="btn btn-success">Input Routing</a>
                            <a href="/reset" class="btn btn-danger" style="float: right">Reset </a>
                        </div> --}}
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>BRANCH</th>
                                        <th>NAMA CABANG</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($wh as $item)
                                        <tr>
                                            <td>{{ $item->FC_BRANCH }}</td>
                                            <td>{{ $item->FV_NAME }}</td>
                                            <?php
                                                $data = DB::connection('other')->select("SELECT FC_BRANCH FROM [d_master].[dbo].[t_dc] WHERE FC_BRANCH = '$item->FC_BRANCH'");
                                                if(!$data) {
                                                ?>
                                            <td>
                                                <form action="/setting-dc" method="post">
                                                    @csrf
                                                    <input type="hidden" name="FC_BRANCH" value="{{ $item->FC_BRANCH }}">
                                                    <button type="submit" class="btn btn-success">Setting</button>
                                                </form>
                                            </td>
                                            <?php } else { ?>
                                            <td>
                                                <div class="btn-group" role="group" aria-label="Basic example">
                                                    <form action="/setting-dc" method="post">
                                                        @csrf
                                                        <input type="hidden" name="FC_BRANCH"
                                                            value="{{ $item->FC_BRANCH }}">
                                                        <button type="submit" class="btn btn-warning">UNSETTING</button>
                                                    </form>
                                                    <a href="/input-sto/{{ $item->FC_BRANCH }}"
                                                        class="btn btn-primary">Input STO</a>
                                                    <a href="/satelite/{{ $item->FC_BRANCH }}"
                                                        class="btn btn-success">SATELITE</a>
                                                </div>
                                            </td>
                                            <?php } ?>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
