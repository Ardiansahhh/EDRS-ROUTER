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
                            <h3 class="card-title">Input Loader / Picker</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <form action="/store-employee" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-10">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>CABANG</label>
                                            <input type="text" name="FC_BRANCH" class="form-control" disabled
                                                value="{{ Auth::user()->fc_branch }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>JABATAN</label>
                                            <select class="form-control" name="FC_TITLENAME">
                                                <option value="LOADER">LOADER</option>
                                                <option value="PICKER">PICKER</option>
                                                <option value="CHECKER">CHECKER</option>
                                                <option value="WH LEADER">WH LEADER</option>
                                                <option value="ROUTE PLANNER">ROUTE PLANNER</option>
                                                <option value="LEADER ADMIN">LEADER ADMIN</option>
                                                <option value="STAFF ADMIN">STAFF ADMIN</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>NAMA</label>
                                            <input type="text" name="FC_NAME" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>NIK</label>
                                            <input type="number" name="FC_NIK" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>KODE FINGER</label>
                                            <input type="number" name="FC_FINGERBADGENO" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" name="create_employee" class="btn btn-info"><i
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
