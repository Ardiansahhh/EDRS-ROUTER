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
                                                    @if (session()->has('session'))
                                                        <div class="alert alert-danger alert-dismissible text-center fade show"
                                                            role="alert">
                                                            {{ session('session') }}
                                                        </div>
                                                    @endif
                                                    @if (session()->has('success'))
                                                        <div class="alert alert-success alert-dismissible text-center fade show"
                                                            role="alert">
                                                            {{ session('success') }}</div>
                                                    @endif
                                                    <form action="/pilih-toko-rayon" method="post">
                                                        @csrf
                                                        <div class="row">
                                                            <div class="col-sm-10">
                                                                <!-- text input -->
                                                                <div class="form-group">
                                                                    <label>Input Code Customer</label>
                                                                    <input type="hidden" name="kode_rayon"
                                                                        value="{{ $rayon }}">
                                                                    <input type="number" autofocus required
                                                                        class="form-control" name="FC_CUSTCODE"
                                                                        placeholder="Kode Pelanggan">
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-2">
                                                                <label>Simpan</label>
                                                                <div class="form-group">
                                                                    <button type="submit" name="pilih_toko_rayon"
                                                                        class="form-control btn btn-info">Simpan</button>
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
                                                                <h3>{{ $total }}</h3>
                                                                <p>Detail Toko Routing</p>
                                                            </div>
                                                            <div class="icon">
                                                                <i class="ion ion-person-add"></i>
                                                            </div>
                                                            <a href="/detail-toko-rayon/{{ $rayon }}"
                                                                class="small-box-footer">Detail
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
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>BRANCH</th>
                                        <th>Kode Customer</th>
                                        <th>Rayon</th>
                                        <th>Nama</th>
                                        <th>Alamat</th>
                                        <th>Kota</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($isContent)
                                        @foreach ($data as $item)
                                            <tr>
                                                <td>{{ $item->fc_branch }}</td>
                                                <td>{{ $item->kode_rayon }}</td>
                                                <td>{{ $item->fc_custcode }}</td>
                                                <td>{{ $item->fv_custname }}</td>
                                                <td>{{ $item->fv_custadd1 }}</td>
                                                <td>{{ $item->fv_custcity }}</td>
                                                <td>
                                                    <form action="/hapus-toko-rayon" method="post">
                                                        @csrf
                                                        <input type="hidden" name="kode_rayon"
                                                            value="{{ $item->kode_rayon }}">
                                                        <input type="hidden" name="fc_custcode"
                                                            value="{{ $item->fc_custcode }}">
                                                        <input type="hidden" name="fc_branch"
                                                            value="{{ $item->fc_branch }}">
                                                        <button type="submit" class="btn btn-danger"
                                                            name="hapus_toko">Hapus</button>
                                                    </form>
                                                </td>
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
