@extends('main')
@section('contents')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <a href="{{ route('input-rayon') }}" class="btn btn-success">Input Rayon</a>
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
                                        <th>Kode Rayon</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td>{{ $item->fc_branch }}</td>
                                            <td>{{ $item->kode_rayon }}</td>
                                            <td>
                                                <a href="/setting-rayon/{{ $item->kode_rayon }}"
                                                    class="btn btn-primary">Setting Toko</a>
                                                <a href="/detail-toko-rayon/{{ $item->kode_rayon }}"
                                                    class="btn btn-success">Detail Toko</a>
                                                <a class="btn btn-danger" data-toggle="modal"
                                                    data-target="#modal-lg{{ $item->kode_rayon }}">Hold</a>
                                                <div class="modal fade" id="modal-lg{{ $item->kode_rayon }}">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title">Warning</h4>
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Apakah anda ingin hold kode rayon
                                                                    {{ $item->kode_rayon }}</p>
                                                            </div>
                                                            <div class="modal-footer justify-content-between">
                                                                <button type="button" class="btn btn-default"
                                                                    data-dismiss="modal">Close</button>
                                                                <form action="/hold-rayon" method="post">
                                                                    @csrf
                                                                    <input type="hidden" name="kode_rayon"
                                                                        value="{{ $item->kode_rayon }}">
                                                                    <button type="submit"
                                                                        class="btn btn-danger">HOLD</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                        <!-- /.modal-content -->
                                                    </div>
                                                    <!-- /.modal-dialog -->
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
