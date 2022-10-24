@extends('main')
@section('contents')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <a href="{{ route('WH') }}" class="btn btn-success">Input Routing</a>
                            {{-- <a href="/reset" class="btn btn-danger" style="float: right">Reset </a> --}}
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
                                        <th>ACTION</th>
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
                                            <td>
                                                <div class="btn-group">
                                                    <?php if($item->CONFIRM != "YES") { ?>
                                                    <a href="/pilih/{{ $item->NOROUTING }}"
                                                        class="btn btn-primary">Detail</a>
                                                    <form action="/confirm" method="post">
                                                        @csrf
                                                        <input type="hidden" name="norouting"
                                                            value="{{ $item->NOROUTING }}">
                                                        <button type="submit" name="confirm"
                                                            class="btn btn-success">Confirm</button>
                                                    </form>
                                                    <?php } else { ?>
                                                    <a href="/detail-toko-routing/{{ $item->NOROUTING }}"
                                                        class="btn btn-primary">Detail</a>
                                                    <?php } ?>
                                                </div>
                                            </td>
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
