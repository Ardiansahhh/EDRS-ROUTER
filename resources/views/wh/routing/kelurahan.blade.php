 <?php
 use Illuminate\Support\Facades\DB;
 ?>
 @extends('main')
 @section('contents')
     <section class="content">
         <div class="container-fluid">
             <div class="row justify-content-between">
                 <div class="col-12">
                     <div class="card">
                         <div class="card-header">
                             <div class="form-group">
                                 @if (session()->has('danger'))
                                     <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                         {{ session('danger') }}
                                     </div>
                                 @endif
                                 <div class="row">
                                     <div class="col-lg-3 col-6">
                                         <!-- small box -->
                                         <div class="small-box bg-info">
                                             <div class="inner">
                                                 <h3>{{ $toko_routing->toko }}</h3>
                                                 <p>Detail Toko Routing</p>
                                             </div>
                                             <div class="icon">
                                                 <i class="ion ion-person-add"></i>
                                             </div>
                                             <a href="/detail-toko-routing/{{ $routing }}"
                                                 class="small-box-footer">Detail Toko <i
                                                     class="fas fa-arrow-circle-right"></i></a>
                                         </div>
                                     </div>
                                     <div class="col-lg-3 col-6">
                                         <!-- small box -->
                                         <div class="small-box bg-info">
                                             <div class="inner">
                                                 <h3>{{ $kubikasi->KUBIK / 1000000 }}M<sup style="font-size: 20px">3</sup>
                                                 </h3>
                                                 <p>Kubikasi Barang</p>
                                             </div>
                                             <div class="icon">
                                                 <i class="ion ion-pie-graph"></i>
                                             </div>
                                             <a href="/detail-barang/{{ $routing }}" class="small-box-footer">Detail
                                                 Barang
                                                 <i class="fas fa-arrow-circle-right"></i></a>
                                         </div>
                                     </div>
                                     <div class="col-lg-3 col-6">
                                         <!-- small box -->
                                         <div class="small-box bg-info">
                                             <div class="inner">
                                                 <h3>{{ count($toko_load) }}</h3>
                                                 <p>TOKO</p>
                                             </div>
                                             <div class="icon">
                                                 <i class="ion ion-bag"></i>
                                             </div>
                                             <a href="/pilih/{{ $routing }}" class="small-box-footer">Detail Toko <i
                                                     class="fas fa-arrow-circle-right"></i></a>
                                         </div>
                                     </div>
                                     <div class="col-lg-3 col-6">
                                         <!-- small box -->
                                         <div class="small-box bg-info">
                                             <div class="inner">
                                                 <h3>{{ $kubikasi_load->KUBIK / 1000000 }} M<sup
                                                         style="font-size: 20px">3</sup></h3>

                                                 <p>KUBIKASI</p>
                                             </div>
                                             <div class="icon">
                                                 <i class="ion ion-stats-bars"></i>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                             <section class="content">
                                 <div class="container-fluid">
                                     <div class="row">
                                         <div class="col-md-6">
                                             <!-- general form elements disabled -->
                                             <div class="card card-info">
                                                 <div class="card-header">
                                                     <h3 class="card-title">LOAD DATA ORDERAN</h3>
                                                 </div>
                                                 <!-- /.card-header -->
                                                 <div class="card-body">
                                                     <form action="/range-tanggal" method="post">
                                                         @csrf
                                                         <div class="row">
                                                             <div class="col-sm-5">
                                                                 <!-- text input -->
                                                                 <div class="form-group">
                                                                     <label>From</label>
                                                                     <input type="hidden" name="NOROUTING"
                                                                         value="{{ $routing }}" required>
                                                                     <input type="date" name="tanggal1"
                                                                         class="form-control" required>
                                                                 </div>
                                                             </div>
                                                             <div class="col-sm-5">
                                                                 <div class="form-group">
                                                                     <label>To</label>
                                                                     <input type="date" name="tanggal2"
                                                                         class="form-control" required>
                                                                 </div>
                                                             </div>
                                                             <div class="col-sm-2">
                                                                 <label>Load Data</label>
                                                                 <div class="form-group">
                                                                     <button type="submit"
                                                                         class="form-control btn btn-info"
                                                                         data-bs-toggle="modal"
                                                                         data-bs-target="#exampleModal2">LOAD</button>
                                                                 </div>
                                                             </div>
                                                         </div>
                                                     </form>
                                                     <form action="/load-monthly" method="post">
                                                         @csrf
                                                         <div class="row">
                                                             <div class="col-sm-10">
                                                                 <!-- text input -->
                                                                 <div class="form-group">
                                                                     <label>UNCREATE-DO</label>
                                                                     <input type="hidden" name="norouting"
                                                                         value="{{ $routing }}">
                                                                     <input type="text" class="form-control"
                                                                         value="Data Loading Uncreate-DO" disabled>
                                                                 </div>
                                                             </div>
                                                             <div class="col-sm-2">
                                                                 <label>Load Data</label>
                                                                 <div class="form-group">
                                                                     <button type="submit"
                                                                         class="form-control btn btn-info"
                                                                         data-bs-toggle="modal"
                                                                         data-bs-target="#exampleModal3">LOAD</button>
                                                                 </div>
                                                             </div>
                                                         </div>
                                                     </form>
                                                 </div>
                                                 <!-- /.card-body -->
                                             </div>
                                             <!-- /.card -->
                                         </div>
                                         <!-- left column -->
                                         <div class="col-md-6">

                                             <!-- /.card -->
                                             <!-- Horizontal Form -->
                                             <div class="card card-info">
                                                 <div class="card-header">
                                                     <h3 class="card-title">FILTER TOKO</h3>
                                                 </div>
                                                 <!-- /.card-header -->
                                                 <div class="card-body">
                                                     <form action="filter_byy" method="post">
                                                         @csrf
                                                         <div class="row">
                                                             <div class="col-sm-10">
                                                                 <!-- select -->
                                                                 <div class="form-group">
                                                                     <input type="hidden" name="norouting"
                                                                         value="{{ $routing }}">
                                                                     <label>Berdasarkan</label>
                                                                     <select name="filter_by" class="custom-select">
                                                                         <option value="FC_REGIONDESC">Kelurahan</option>
                                                                         <option value="GT">GT</option>
                                                                         <option value="MT">MT</option>
                                                                     </select>
                                                                 </div>
                                                             </div>
                                                             <div class="col-sm-2">
                                                                 <div class="form-group">
                                                                     <label>Filter</label>
                                                                     <button type="submit" name="filter"
                                                                         class="btn btn-info">Search</button>
                                                                 </div>
                                                             </div>
                                                         </div>
                                                     </form>
                                                     {{-- <form action="/filter_kode" method="post">
                                                         @csrf
                                                         <div class="row">
                                                             <div class="col-sm-10">
                                                                 <!-- text input -->
                                                                 <div class="form-group">
                                                                     <label>Kode Toko</label>
                                                                     <input type="hidden" name="norouting"
                                                                         value="{{ $routing }}">
                                                                     <input type="number" name="FC_CUSTCODE"
                                                                         class="form-control"
                                                                         placeholder="Masukkan Kode Toko" required>
                                                                 </div>
                                                             </div>
                                                             <div class="col-sm-2">
                                                                 <div class="form-group">
                                                                     <label>Filter</label>
                                                                     <button type="submit" name="filter_kode"
                                                                         class="btn btn-info">
                                                                         Search</button>
                                                                 </div>
                                                             </div>
                                                         </div>
                                                     </form> --}}
                                                 </div>
                                             </div>
                                             <!-- /.card -->
                                         </div>
                                         <!--/.col (right) -->
                                     </div>
                                     <!-- /.row -->
                                 </div><!-- /.container-fluid -->
                             </section>
                         </div>
                         <!-- /.card-header -->
                         <div class="card-body">
                             <table id="example2" class="table table-bordered table-hover">
                                 <tr>
                                     <th>NO</th>
                                     <th>Branch</th>
                                     <th>KELURAHAN</th>
                                     <th>KUBIKASI</th>
                                     @if ($data)
                                         <th>ACTION</th>
                                     @endif
                                 </tr>
                                 </thead>
                                 <tbody>
                                     <?php $no = 1; ?>
                                     @foreach ($data as $d)
                                         <tr>
                                             <td>{{ $no }}</td>
                                             <td>{{ $d->FC_BRANCH }}</td>
                                             @if ($d->KELURAHAN != null)
                                                 <td>{{ $d->KELURAHAN }}</td>
                                             @else
                                                 <td>Belum Ada (NULL) </td>
                                             @endif
                                             <td>{{ $d->KUBIK / 1000000 }}</td>
                                             <td>
                                                 <div class="btn-group">
                                                     <form action="/detail-kelurahan" method="post">
                                                         @csrf
                                                         <input type="hidden" name="FC_REGIONDESC"
                                                             value="{{ $d->KELURAHAN }}">
                                                         <input type="hidden" name="norouting"
                                                             value="{{ $routing }}">
                                                         <button type="submit" name="detail_kelurahan"
                                                             class="btn btn-success">Detail Toko
                                                         </button>
                                                     </form>
                                                     &nbsp; &nbsp;
                                                     <form action="/pilih-kelurahan" method="post">
                                                         @csrf
                                                         <input type="hidden" name="norouting"
                                                             value="{{ $routing }}">
                                                         <input type="hidden" name="FC_REGIONDESC"
                                                             value="{{ $d->KELURAHAN }}">
                                                         <button type="submit" name="pilih_kelurahan"
                                                             class="btn btn-primary">Pilih
                                                         </button>
                                                     </form>
                                                 </div>
                                             </td>
                                         </tr>
                                         <?php $no++; ?>
                                     @endforeach
                                 </tbody>
                             </table>
                         </div>
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
 <!-- Modal -->
 <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="exampleModalLabel">Sedang Proses
                 </h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 Harap Menunggu Data Anda Sedang diproses
             </div>
         </div>
     </div>
 </div>
 <!-- Modal -->
 <div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="exampleModalLabel">Sedang Proses
                 </h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 Harap Menunggu Data Anda Sedang diproses
             </div>
         </div>
     </div>
 </div>

 <!-- Modal -->
 <div class="modal fade" id="exampleModal3" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="exampleModalLabel">Sedang Proses
                 </h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 Harap Menunggu Data Anda Sedang diproses
             </div>
         </div>
     </div>
 </div>
