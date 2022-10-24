@extends('main')
@section('contents')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <a href="/check" class="btn btn-success"><i class="fas fa-pen"></i> Cek Barang
                                Cabang
                            </a>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>BRANCH</th>
                                        <th>NO.ROUTING</th>
                                        <th>NAME</th>
                                        <th>TANGGAL</th>
                                        <th>CODE STO</th>
                                        <th>NOPOL</th>
                                        <th>NIK</th>
                                        <th>LOADER</th>
                                        <th>BARANG</th>
                                        <th>KENDARAAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td>{{ $item->FC_BRANCH }}</td>
                                            <td>{{ $item->NOROUTING }}</td>
                                            <td>{{ $item->NAME }}</td>
                                            <td>{{ $item->DATE }}</td>
                                            <td>{{ $item->CODE_STOF }}</td>
                                            <td>{{ $item->NOPOL }}</td>
                                            <td>{{ $item->FC_NIK }}</td>
                                            <td>{{ $item->FC_NAME }}</td>
                                            <td>{{ $item->KUBIK / 1000000 }} M<sup style="font-size: 10px">3</sup></td>
                                            <td>{{ $item->KUBIKASI }} M<sup style="font-size: 10px">3</sup> - 1,5 M <sup
                                                    style="font-size: 10px">3</sup></td>
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
