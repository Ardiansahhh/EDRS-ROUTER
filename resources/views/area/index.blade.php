@extends('main')
@section('contents')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <a href="{{ route('input-area') }}" class="btn btn-success">Input Area</a>
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
                                        @if ($dc)
                                            <th>Satelite Office</th>
                                        @endif
                                        <th>Kode Area</th>
                                        <th>Nama Area</th>
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
