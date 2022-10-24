 @extends('main')
 @section('contents')
     <section class="content">
         <div class="container-fluid">
             <div class="row">
                 <div class="col-12">
                     <div class="card">
                         <div class="card-header">
                             <a href="/input" class="btn btn-info">Input Kubikasi Barang</a>
                             <a href="/check-kubikasi-empty" class="btn btn-info">Check Kubikasi Kosong</a>
                         </div>
                         @if (session()->has('error'))
                             <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                                 {{ session('error') }}
                             </div>
                         @endif
                         @if (session()->has('success'))
                             <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                 {{ session('success') }}
                             </div>
                         @endif
                         <!-- /.card-header -->
                         <div class="card-body">
                             <table id="example2" class="table table-bordered table-hover">
                                 @if (!$empty)
                                     <thead>
                                         <tr>
                                             <th>BRAND</th>
                                             <th>CODE</th>
                                             <th>NAME</th>
                                             <th>UoM</th>
                                             <th>VOLUME CTN</th>
                                             <th>VOLUME PCS</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                         @foreach ($data as $d)
                                             <tr>
                                                 <td>{{ $d->FV_BRANDNAME }}</td>
                                                 <td>{{ $d->FC_STOCKCODE }}</td>
                                                 <td>{{ $d->FV_STOCKNAME }}</td>
                                                 <td>{{ floor($d->UOM) }}</td>
                                                 <td>{{ $d->VOLUME }}</td>
                                                 <td>{{ round($d->KUBIKASI_PCS / 1000000, 7) }}</td>
                                             </tr>
                                         @endforeach
                                     </tbody>
                                 @else
                                     <thead>
                                         <tr>
                                             <th>BRAND</th>
                                             <th>CODE</th>
                                             <th>NAME</th>
                                             <th>UoM</th>
                                             <th>ACTION</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                         @foreach ($data as $d)
                                             <tr>
                                                 <td>{{ $d->FV_BRANDNAME }}</td>
                                                 <td>{{ $d->FC_STOCKCODE }}</td>
                                                 <td>{{ $d->FV_STOCKNAME }}</td>
                                                 <td>{{ floor($d->UOM) }}</td>
                                                 <td>
                                                     <form action="/store-empty" method="post">
                                                         @csrf
                                                         <div class="input-group input-group-sm">
                                                             <input type="hidden" name="FV_BRANDNAME"
                                                                 value="{{ $d->FV_BRANDNAME }}">
                                                             <input type="hidden" name="FC_STOCKCODE"
                                                                 value="{{ $d->FC_STOCKCODE }}">
                                                             <input type="hidden" name="FV_STOCKNAME"
                                                                 value="{{ $d->FV_STOCKNAME }}">
                                                             <input type="hidden" name="UOM"
                                                                 value="{{ floor($d->UOM) }}">
                                                             <input type="number" name="KUBIKASI_CTN" class="form-control"
                                                                 required>
                                                             <span class="input-group-append">
                                                                 <button type="submit" name="store"
                                                                     class="btn btn-info btn-flat"><i
                                                                         class="fas fa-save"></i></button>
                                                             </span>
                                                         </div>
                                                     </form>
                                                 </td>
                                             </tr>
                                         @endforeach
                                     </tbody>
                                 @endif
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
