@extends('main')
@section('contents')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <form action="/load-rayon" method="post">
                                @csrf
                                <input type="hidden" name="FC_BRANCH" value="{{ Auth::user()->fc_branch }}">
                                <input type="hidden" name="kode_rayon" value="{{ $rayon }}">
                                <button type="submit" name="load_rayon" class="btn btn-success"><i
                                        class="fas fa-download"></i> Load Data</button>
                            </form>
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
