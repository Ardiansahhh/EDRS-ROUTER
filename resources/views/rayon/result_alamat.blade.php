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
                                                    <h3 class="card-title">LOAD DATA TOKO</h3>
                                                </div>
                                                <!-- /.card-header -->
                                                <div class="card-body">
                                                    @if (!$is_dc)
                                                        <form action="/load-rayon" method="post">
                                                            @csrf
                                                            <input type="hidden" name="FC_BRANCH"
                                                                value="{{ Auth::user()->fc_branch }}">
                                                            <input type="hidden" name="kode_rayon"
                                                                value="{{ $rayon }}">
                                                            <button type="submit" name="load_rayon"
                                                                class="btn btn-success"><i class="fas fa-download"></i> Load
                                                                Data</button>
                                                        </form><br>
                                                    @endif
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
                                                    @if ($is_dc)
                                                        <form action="/load-rayon" method="post">
                                                            @csrf
                                                            <div class="row">
                                                                <div class="col-sm-5">
                                                                    <!-- text input -->
                                                                    <div class="form-group">
                                                                        <input type="hidden" name="kode_rayon"
                                                                            value="{{ $rayon }}">
                                                                        <label>Cabang</label>
                                                                        <select name="fc_branch" class="form-control">
                                                                            @foreach ($dc as $item)
                                                                                <option value="{{ $item->CODE_STOF }}">
                                                                                    {{ $item->SATELITE_OFFICE }}
                                                                                </option>
                                                                            @endforeach
                                                                            <option value="{{ Auth::user()->fc_branch }}">
                                                                                {{ Auth::user()->fc_branch }}
                                                                            </option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-2">
                                                                    <label>Load</label>
                                                                    <div class="form-group">
                                                                        <button type="submit" name="load_rayon"
                                                                            class="form-control btn btn-info"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#exampleModal3">Load</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    @endif
                                                    <form action="/search-customer" method="post">
                                                        @csrf
                                                        <div class="row">
                                                            <div class="col-sm-5">
                                                                <!-- text input -->
                                                                <div class="form-group">
                                                                    <label>Search</label>
                                                                    <select name="pilih" class="form-control">
                                                                        <option value="FC_CUSTCODE">Kode Customer</option>
                                                                        <option value="FV_CUSTNAME">Nama Customer</option>
                                                                        <option value="FV_CUSTADD1">Alamat - IDCard</option>
                                                                        <option value="FV_SHIPADD1">Alamat - Shipto</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <!-- text input -->
                                                                <div class="form-group">
                                                                    <label>Input</label>
                                                                    <input type="hidden" name="kode_rayon"
                                                                        value="{{ $rayon }}">
                                                                    <input type="text" autofocus required
                                                                        class="form-control" name="search"
                                                                        placeholder="Search...">
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-2">
                                                                <label>Searching</label>
                                                                <div class="form-group">
                                                                    <button type="submit" name="search_toko"
                                                                        class="form-control btn btn-info"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#exampleModal3">search</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    <form action="/setting-shipto" method="post">
                                                        @csrf
                                                        <div class="row">
                                                            <div class="col-sm-10">
                                                                <!-- text input -->
                                                                <div class="form-group">
                                                                    <label>Setting Ship</label>
                                                                    <input type="hidden" name="kode_rayon"
                                                                        value="{{ $rayon }}">
                                                                    <input type="text" readonly autofocus required
                                                                        class="form-control" name="FC_SHIPCODE"
                                                                        placeholder="Setting ShipTO">
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-2">
                                                                <label>Setting</label>
                                                                <div class="form-group">
                                                                    <button type="submit" name="setting_rayon"
                                                                        class="form-control btn btn-info"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#exampleModal3">Setting</button>
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
                                        <th>Nama</th>
                                        <th>Alamat</th>
                                        <th>SHIP CODE</th>
                                        <th>Kota</th>
                                        <th>Pilih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($isContent)
                                        @foreach ($data as $item)
                                            <tr>
                                                <td>{{ $item->FC_BRANCH }}</td>
                                                <td>{{ $item->FC_CUSTCODE }}</td>
                                                <td>{{ $item->FV_CUSTNAME }}</td>
                                                <td>{{ $item->ALAMAT }}</td>
                                                <td>{{ $item->FC_SHIPCODE }}</td>
                                                <td>{{ $item->FV_CUSTCITY }}</td>
                                                <form action="/checkbox-rayon" method="post">
                                                    @csrf
                                                    <td><input type="checkbox" name="FC_CUSTCODE[]"
                                                            value="{{ $item->FC_CUSTCODE . '-' . $item->FC_SHIPCODE }}">
                                                        <input type="hidden" name="FC_BRANCH"
                                                            value="{{ $item->FC_BRANCH }}">
                                                        <input type="hidden" name="kode_rayon"
                                                            value="{{ $rayon }}">
                                                    </td>
                                            </tr>
                                        @endforeach
                                    @else
                                    @endif
                                </tbody>
                            </table>
                            <button type="submit" style="border: none"></button>
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
