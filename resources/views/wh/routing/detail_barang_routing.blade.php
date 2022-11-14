 <?php use Illuminate\Support\Facades\DB; ?>
 @extends('main')
 @section('contents')
     <section class="content">
         <div class="container-fluid">
             <div class="row">
                 <div class="col-12">
                     <div class="card">
                         <div class="card-header">
                             <?php if($confirm->CONFIRM == 'YES') { ?>
                             <a href="/routing-list" class="btn btn-primary">Kembali</a>
                             <?php } else { ?>
                             <a href="/pilih/{{ $routing }}" class="btn btn-primary">Kembali</a>
                             <?php } ?>
                             <a href="/cetak-barang/{{ $routing }}" class="btn btn-primary"><i
                                     class="fas fa-print"></i>
                                 Print Barang</a>
                         </div>
                         <!-- /.card-header -->
                         <div class="card-body">
                             <table id="example2" class="table table-bordered table-hover">
                                 <thead>
                                     <tr>
                                         <th>NO</th>
                                         <th>STOCKCODE</th>
                                         <th>STOCKNAME</th>
                                         <th>Karton</th>
                                         <th>Pcs</th>
                                         <th>EXTRA(pcs)</th>
                                         <th>Total Keseluruhan</th>
                                         <th>KUBIKASI</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     <?php $no = 1; ?>
                                     @foreach ($barang as $d)
                                         <tr>
                                             <td>{{ $no }}</td>
                                             <td>{{ $d->FC_STOCKCODE }}</td>
                                             <td>{{ $d->FV_STOCKNAME }}</td>
                                             <td><?php floor((int) $d->QTY / (int) $d->UOM); ?></td>
                                             <td>
                                                 <?php if ((int) $d->QTY >= (int) $d->UOM) {
                                                     echo (int) $d->QTY % (int) $d->UOM;
                                                 } else {
                                                     echo (int) $d->QTY;
                                                 } ?>
                                             </td>
                                             <td>{{ $d->EXTRA }}</td>
                                             <td>{{ $d->QTY }}</td>
                                             <td>{{ $d->KUBIK / 1000000 }}</td>
                                             <?php $no++; ?>
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
 {{-- <a href="/hitung" class="btn btn-primary">Recount</a>
                             <a href="/group" class="btn btn-primary">Group</a> --}}
