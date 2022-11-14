@extends('main')
@section('contents')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        @if (session()->has('session'))
                            <div class="alert alert-danger alert-dismissible text-center fade show" role="alert">
                                {{ session('session') }}
                            </div>
                        @endif
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissible text-center fade show" role="alert">
                                {{ session('success') }}</div>
                        @endif
                        <div class="card-header">
                            <h3 class="card-title">Input Rayon</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <form action="/store-rayon" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-2">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>CABANG</label>
                                            <input type="text" name="fc_branch" class="form-control" disabled
                                                value="{{ Auth::user()->fc_branch }}">
                                        </div>
                                    </div>
                                </div>
                                <table id="example2" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>BRANCH</th>
                                            @if ($dc)
                                                <th>Satelite Office</th>
                                            @endif
                                            <th>Kode Area</th>
                                            <th>Nama Area</th>
                                            <th>Pilih</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data as $item)
                                            <tr>
                                                <td>{{ $item->fc_branch }}</td>
                                                @if ($dc)
                                                    <td>{{ $item->code_stof }}</td>
                                                @endif
                                                <td>{{ $item->kode_area }}</td>
                                                <td>{{ $item->nama_area }}</td>
                                                <td>
                                                    <form action="/store-rayon" method="post">
                                                        <input type="checkbox" name="kode_area[]"
                                                            value="{{ $item->kode_area }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="card-footer">
                                    <button type="submit" name="create_rayon" style="border: none"></button>
                                </div>
                            </form>
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
