@extends('main')
@section('contents')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <a href="{{ route('input-loader') }}" class="btn btn-success">Input Loader / Picker</a>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>BRANCH</th>
                                        <th>JABATAN</th>
                                        <th>NAMA</th>
                                        <th>NIK</th>
                                        <th>KODE FINGER</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td>{{ $item->FC_BRANCH }}</td>
                                            <td>{{ $item->FC_TITLENAME }}</td>
                                            <td>{{ $item->FC_NAME }}</td>
                                            <td>{{ $item->FC_NIK }}</td>
                                            <td>{{ $item->FC_FINGERBADGENO }}</td>
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
