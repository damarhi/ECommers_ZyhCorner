@extends('Layout.layout_pAdmin')

@section('content')
          <div class="content-wrapper">
            <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
              <h3 class="page-title m-0">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                  <i class="mdi mdi-home"></i>
                </span> Dasbor
              </h3>
              <nav aria-label="breadcrumb">
                <ul class="breadcrumb mb-0">
                  <li class="breadcrumb-item active" aria-current="page">
                    <span></span>Ringkasan <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"></i>
                  </li>
                </ul>
              </nav>
            </div>
            <div class="row">
              <div class="col-md-4 stretch-card grid-margin">
                <div class="card bg-gradient-danger card-img-holder text-white">
                  <div class="card-body">
                    <img src="{{ asset('PurpleAdmin/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Penjualan Mingguan <i class="mdi mdi-chart-line mdi-24px float-end"></i>
                    </h4>
                    <h2 class="mb-5">Rp 15.000.000</h2>
                    <h6 class="card-text">Meningkat sebesar 60%</h6>
                  </div>
                </div>
              </div>
              <div class="col-md-4 stretch-card grid-margin">
                <div class="card bg-gradient-info card-img-holder text-white">
                  <div class="card-body">
                    <img src="{{ asset('PurpleAdmin/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Pesanan Mingguan <i class="mdi mdi-bookmark-outline mdi-24px float-end"></i>
                    </h4>
                    <h2 class="mb-5">45,6334</h2>
                    <h6 class="card-text">Menurun sebesar 10%</h6>
                  </div>
                </div>
              </div>
              <div class="col-md-4 stretch-card grid-margin">
                <div class="card bg-gradient-success card-img-holder text-white">
                  <div class="card-body">
                    <img src="{{ asset('PurpleAdmin/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                    <h4 class="font-weight-normal mb-3">Pengunjung Online <i class="mdi mdi-diamond mdi-24px float-end"></i>
                    </h4>
                    <h2 class="mb-5">95,5741</h2>
                    <h6 class="card-text">Meningkat sebesar 5%</h6>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-7 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="clearfix">
                      <h4 class="card-title float-start">Statistik Kunjungan dan Penjualan</h4>
                      <div id="visit-sale-chart-legend" class="rounded-legend legend-horizontal legend-top-right float-end"></div>
                    </div>
                    <canvas id="visit-sale-chart" class="mt-4"></canvas>
                  </div>
                </div>
              </div>
              <div class="col-md-5 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Sumber Lalu Lintas</h4>
                    <div class="doughnutjs-wrapper d-flex justify-content-center">
                      <canvas id="traffic-chart"></canvas>
                    </div>
                    <div id="traffic-chart-legend" class="rounded-legend legend-vertical legend-bottom-left pt-4"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12 grid-margin">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Tiket Terbaru</h4>
                    <div class="table-responsive">
                      <table class="table">
                        <thead>
                          <tr>
                            <th> Penerima Tugas </th>
                            <th> Subjek </th>
                            <th> Status </th>
                            <th> Pembaruan Terakhir </th>
                            <th> ID Pelacakan </th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>
                              <img src="{{ asset('PurpleAdmin/assets/images/faces/face1.jpg') }}" class="me-2" alt="image"> David Grey
                            </td>
                            <td> Dana tidak diterima </td>
                            <td>
                              <label class="badge badge-gradient-success">SELESAI</label>
                            </td>
                            <td> Des 5, 2017 </td>
                            <td> WD-12345 </td>
                          </tr>
                          <tr>
                            <td>
                              <img src="{{ asset('PurpleAdmin/assets/images/faces/face2.jpg') }}" class="me-2" alt="image"> Stella Johnson
                            </td>
                            <td> Waktu muat lama </td>
                            <td>
                              <label class="badge badge-gradient-warning">DALAM PROSES</label>
                            </td>
                            <td> Des 12, 2017 </td>
                            <td> WD-12346 </td>
                          </tr>
                          <tr>
                            <td>
                              <img src="{{ asset('PurpleAdmin/assets/images/faces/face3.jpg') }}" class="me-2" alt="image"> Marina Michel
                            </td>
                            <td> Website down selama satu minggu </td>
                            <td>
                              <label class="badge badge-gradient-info">DITANGGUHKAN</label>
                            </td>
                            <td> Des 16, 2017 </td>
                            <td> WD-12347 </td>
                          </tr>
                          <tr>
                            <td>
                              <img src="{{ asset('PurpleAdmin/assets/images/faces/face4.jpg') }}" class="me-2" alt="image"> John Doe
                            </td>
                            <td> Kehilangan kontrol pada server </td>
                            <td>
                              <label class="badge badge-gradient-danger">DITOLAK</label>
                            </td>
                            <td> Des 3, 2017 </td>
                            <td> WD-12348 </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
@endsection
