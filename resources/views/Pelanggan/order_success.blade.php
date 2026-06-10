@extends('Layout.layout_pelanggan')

@section('title', 'Order Success - Zyh Corner')

@section('breadcrumb')
    <a href="{{ route('userChart') }}">Shopping Cart</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span>Checkout</span>
@endsection

@section('content')
<div class="success-container" style="max-width: 600px; margin: 0 auto; padding: 80px 15px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
    <div class="success-illustration" style="margin-bottom: 25px; max-width: 320px; width: 100%;">
        <img src="{{ asset('image/order_success.png') }}" alt="Order Received Successfully" style="width: 100%; height: auto; object-fit: contain;">
    </div>
    
    <h1 style="font-size: 36px; font-weight: 600; color: #1a1a1a; margin-bottom: 12px; font-family: 'Poppins', sans-serif;">Order Received!</h1>
    
    <p style="font-size: 15px; color: #666; margin-bottom: 40px; font-family: 'Poppins', sans-serif;">Our team will process your order immediately.</p>
    
    <div class="success-buttons" style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center;">
        <a href="#" class="btn-track" style="display: inline-flex; align-items: center; justify-content: center; background-color: #f2fcf3; color: #00b207; font-weight: 600; font-size: 14px; padding: 14px 32px; border-radius: 50px; text-decoration: none; border: 1.5px solid #00b207; transition: all 0.3s; height: 45px; min-width: 160px; font-family: 'Poppins', sans-serif;">Tracking Order</a>
        <a href="{{ route('home') }}" class="btn-home" style="display: inline-flex; align-items: center; justify-content: center; background-color: #00b207; color: white; font-weight: 600; font-size: 14px; padding: 14px 32px; border-radius: 50px; text-decoration: none; transition: all 0.3s; height: 45px; min-width: 160px; font-family: 'Poppins', sans-serif;">Back to Home</a>
    </div>
</div>

<style>
    .btn-track:hover {
        background-color: #e6f9e8 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 178, 7, 0.1);
    }
    .btn-home:hover {
        background-color: #009906 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 178, 7, 0.2);
    }
</style>
@endsection
