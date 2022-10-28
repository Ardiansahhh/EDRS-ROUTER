@extends('main')
@section('contents')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <section class="content">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <!-- general form elements disabled -->
                                            <div class="card card-info">
                                                <div class="card-header">
                                                    <h3 class="card-title">LOAD DATA ORDERAN</h3>
                                                </div>
                                                <!-- /.card-header -->
                                                <div class="card-body">
                                                    <form action="/load-rayon" method="post">
                                                        @csrf
                                                        <input type="hidden" name="FC_BRANCH"
                                                            value="{{ Auth::user()->fc_branch }}">
                                                        <input type="hidden" name="kode_rayon" value="{{ $rayon }}">
                                                        <button type="submit" name="load_rayon" class="btn btn-success"><i
                                                                class="fas fa-download"></i> Load Data</button>
                                                    </form><br>
                                                    <form action="/pilih-toko-rayon" method="post">
                                                        @csrf
                                                        <div class="row">
                                                            <div class="col-sm-10">
                                                                <!-- text input -->
                                                                <div class="form-group">
                                                                    <label>Input Code Customer</label>
                                                                    <input type="hidden" name="kode_rayon"
                                                                        value="{{ $rayon }}">
                                                                    <input type="number" required class="form-control"
                                                                        name="FC_CUSTCODE" placeholder="Kode Pelanggan">
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-2">
                                                                <label>Simpan</label>
                                                                <div class="form-group">
                                                                    <button type="submit" name="pilih_toko_rayon"
                                                                        class="form-control btn btn-info"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#exampleModal3">Simpan</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                                <!-- /.card-body -->
                                            </div>
                                            <!-- /.card -->
                                        </div>
                                        <div class="col-md-6">
                                            <!-- general form elements disabled -->
                                            <div class="card card-info">
                                                <div class="card-header">
                                                    <h3 class="card-title">Informasi Toko</h3>
                                                </div>
                                                <!-- /.card-header -->
                                                <div class="card-body">
                                                    <div class="col-lg-6 col-6">
                                                        <!-- small box -->
                                                        <div class="small-box bg-info">
                                                            <div class="inner">
                                                                <h3>18</h3>
                                                                <p>Detail Toko Routing</p>
                                                            </div>
                                                            <div class="icon">
                                                                <i class="ion ion-person-add"></i>
                                                            </div>
                                                            <a href="/detail-toko-routing/" class="small-box-footer">Detail
                                                                Toko <i class="fas fa-arrow-circle-right"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /.card-body -->
                                            </div>
                                            <!-- /.card -->
                                        </div>
                                    </div>
                                    <!-- /.row -->
                                </div><!-- /.container-fluid -->
                            </section>
                        </div>
                        @if (session()->has('session'))
                            <div class="alert alert-danger alert-dismissible text-center fade show" role="alert">
                                {{ session('session') }}
                            </div>
                        @endif
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissible text-center fade show" role="alert">
                                {{ session('success') }}</div>
                        @endif
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>BRANCH</th>
                                        <th>Kode Customer</th>
                                        <th>Nama</th>
                                        <th>Alamat</th>
                                        <th>Kota</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($isContent)
                                        @foreach ($data as $item)
                                            <tr>
                                                <td>{{ $item->FC_BRANCH }}</td>
                                                <td>{{ $item->FC_CUSTCODE }}</td>
                                                <td>{{ $item->FV_CUSTNAME }}</td>
                                                <td>{{ $item->FV_CUSTADD1 }}</td>
                                                <td>{{ $item->FV_CUSTCITY }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                    @endif
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
