@extends('layouts.modern-layout.master')

@section('title')Apex Chart
 {{ $title }}
@endsection

@push('css')
@endpush

@section('content')
	@component('components.breadcrumb')
		@slot('breadcrumb_title')
			<h3>Apex Chart</h3>
		@endslot
		<li class="breadcrumb-item">Charts</li>
		<li class="breadcrumb-item active">Apex Chart</li>
	@endcomponent
	
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12 col-xl-6 box-col-6">
				<div class="card">
					<div class="card-header pb-0">
						<h5>Basic Area Chart</h5>
					</div>
					<div class="card-body">
						<div id="basic-apex"></div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-xl-6 box-col-6">
				<div class="card">
					<div class="card-header pb-0">
						<h5>Area Spaline Chart</h5>
					</div>
					<div class="card-body">
						<div id="area-spaline"></div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-xl-6 box-col-6">
				<div class="card">
					<div class="card-header pb-0">
						<h5>Bar chart</h5>
					</div>
					<div class="card-body">
						<div id="basic-bar"></div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-xl-6 box-col-6">
				<div class="card">
					<div class="card-header pb-0">
						<h5>Column Chart</h5>
					</div>
					<div class="card-body">
						<div id="column-chart"></div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-xl-6 box-col-12">
				<div class="card">
					<div class="card-header pb-0">
						<h5>
							3d Bubble Chart
						</h5>
					</div>
					<div class="card-body">
						<div id="chart-bubble"></div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-xl-6 box-col-12">
				<div class="card">
					<div class="card-header pb-0">
						<h5>Candlestick Chart</h5>
					</div>
					<div class="card-body">
						<div id="candlestick"></div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-xl-6 box-col-6">
				<div class="card">
					<div class="card-header pb-0">
						<h5>Stepline Chart</h5>
					</div>
					<div class="card-body">
						<div id="stepline"></div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-xl-6 box-col-6">
				<div class="card">
					<div class="card-header pb-0">
						<h5>Column Chart</h5>
					</div>
					<div class="card-body">
						<div id="annotationchart"></div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-xl-6 box-col-6">
				<div class="card">
					<div class="card-header pb-0">
						<h5>Pie Chart</h5>
					</div>
					<div class="card-body apex-chart">
						<div id="piechart"></div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-xl-6 box-col-6">
				<div class="card">
					<div class="card-header pb-0">
						<h5>Donut Chart</h5>
					</div>
					<div class="card-body apex-chart">
						<div id="donutchart"></div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-xl-12 box-col-12">
				<div class="card">
					<div class="card-header pb-0">
						<h5>Mixed Chart</h5>
					</div>
					<div class="card-body">
						<div id="mixedchart"></div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-xl-6 box-col-6">
				<div class="card">
					<div class="card-header pb-0">
						<h5>Radar Chart</h5>
					</div>
					<div class="card-body">
						<div id="radarchart"></div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-xl-6 box-col-6">
				<div class="card">
					<div class="card-header pb-0">
						<h5>Radial Bar Chart</h5>
					</div>
					<div class="card-body">
						<div id="circlechart"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	
	@push('scripts')
	<script src="{{ asset('assets/js/chart/apex-chart/apex-chart.js') }}"></script>
    <script src="{{ asset('assets/js/chart/apex-chart/stock-prices.js') }}"></script>
    <script src="{{ asset('assets/js/chart/apex-chart/chart-custom.js') }}"></script>
	@endpush

@endsection