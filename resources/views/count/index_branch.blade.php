@extends('main')
@section('contents')
    <section class="content">
        <div class="container-fluid">
            <div class="scroll mb-3">
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ count($data) }}</h3>
                                <p>TOKO</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-bag"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ round($kubikasi[0]->KUBIKASI, 3) }} M<sup style="font-size: 20px">3</sup></h3>
                                <p>KUBIKASI</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-stats-bars"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <a href="/count" class="btn btn-success">Refresh</a>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>NO.</th>
                                        <th>BRANCH</th>
                                        <th>SALES ORDER</th>
                                        <th>CODE CUST</th>
                                        <th>NAMA</th>
                                        <th>ALAMAT</th>
                                        <th>RAYON</th>
                                        <th>KUBIKASI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td>{{ $no }}</td>
                                            <td>{{ $item->FC_BRANCH }}</td>
                                            <td>{{ $item->FC_SONO }}</td>
                                            <td>{{ $item->FC_CUSTCODE }}</td>
                                            <td>{{ $item->FV_CUSTNAME }}</td>
                                            <td>{{ $item->ALAMAT }}</td>
                                            <td>{{ $item->KODE_RAYON }}</td>
                                            <td>{{ round($item->KUBIKASI, 3) }}</td>
                                        </tr>
                                        <?php $no++; ?>
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
