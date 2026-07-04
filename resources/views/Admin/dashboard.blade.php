@extends('Layout.layout_pAdmin')

@section('title', 'Dashboard - Admin')

@section('content')
          <div class="content-wrapper">
            <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
              <h3 class="page-title m-0">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                  <i class="mdi mdi-home"></i>
                </span> Dashboard
              </h3>
              <nav aria-label="breadcrumb">
                <ul class="breadcrumb mb-0">
                  <li class="breadcrumb-item active" aria-current="page">
                    <span></span>Ringkasan
                  </li>
                </ul>
              </nav>
            </div>
            <div class="row">
              <div class="col-md-4 stretch-card grid-margin">
                <div class="card stat-card stat-card-coral border-0 text-white">
                  <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon">
                      <i class="mdi mdi-chart-line"></i>
                    </div>
                    <div>
                      <p class="stat-label mb-0">Penjualan Mingguan</p>
                      <h3 class="stat-value">Rp 15.000.000</h3>
                      <p class="stat-change mb-0">Meningkat sebesar 60%</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4 stretch-card grid-margin">
                <div class="card stat-card stat-card-indigo border-0 text-white">
                  <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon">
                      <i class="mdi mdi-bookmark-outline"></i>
                    </div>
                    <div>
                      <p class="stat-label mb-0">Pesanan Mingguan</p>
                      <h3 class="stat-value">45,6334</h3>
                      <p class="stat-change mb-0">Menurun sebesar 10%</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4 stretch-card grid-margin">
                <div class="card stat-card stat-card-teal border-0 text-white">
                  <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon">
                      <i class="mdi mdi-diamond"></i>
                    </div>
                    <div>
                      <p class="stat-label mb-0">Pengunjung Online</p>
                      <h3 class="stat-value">95,5741</h3>
                      <p class="stat-change mb-0">Meningkat sebesar 5%</p>
                    </div>
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
                              <label class="badge badge-soft-success">SELESAI</label>
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
                              <label class="badge badge-soft-warning">DALAM PROSES</label>
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
                              <label class="badge badge-soft-info">DITANGGUHKAN</label>
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
                              <label class="badge badge-soft-danger">DITOLAK</label>
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
