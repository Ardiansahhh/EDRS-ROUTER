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
                            <form action="/store-area" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-8">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>CABANG</label>
                                            <input type="text" name="fc_branch" class="form-control" readonly
                                                value="{{ Auth::user()->fc_branch }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>Kode Area</label>
                                            <input type="text" maxlength="2" name="kode_area" class="form-control"
                                                required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>Nama Area</label>
                                            <input type="text" name="nama_area" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" name="create_area" class="btn btn-info"><i
                                            class="fa-solid fa-send"></i>Save</button>
                                </div>
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
