@extends('Layout.layout_pelanggan')

@section('title', 'Home - Zyh Corner')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ file_exists(public_path('css/home.css')) ? filemtime(public_path('css/home.css')) : time() }}">
    <style>
        .breadcrumb-wrapper { display: none !important; }

        .home-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        .hero-slider-wrapper {
            margin-top: 28px !important;
            margin-bottom: 36px !important;
        }

        /* Hero Section (Single Unified Canvas with Full-Bleed Showcase & Feathered Spotlight Melt) */
        .hero-section {
            background-color: #edf2ee;
            border: 1px solid #dbe8dd;
            border-radius: 24px;
            display: flex !important;
            align-items: center !important;
            min-height: 520px !important;
            position: relative !important;
            overflow: hidden !important;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.04), 0 4px 16px rgba(0, 178, 7, 0.06);
            transition: border-color 0.35s ease, box-shadow 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            padding: 0 !important;
            margin: 0 !important;
        }
        .hero-section:hover {
            border-color: #b9dac0;
            box-shadow: 0 20px 42px rgba(0, 0, 0, 0.06), 0 8px 24px rgba(0, 178, 7, 0.10);
        }
        /* Unified Dual-Tone Spotlight & Seamless Melt Layer */
        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 2;
            background: 
                radial-gradient(circle at 10% 25%, rgba(0, 178, 7, 0.08) 0%, transparent 45%),
                linear-gradient(90deg, 
                    #edf2ee 0%, 
                    rgba(237, 242, 238, 0.98) 42%, 
                    rgba(237, 242, 238, 0.85) 54%, 
                    rgba(237, 242, 238, 0.35) 75%, 
                    rgba(237, 242, 238, 0) 100%
                );
            pointer-events: none;
            transition: opacity 0.35s ease;
        }
        /* Full-Bleed Showcase Image (58% width across right side) */
        .hero-image,
        .hero-showcase-bg {
            position: absolute !important;
            top: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 58% !important;
            height: 100% !important;
            min-height: 520px !important;
            margin: 0 !important;
            padding: 0 !important;
            z-index: 1 !important;
            overflow: hidden !important;
            display: block !important;
        }
        .hero-image-frame {
            position: absolute !important;
            inset: 0 !important;
            width: 100% !important;
            height: 100% !important;
            display: block !important;
            overflow: hidden !important;
        }
        .hero-image img,
        .hero-image-frame img {
            position: absolute !important;
            inset: 0 !important;
            width: 100% !important;
            height: 100% !important;
            max-width: none !important;
            max-height: none !important;
            object-fit: cover !important;
            object-position: center right !important;
            display: block !important;
            filter: saturate(1.05) contrast(1.02);
            transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .hero-section:hover .hero-image img,
        .hero-section:hover .hero-image-frame img {
            transform: scale(1.03);
        }
        .hero-image::before,
        .hero-text::before {
            display: none !important;
        }
        /* Floating Content Area (Free-flowing on the left) */
        .hero-text,
        .hero-content {
            position: relative !important;
            z-index: 3 !important;
            max-width: 620px !important;
            width: 100% !important;
            padding: 55px 45px 55px 75px !important;
            box-sizing: border-box !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            align-items: flex-start !important;
        }
        .hero-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(0, 178, 7, 0.09);
            color: #008a05;
            border: 1px solid rgba(0, 178, 7, 0.28);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(0, 178, 7, 0.08);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            transition: all 0.3s ease;
        }
        .hero-text h1 {
            font-size: 48px;
            font-weight: 700;
            color: #1a1a1a;
            line-height: 1.15;
            margin-bottom: 12px;
            letter-spacing: -0.8px;
            max-width: 580px;
        }
        .hero-sale-callout {
            font-size: 21px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .hero-sale-highlight {
            color: #ff8a00;
            font-weight: 700;
        }
        .hero-text p.desc {
            color: #555555;
            font-size: 14.5px;
            margin-top: 4px;
            margin-bottom: 22px;
            max-width: 500px;
            line-height: 1.55;
        }
        .hero-btn {
            margin-top: 0;
            background: #00b207;
            color: #ffffff !important;
            border: none;
            padding: 13px 32px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14.5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
            box-shadow: 0 6px 20px rgba(0, 178, 7, 0.28);
        }
        .hero-btn:hover {
            background: #009906;
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(0, 178, 7, 0.40);
        }
        .hero-btn i { transition: transform 0.25s ease; }
        .hero-btn:hover i { transform: translateX(4px); }
        /* Frosted Glass Slider Arrows (48px Circle with Ambient Blur) */
        /* Frosted Glass Slider Arrows - Hidden by default, reveal on hover */
        .hero-slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%) scale(0.9);
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(14px) !important;
            -webkit-backdrop-filter: blur(14px) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.95) !important;
            color: #1a1a1a !important;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            opacity: 0;
            pointer-events: none;
            font-size: 15px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.10) !important;
        }
        .hero-slider-wrapper:hover .hero-slider-arrow {
            opacity: 0.92;
            pointer-events: auto;
            transform: translateY(-50%) scale(1);
        }
        .hero-slider-arrow:hover {
            background: #ffffff !important;
            color: #00b207 !important;
            border-color: #00b207 !important;
            opacity: 1 !important;
            transform: translateY(-50%) scale(1.1) !important;
            box-shadow: 0 8px 26px rgba(0, 178, 7, 0.25) !important;
        }
        .hero-slider-prev { left: 24px !important; }
        .hero-slider-next { right: 24px !important; }

        /* Indicator Dots (Glassmorphic Floating Pill) */
        .hero-slider-dots {
            position: absolute;
            bottom: 24px;
            left: 75px;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 10;
            background: rgba(0, 0, 0, 0.20);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 6px 14px;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.20);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .hero-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.65);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .hero-dot.active {
            width: 22px;
            border-radius: 10px;
            background: #00b207;
            box-shadow: 0 2px 8px rgba(0, 178, 7, 0.6);
        }
        /* Features Bar */
        .features-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            background: #ffffff;
            border: 1px solid #e6e6e6;
            border-radius: 12px;
            padding: 24px 30px;
            margin-bottom: 50px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
        }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 16px;
            opacity: 1 !important;
            transform: none !important;
        }
        .feature-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #eef8f1;
            color: #00b207;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .feature-content h4 {
            font-size: 15px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 3px;
        }
        .feature-content p {
            font-size: 13px;
            color: #777777;
            margin: 0;
        }

        /* Section Titles */
        .section-title { text-align: center; font-size: 32px; font-weight: 700; margin-bottom: 36px; color: #1a1a1a; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; }
        .section-header h2, .section-title-left { font-size: 26px; font-weight: 700; color: #1a1a1a; }
        .view-all { color: #00b207; font-weight: 600; font-size: 14.5px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: color 0.2s ease; }
        .view-all:hover { color: #009906; text-decoration: underline; }
        
        .title-line { display: flex; justify-content: center; gap: 4px; margin-top: 10px; }
        .title-line span { height: 3px; background: #00b207; border-radius: 2px; }
        .title-line span:nth-child(1) { width: 12px; opacity: 0.3; }
        .title-line span:nth-child(2) { width: 32px; }
        .title-line span:nth-child(3) { width: 12px; opacity: 0.3; }

        /* Product Grid (4 Columns) */
        .product-grid {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 20px !important;
            margin-bottom: 60px !important;
        }
        .product-card {
            border: 1px solid #e6e6e6 !important;
            border-radius: 10px !important;
            overflow: hidden !important;
            position: relative !important;
            transition: all 0.3s ease !important;
            background: #ffffff !important;
            padding: 16px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02) !important;
        }
        .product-card:hover {
            border-color: #00b207 !important;
            box-shadow: 0 8px 24px rgba(0, 178, 7, 0.12) !important;
            transform: translateY(-3px) !important;
        }
        .product-image {
            height: 200px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: #fafafa;
            border-radius: 6px;
            overflow: hidden;
        }
        .product-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        .sale-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #ea4b48;
            color: #ffffff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            z-index: 2;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sale-badge.out-of-stock {
            background-color: #6c757d;
        }
        /* Solusi 3: Grayscale out of stock */
        .product-card.is-out-of-stock .product-image img,
        .small-product-card.is-out-of-stock .sp-image img {
            filter: grayscale(85%) opacity(0.72);
            transition: filter 0.3s ease, opacity 0.3s ease;
        }
        .product-card.is-out-of-stock:hover .product-image img,
        .small-product-card.is-out-of-stock:hover .sp-image img {
            filter: grayscale(60%) opacity(0.85);
        }
        /* Solusi 1: Eyebrow pills and inline status */
        .product-meta-eyebrow {
            display: flex;
            align-items: center;
            gap: 4.5px;
            margin-bottom: 3.5px;
            flex-wrap: wrap;
        }
        .eyebrow-signature,
        .signature-badge.eyebrow-signature {
            position: static;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            color: #d97706;
            background: rgba(217, 119, 6, 0.12);
            padding: 1.5px 5.5px;
            border-radius: 3px;
            box-shadow: none;
            line-height: 1.2;
        }
        .eyebrow-signature i,
        .signature-badge.eyebrow-signature i {
            font-size: 7.5px;
            color: #d97706;
        }
        .eyebrow-discount {
            display: inline-flex;
            align-items: center;
            font-size: 8.5px;
            font-weight: 700;
            color: #ea4b48;
            background: rgba(234, 75, 72, 0.1);
            padding: 1.5px 5px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.2;
        }
        .stock-status-inline {
            font-size: 9px;
            font-weight: 600;
            color: #6c757d;
            background: #f1f3f5;
            padding: 1px 5px;
            border-radius: 2.5px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.3;
        }
        .signature-badge:not(.eyebrow-signature) {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #d97706;
            color: #ffffff;
            font-size: 10px;
            font-weight: 600;
            padding: 3px 7px;
            border-radius: 3px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 3.5px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .signature-badge:not(.eyebrow-signature) i {
            font-size: 8.5px;
        }
        .sp-image .signature-badge,
        .sp-image .sale-badge {
            display: none !important;
        }
        .product-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 2;
        }
        .product-card:hover .product-actions {
            opacity: 1;
        }
        .action-btn {
            width: 36px;
            height: 36px;
            background: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a1a1a;
            cursor: pointer;
            border: 1px solid #e6e6e6;
            transition: all 0.25s ease;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .action-btn:hover {
            background: #00b207;
            color: #ffffff;
            border-color: #00b207;
            transform: scale(1.08);
        }
        
        .product-info {
            position: relative;
            padding-right: 45px;
        }
        .product-name {
            font-size: 15px;
            font-weight: 500;
            color: #2b2b2b;
            margin-bottom: 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .product-name:hover {
            color: #00b207;
        }
        .product-price {
            font-size: 16.5px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .product-price .old {
            color: #999999;
            text-decoration: line-through;
            font-size: 13.5px;
            font-weight: 400;
        }
        .product-rating {
            color: #ff8a00;
            font-size: 12px;
            display: flex;
            gap: 2px;
        }
        .cart-btn {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 40px;
            height: 40px;
            background-color: #f2f2f2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a1a1a;
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .cart-btn:hover {
            background-color: #00b207;
            color: #ffffff;
            transform: scale(1.08);
        }
        .cart-btn.disabled {
            background-color: #e9ecef;
            color: #adb5bd;
            cursor: not-allowed;
        }

        /* Four Columns Section (Enlarged Banner Column) */
        .four-cols {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) minmax(0, 1.35fr) !important;
            gap: 22px !important;
            margin-bottom: 60px !important;
            align-items: stretch !important;
        }
        .four-cols > div:last-child {
            display: flex !important;
            flex-direction: column !important;
            height: 100% !important;
        }
        .col-title {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 2px solid #eef8f1;
        }
        .small-product-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .small-product-card {
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid #e6e6e6;
            border-radius: 10px;
            padding: 10px 12px;
            height: 108px;
            min-height: 108px;
            max-height: 108px;
            box-sizing: border-box;
            transition: all 0.3s ease;
            background: #ffffff;
            cursor: pointer;
            position: relative;
        }
        .small-product-card:hover {
            border-color: #00b207;
            box-shadow: 0 6px 18px rgba(0, 178, 7, 0.1);
            transform: translateY(-2px);
        }
        .small-product-card.is-signature:hover {
            border-color: #d97706;
            box-shadow: 0 6px 18px rgba(217, 119, 6, 0.18);
        }
        .sp-image {
            width: 76px;
            height: 76px;
            flex-shrink: 0;
            background: #f9f9f9;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 4px;
            position: relative;
            overflow: hidden;
        }
        .sp-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
            border-radius: 6px;
        }
        .sp-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
        }
        .sp-name {
            font-size: 13.5px;
            font-weight: 500;
            color: #2b2b2b;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: color 0.2s ease;
            line-height: 1.3;
        }
        .sp-name:hover {
            color: #00b207;
        }
        .sp-bottom-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .sp-price-box {
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.15;
            min-width: 0;
        }
        .sp-price-current {
            font-size: 13.5px;
            font-weight: 700;
            color: #1a1a1a;
            white-space: nowrap;
        }
        .sp-price-old {
            font-size: 11px;
            color: #94a3b8;
            text-decoration: line-through;
            white-space: nowrap;
            margin-top: 1px;
        }
        .sp-price {
            font-size: 13.5px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0;
            white-space: nowrap;
        }
        .sp-rating {
            color: #ff8a00;
            font-size: 11px;
            display: flex;
            gap: 2px;
        }
        .sp-actions {
            display: flex;
            gap: 5px;
            flex-shrink: 0;
        }
        .sp-action-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1px solid #e6e6e6;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.25s ease;
            color: #1a1a1a;
        }
        .sp-action-btn:hover {
            background-color: #00b207 !important;
            color: #ffffff !important;
            border-color: #00b207 !important;
            transform: scale(1.08);
        }

        /* Promo Banner (4th Column) */
        /* ==========================================================================
           Ecobazar Figma Replica: Promotional / Discount Banner (Enlarged & Enhanced)
           ========================================================================== */
        .promo-banner-figma,
        .promo-banner {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            width: 100%;
            height: 100%;
            min-height: 420px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            text-align: center;
            padding: 28px 20px 20px 20px;
            background: #ffffff;
            border: 1.5px solid #dbe8dd;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04), 0 1px 4px rgba(0, 178, 7, 0.06);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            box-sizing: border-box;
            flex: 1;
        }
        /* Lapisan Gradasi Radial Spotlight (Melindungi Keterbacaan Teks Atas & Memancarkan Gambar Penuh) */
        .promo-banner-figma::before,
        .promo-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(
                ellipse 150% 115% at 50% 12%,
                rgba(255, 255, 255, 0.98) 0%,
                rgba(255, 255, 255, 0.92) 34%,
                rgba(255, 255, 255, 0.65) 60%,
                rgba(255, 255, 255, 0.15) 82%,
                rgba(255, 255, 255, 0) 100%
            );
            z-index: 2;
            pointer-events: none;
        }
        .promo-banner-figma:hover,
        .promo-banner:hover {
            transform: translateY(-4px);
            border-color: #b9dac0;
            box-shadow: 0 16px 36px rgba(0, 178, 7, 0.12), 0 4px 12px rgba(0, 0, 0, 0.04);
        }
        .promo-figma-header {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            z-index: 3;
        }
        .promo-figma-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 138, 0, 0.14);
            color: #ff8a00;
            border: 1px solid rgba(255, 138, 0, 0.32);
            padding: 5px 14px;
            border-radius: 30px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 8px;
            box-shadow: 0 2px 6px rgba(255, 138, 0, 0.08);
        }
        .promo-figma-badge-pill i {
            font-size: 11px;
        }
        .promo-figma-title {
            font-size: 42px;
            font-weight: 900;
            color: #00b207;
            line-height: 1.05;
            margin-bottom: 6px;
            letter-spacing: -1px;
            text-shadow: 0 2px 8px rgba(0, 178, 7, 0.10);
        }
        .promo-figma-tagline {
            font-size: 12.5px;
            color: #555555;
            font-weight: 500;
            margin-bottom: 16px;
            line-height: 1.35;
            max-width: 260px;
        }
        .promo-figma-btn,
        .promo-btn {
            background: linear-gradient(135deg, #00b207 0%, #009906 100%);
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 700;
            padding: 11px 28px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 6px 18px rgba(0, 178, 7, 0.32);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            border: none;
        }
        .promo-figma-btn:hover,
        .promo-btn:hover {
            background: linear-gradient(135deg, #009e06 0%, #008505 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(0, 178, 7, 0.42);
            color: #ffffff !important;
        }
        .promo-figma-btn i,
        .promo-btn i {
            transition: transform 0.25s ease;
        }
        .promo-figma-btn:hover i,
        .promo-btn:hover i {
            transform: translateX(5px);
        }
        /* Kontainer Gambar Memenuhi Seluruh Card (Kiri, Kanan, Atas, Bawah) */
        .promo-figma-image-wrap {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            z-index: 1;
            border-radius: 20px;
        }
        .promo-floating-tag {
            position: absolute;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            top: auto;
            z-index: 3;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 178, 7, 0.25);
            color: #00b207;
            font-size: 11px;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 20px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            pointer-events: none;
        }
        /* Gambar Cover Penuh 100% Edge-to-Edge */
        .promo-figma-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            max-height: none;
            object-fit: cover;
            object-position: center bottom;
            display: block;
            filter: saturate(1.05) contrast(1.02);
            transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .promo-banner-figma:hover .promo-figma-img,
        .promo-banner:hover .promo-figma-img {
            transform: scale(1.05);
        }
        /* Top Category Grid */
        .category-grid {
            display: grid !important;
            grid-template-columns: repeat(5, 1fr) !important;
            gap: 20px !important;
            margin-bottom: 60px !important;
        }
        .category-card {
            border: 1px solid #e6e6e6 !important;
            border-radius: 12px !important;
            padding: 24px 15px !important;
            text-align: center !important;
            transition: all 0.3s ease !important;
            cursor: pointer !important;
            background: #ffffff !important;
        }
        .category-card:hover {
            border-color: #00b207 !important;
            box-shadow: 0 8px 24px rgba(0, 178, 7, 0.12) !important;
            transform: translateY(-4px) !important;
        }
        .cat-icon {
            height: 64px;
            margin-bottom: 14px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .cat-icon img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            border-radius: 8px;
        }
        .cat-name {
            font-size: 15.5px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .cat-count {
            font-size: 13px;
            color: #888888;
        }

        /* Scroll-Reveal Animations for Cards (Figma Motion Standards) */
        .scroll-reveal,
        .safe-reveal {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.65s cubic-bezier(0.16, 1, 0.3, 1),
                        transform 0.65s cubic-bezier(0.16, 1, 0.3, 1);
            transition-delay: var(--reveal-delay, 0s);
            will-change: transform, opacity;
        }

        .js-animate .scroll-reveal:not(.is-visible),
        .js-animate .safe-reveal:not(.is-visible) {
            opacity: 0;
            transform: translateY(32px) scale(0.97);
            pointer-events: none;
        }

        .scroll-reveal.is-visible,
        .safe-reveal.is-visible {
            opacity: 1 !important;
            transform: translateY(0) scale(1) !important;
            pointer-events: auto;
        }

        @media (prefers-reduced-motion: reduce) {
            .scroll-reveal,
            .safe-reveal {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
            }
        }

        /* Responsive overrides */
        @media (max-width: 1100px) {
            .hero-section { min-height: 460px; }
            .hero-image,
            .hero-showcase-bg { width: 50% !important; min-height: 460px !important; }
            .hero-text,
            .hero-content { padding: 45px 30px 45px 45px !important; max-width: 520px !important; }
            .hero-text h1 { font-size: 38px; }
            .product-grid { grid-template-columns: repeat(3, 1fr) !important; }
            .four-cols { grid-template-columns: repeat(2, 1fr) !important; }
            .category-grid { grid-template-columns: repeat(3, 1fr) !important; }
        }
        @media (max-width: 768px) {
            .hero-slider-wrapper {
                margin-top: 16px !important;
                margin-bottom: 24px !important;
            }
            .hero-section {
                display: flex !important;
                flex-direction: column !important;
                padding: 0 !important;
                text-align: center !important;
                min-height: auto !important;
            }
            .hero-section::before {
                display: none !important;
            }
            .hero-image,
            .hero-showcase-bg {
                position: relative !important;
                top: auto !important;
                right: auto !important;
                bottom: auto !important;
                width: 100% !important;
                height: 210px !important;
                min-height: 210px !important;
                padding: 0 !important;
                margin-bottom: 0 !important;
                z-index: 1 !important;
            }
            .hero-image-frame {
                position: relative !important;
                width: 100% !important;
                height: 100% !important;
            }
            .hero-image-frame img {
                position: absolute !important;
                inset: 0 !important;
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
                object-position: center !important;
            }
            .hero-text,
            .hero-content {
                position: relative !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 24px 20px 30px !important;
                align-items: center !important;
                text-align: center !important;
                z-index: 2 !important;
            }
            .hero-badge-pill { font-size: 11.5px !important; padding: 5px 14px !important; margin-bottom: 10px !important; }
            .hero-text h1 { font-size: 26px !important; line-height: 1.22 !important; margin-bottom: 8px !important; letter-spacing: -0.5px !important; max-width: 100% !important; }
            .hero-sale-callout { font-size: 17px !important; margin-bottom: 6px !important; justify-content: center !important; }
            .hero-text p.desc { font-size: 13px !important; line-height: 1.45 !important; margin-top: 4px !important; margin-bottom: 18px !important; margin-left: auto !important; margin-right: auto !important; max-width: 320px !important; }
            .hero-btn { padding: 12px 28px !important; font-size: 13.5px !important; }
            .hero-slider-arrow {
                opacity: 0.85 !important;
                pointer-events: auto !important;
                width: 38px !important;
                height: 38px !important;
                font-size: 13px !important;
            }
            .hero-slider-prev { left: 10px !important; }
            .hero-slider-next { right: 10px !important; }
            .hero-slider-dots {
                left: 50% !important;
                transform: translateX(-50%) !important;
                bottom: 12px !important;
                padding: 4px 10px !important;
            }
            .hero-dot { width: 7px !important; height: 7px !important; }
            .hero-dot.active { width: 18px !important; }
            .features-bar { grid-template-columns: repeat(2, 1fr); gap: 16px; padding: 20px; }
            .product-grid { grid-template-columns: repeat(2, 1fr) !important; }
            .four-cols { grid-template-columns: 1fr !important; }
            .category-grid { grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media (max-width: 500px) {
            .features-bar { grid-template-columns: 1fr; }
            .product-grid { grid-template-columns: 1fr !important; }
        }
    </style>
@endsection

@section('content')
    <div class="home-container">

        <!-- Hero Section / Dynamic Banner Slider -->
        <div class="hero-slider-wrapper position-relative overflow-hidden mb-5" style="border-radius: 24px;">
            <div class="hero-slides-container">
                @forelse($heroBanners as $index => $banner)
                    @php
                        $subjudul = $banner->subjudul;
                        $badgeText = 'Zyh Corner';
                        $calloutPrefix = null;
                        $calloutHighlight = null;
                        $descText = null;

                        if ($subjudul) {
                            if (str_contains($subjudul, '-')) {
                                $parts = explode('-', $subjudul, 2);
                                $badgeText = trim($parts[0]);
                                $calloutCandidate = trim($parts[1]);
                            } else {
                                $calloutCandidate = trim($subjudul);
                            }

                            if (preg_match('/^(.*?)(\d+%\s*OFF|\d+%\s*diskon|potongan\s*\d+%|diskon\s*\d+%.*|\d+%.*)$/i', $calloutCandidate, $matches)) {
                                $calloutPrefix = trim($matches[1]) ?: null;
                                $calloutHighlight = trim($matches[2]);
                                $descText = 'Dipanggang segar setiap pagi dengan bahan alami pilihan terbaik untuk momen spesial Anda.';
                            } else {
                                $descText = $calloutCandidate;
                            }
                        }
                    @endphp
                    <div class="hero-slide-item {{ $loop->first ? 'active' : '' }}" data-slide="{{ $index }}">
                        <div class="hero-section">
                            <div class="hero-image hero-showcase-bg">
                                <div class="hero-image-frame">
                                    @if ($banner->gambar_banner && Storage::disk('public')->exists($banner->gambar_banner))
                                        <img src="{{ asset('storage/' . $banner->gambar_banner) }}" alt="{{ $banner->judul_banner }}">
                                    @else
                                        <img src="{{ asset('storage/banners/JMrYmKE9l5JHfJWWfbf5k0zNjjGZQZzNDKVWsG9N.png') }}" alt="{{ $banner->judul_banner }}">
                                    @endif
                                </div>
                            </div>
                            <div class="hero-text hero-content">
                                <span class="hero-badge-pill">
                                    <i class="fa-solid fa-sparkles text-success"></i> {{ $badgeText }}
                                </span>
                                <h1>{{ $banner->judul_banner }}</h1>
                                @if($calloutHighlight)
                                    <div class="hero-sale-callout">
                                        @if($calloutPrefix)
                                            <span>{{ $calloutPrefix }}</span>
                                        @endif
                                        <span class="hero-sale-highlight">{{ $calloutHighlight }}</span>
                                    </div>
                                @endif
                                @if($descText)
                                    <p class="desc">{{ $descText }}</p>
                                @endif
                                <a href="{{ $banner->link_url ? url($banner->link_url) : route('shop') }}" class="hero-btn">
                                    Shop Now <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="hero-slide-item active" data-slide="0">
                        <div class="hero-section">
                            <div class="hero-image hero-showcase-bg">
                                <div class="hero-image-frame">
                                    <img src="{{ asset('storage/banners/JMrYmKE9l5JHfJWWfbf5k0zNjjGZQZzNDKVWsG9N.png') }}" alt="Zyh Corner Pastry">
                                </div>
                            </div>
                            <div class="hero-text hero-content">
                                <span class="hero-badge-pill"><i class="fa-solid fa-sparkles text-success"></i> Zyh Corner</span>
                                <h1>Delicious & Fresh Cakes and Desserts</h1>
                                <p class="desc">Nikmati aneka pilihan pastry dan cake segar kualitas terbaik dari Zyh Corner setiap hari.</p>
                                <a href="{{ route('shop') }}" class="hero-btn">Shop Now <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($heroBanners->count() > 1)
                <!-- Navigation Arrows -->
                <button class="hero-slider-arrow hero-slider-prev" aria-label="Previous Slide">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button class="hero-slider-arrow hero-slider-next" aria-label="Next Slide">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

                <!-- Indicator Dots -->
                <div class="hero-slider-dots">
                    @foreach($heroBanners as $index => $b)
                        <span class="hero-dot {{ $loop->first ? 'active' : '' }}" data-slide-to="{{ $index }}"></span>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Informasi Tambahan (Value Propositions / Features Bar) -->
        <div class="features-bar">
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div class="feature-content">
                    <h4>Free Shipping</h4>
                    <p>Free shipping on all your order</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <div class="feature-content">
                    <h4>Customer Support 24/7</h4>
                    <p>Instant access to Support</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="feature-content">
                    <h4>100% Secure Payment</h4>
                    <p>We ensure your money is safe</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrapper">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <div class="feature-content">
                    <h4>Freshly Baked Daily</h4>
                    <p>100% Guaranteed Freshness</p>
                </div>
            </div>
        </div>

        <!-- Featured Products -->
        <div class="section-title">
            Featured Products
            <div class="title-line">
                <span></span><span></span><span></span>
            </div>
        </div>

        <div class="product-grid">
            @forelse ($featuredProducts as $product)
                <div class="product-card {{ $product->stok == 0 ? 'is-out-of-stock' : '' }} {{ $product->is_signature ? 'is-signature' : '' }} scroll-reveal" style="--reveal-delay: {{ $loop->index * 0.06 }}s; cursor: pointer;"
                    onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                    <div class="product-image">
                        @if ($product->has_diskon)
                            <div class="sale-badge">{{ $product->diskon_badge }}</div>
                        @endif
                        <div class="product-actions">
                            <div class="action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)"
                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                data-price="{{ $product->harga_diskon }}"
                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                data-desc="{{ $product->deskripsi }}"
                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                <i class="fa-regular fa-eye"></i>
                            </div>
                        </div>
                        <a href="{{ route('product.detail', $product->id_produk) }}" style="display:block;" onclick="event.stopPropagation();">
                            @if ($product->foto_1)
                                <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                            @endif
                        </a>
                    </div>
                    <div class="product-info">
                        @if ($product->is_signature)
                            <div class="product-meta-eyebrow">
                                <span class="signature-badge eyebrow-signature"><i class="fa-solid fa-crown"></i> Signature</span>
                            </div>
                        @endif
                        <a href="{{ route('product.detail', $product->id_produk) }}"
                            class="product-name" onclick="event.stopPropagation();">{{ $product->nama_produk }}</a>
                        <div class="product-price d-flex align-items-center justify-content-between">
                            <div>
                                @if ($product->has_diskon)
                                    Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}
                                    <span class="old">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                @else
                                    Rp. {{ number_format($product->harga, 0, ',', '.') }}
                                @endif
                            </div>
                            @if ($product->stok == 0)
                                <span class="stock-status-inline">Habis</span>
                            @endif
                        </div>
                        @if ($product->stok > 0)
                            <div class="cart-btn" title="Lihat Preview & Pesan"
                                onclick="event.stopPropagation(); openProductModal(this);"
                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                data-price="{{ $product->harga_diskon }}"
                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                data-desc="{{ $product->deskripsi }}"
                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                <i class="fa-solid fa-bag-shopping"></i>
                            </div>
                        @else
                            <div class="cart-btn disabled" title="Stok Habis" onclick="event.stopPropagation();">
                                <i class="fa-solid fa-ban"></i>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                    Belum ada produk unggulan yang tersedia saat ini.
                </div>
            @endforelse
        </div>

        <!-- 4 Columns Section -->
        <div class="four-cols">

            <!-- 1. Hot Deals -->
            <div>
                <h3 class="col-title">Hot Deals</h3>
                <div class="small-product-list">
                    @forelse ($hotDeals as $product)
                        <div class="small-product-card {{ $product->stok == 0 ? 'is-out-of-stock' : '' }} {{ $product->is_signature ? 'is-signature' : '' }} scroll-reveal" style="--reveal-delay: {{ $loop->index * 0.05 }}s;"
                            onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                            <div class="sp-image">
                                @if ($product->foto_1)
                                    <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                                @endif
                            </div>
                            <div class="sp-info">
                                @if ($product->is_signature || $product->has_diskon)
                                    <div class="product-meta-eyebrow">
                                        @if ($product->is_signature)
                                            <span class="signature-badge eyebrow-signature"><i class="fa-solid fa-crown"></i> Signature</span>
                                        @endif
                                        @if ($product->has_diskon)
                                            <span class="eyebrow-discount">{{ $product->diskon_badge }}</span>
                                        @endif
                                    </div>
                                @endif
                                <div class="sp-name">{{ $product->nama_produk }}</div>

                                <div class="sp-bottom-row">
                                    <div class="sp-price-box">
                                        @if ($product->has_diskon)
                                            <span class="sp-price-current">Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}</span>
                                            <span class="sp-price-old">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                        @else
                                            <span class="sp-price-current">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                        @endif
                                        @if ($product->stok == 0)
                                            <div class="mt-1"><span class="stock-status-inline">Habis</span></div>
                                        @endif
                                    </div>
                                    <div class="sp-actions">
                                        <button type="button" class="sp-action-btn" title="Quick View"
                                            onclick="event.stopPropagation(); openProductModal(this)"
                                            data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                            data-price="{{ $product->harga_diskon }}"
                                            data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                            data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                            data-desc="{{ $product->deskripsi }}"
                                            data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                            data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                            data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                            data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                            data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                            data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                            data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                            data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                            <i class="fa-regular fa-eye" style="font-size: 11px;"></i>
                                        </button>
                                        @if ($product->stok > 0)
                                            <button type="button" class="sp-action-btn" title="Lihat Preview & Pesan"
                                                onclick="event.stopPropagation(); openProductModal(this);"
                                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                                data-price="{{ $product->harga_diskon }}"
                                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                                data-desc="{{ $product->deskripsi }}"
                                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                                <i class="fa-solid fa-bag-shopping" style="font-size: 11px;"></i>
                                            </button>
                                        @else
                                            <button type="button" class="sp-action-btn" title="Stok Habis" disabled style="opacity: 0.5; cursor: not-allowed;" onclick="event.stopPropagation();">
                                                <i class="fa-solid fa-ban" style="font-size: 11px;"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="padding: 15px; color: #777; font-size: 13px;">Belum ada produk hot deals.</div>
                    @endforelse
                </div>
            </div>

            <!-- 2. Best Seller -->
            <div>
                <h3 class="col-title">Best Seller</h3>
                <div class="small-product-list">
                    @forelse ($bestSellers as $product)
                        <div class="small-product-card {{ $product->stok == 0 ? 'is-out-of-stock' : '' }} {{ $product->is_signature ? 'is-signature' : '' }} scroll-reveal" style="--reveal-delay: {{ $loop->index * 0.05 }}s;"
                            onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                            <div class="sp-image">
                                @if ($product->foto_1)
                                    <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                                @endif
                            </div>
                            <div class="sp-info">
                                @if ($product->is_signature || $product->has_diskon)
                                    <div class="product-meta-eyebrow">
                                        @if ($product->is_signature)
                                            <span class="signature-badge eyebrow-signature"><i class="fa-solid fa-crown"></i> Signature</span>
                                        @endif
                                        @if ($product->has_diskon)
                                            <span class="eyebrow-discount">{{ $product->diskon_badge }}</span>
                                        @endif
                                    </div>
                                @endif
                                <div class="sp-name">{{ $product->nama_produk }}</div>

                                <div class="sp-bottom-row">
                                    <div class="sp-price-box">
                                        @if ($product->has_diskon)
                                            <span class="sp-price-current">Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}</span>
                                            <span class="sp-price-old">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                        @else
                                            <span class="sp-price-current">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                        @endif
                                        @if ($product->stok == 0)
                                            <div class="mt-1"><span class="stock-status-inline">Habis</span></div>
                                        @endif
                                    </div>
                                    <div class="sp-actions">
                                        <button type="button" class="sp-action-btn" title="Quick View"
                                            onclick="event.stopPropagation(); openProductModal(this)"
                                            data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                            data-price="{{ $product->harga_diskon }}"
                                            data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                            data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                            data-desc="{{ $product->deskripsi }}"
                                            data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                            data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                            data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                            data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                            data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                            data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                            data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                            data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                            <i class="fa-regular fa-eye" style="font-size: 11px;"></i>
                                        </button>
                                        @if ($product->stok > 0)
                                            <button type="button" class="sp-action-btn" title="Lihat Preview & Pesan"
                                                onclick="event.stopPropagation(); openProductModal(this);"
                                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                                data-price="{{ $product->harga_diskon }}"
                                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                                data-desc="{{ $product->deskripsi }}"
                                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                                <i class="fa-solid fa-bag-shopping" style="font-size: 11px;"></i>
                                            </button>
                                        @else
                                            <button type="button" class="sp-action-btn" title="Stok Habis" disabled style="opacity: 0.5; cursor: not-allowed;" onclick="event.stopPropagation();">
                                                <i class="fa-solid fa-ban" style="font-size: 11px;"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="padding: 15px; color: #777; font-size: 13px;">Belum ada produk best seller.</div>
                    @endforelse
                </div>
            </div>

            <!-- 3. Zyh Signatures -->
            <div>
                <h3 class="col-title d-flex align-items-center justify-content-between">
                    <span>Zyh Signatures</span>
                    <span class="badge" style="background: rgba(217, 119, 6, 0.12); color: #d97706; font-size: 10px; font-weight: 600; padding: 2.5px 7px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.3px;">
                        <i class="fa-solid fa-crown me-1" style="font-size: 9px;"></i> Mahakarya
                    </span>
                </h3>
                <div class="small-product-list">
                    @forelse (($signatureProducts ?? $topRated) as $product)
                        <div class="small-product-card {{ $product->stok == 0 ? 'is-out-of-stock' : '' }} {{ $product->is_signature ? 'is-signature' : '' }} scroll-reveal" style="--reveal-delay: {{ $loop->index * 0.05 }}s;"
                            onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                            <div class="sp-image">
                                @if ($product->foto_1)
                                    <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                                @endif
                            </div>
                            <div class="sp-info">
                                @if ($product->is_signature || $product->has_diskon)
                                    <div class="product-meta-eyebrow">
                                        @if ($product->is_signature)
                                            <span class="signature-badge eyebrow-signature"><i class="fa-solid fa-crown"></i> Signature</span>
                                        @endif
                                        @if ($product->has_diskon)
                                            <span class="eyebrow-discount">{{ $product->diskon_badge }}</span>
                                        @endif
                                    </div>
                                @endif
                                <div class="sp-name">{{ $product->nama_produk }}</div>

                                <div class="sp-bottom-row">
                                    <div class="sp-price-box">
                                        @if ($product->has_diskon)
                                            <span class="sp-price-current">Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}</span>
                                            <span class="sp-price-old">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                        @else
                                            <span class="sp-price-current">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                        @endif
                                        @if ($product->stok == 0)
                                            <div class="mt-1"><span class="stock-status-inline">Habis</span></div>
                                        @endif
                                    </div>
                                    <div class="sp-actions">
                                        <button type="button" class="sp-action-btn" title="Quick View"
                                            onclick="event.stopPropagation(); openProductModal(this)"
                                            data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                            data-price="{{ $product->harga_diskon }}"
                                            data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                            data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                            data-desc="{{ $product->deskripsi }}"
                                            data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                            data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                            data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                            data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                            data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                            data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                            data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                            data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                            <i class="fa-regular fa-eye" style="font-size: 11px;"></i>
                                        </button>
                                        @if ($product->stok > 0)
                                            <button type="button" class="sp-action-btn" title="Lihat Preview & Pesan"
                                                onclick="event.stopPropagation(); openProductModal(this);"
                                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                                data-price="{{ $product->harga_diskon }}"
                                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                                data-desc="{{ $product->deskripsi }}"
                                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                                <i class="fa-solid fa-bag-shopping" style="font-size: 11px;"></i>
                                            </button>
                                        @else
                                            <button type="button" class="sp-action-btn" title="Stok Habis" disabled style="opacity: 0.5; cursor: not-allowed;" onclick="event.stopPropagation();">
                                                <i class="fa-solid fa-ban" style="font-size: 11px;"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="padding: 15px; color: #777; font-size: 13px;">Belum ada produk signature.</div>
                    @endforelse
                </div>
            </div>
            <!-- 4. Promotional Banner (Ecobazar Figma Replica) -->
            <div>
                @php
                    $promoSub = 'SUMMER SALE';
                    $promoTitle = '75% OFF';
                    if (isset($popupBanner) && $popupBanner) {
                        if (!empty($popupBanner->judul_banner)) {
                            if (preg_match('/(.*?)\s*(\d+%\s*OFF)/i', $popupBanner->judul_banner, $matches)) {
                                $promoSub = strtoupper(trim($matches[1])) ?: 'SUMMER SALE';
                                $promoTitle = strtoupper(trim($matches[2]));
                            } elseif (preg_match('/(\d+%\s*OFF)/i', $popupBanner->judul_banner, $matches)) {
                                $promoTitle = strtoupper(trim($matches[1]));
                                $promoSub = strtoupper($popupBanner->subjudul ?? 'SUMMER SALE');
                            } else {
                                $promoTitle = $popupBanner->judul_banner;
                                $promoSub = strtoupper($popupBanner->subjudul ?? 'SPECIAL PROMO');
                            }
                        } elseif (!empty($popupBanner->subjudul)) {
                            $promoSub = strtoupper($popupBanner->subjudul);
                        }
                    }
                @endphp
                <div class="promo-banner-figma">
                    <!-- Layer 1: Background Edge-to-Edge Imagery Area -->
                    <div class="promo-figma-image-wrap">
                        @if(isset($popupBanner) && $popupBanner && $popupBanner->gambar_banner && Storage::disk('public')->exists($popupBanner->gambar_banner))
                            <img src="{{ asset('storage/' . $popupBanner->gambar_banner) }}" alt="{{ $popupBanner->judul_banner ?? 'Summer Sale' }}" class="promo-figma-img">
                        @else
                            <img src="{{ asset('storage/banners/SZFG52k6XY9FraW8KrM9ZT1zpBSrurmlFdXxV2NI.png') }}" alt="Summer Sale" class="promo-figma-img">
                        @endif
                    </div>

                    <!-- Layer 3: Top Content Area (Badge, Title, Tagline, CTA Button) -->
                    <div class="promo-figma-header">
                        <span class="promo-figma-badge-pill">
                            <i class="fa-solid fa-sparkles"></i> {{ $promoSub }}
                        </span>
                        <h2 class="promo-figma-title">{{ $promoTitle }}</h2>
                        <p class="promo-figma-tagline">Aneka Pastry & Cake Pilihan Terbaik</p>
                        <a href="{{ isset($popupBanner) && $popupBanner && $popupBanner->link_url ? $popupBanner->link_url : route('shop') }}" class="promo-figma-btn">
                            Shop Now <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Layer 3: Floating Tag at Bottom -->
                    <div class="promo-floating-tag">
                        <i class="fa-regular fa-clock"></i> Limited Time Offer
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Category -->
        <div class="section-title">
            Top Category
            <div class="title-line">
                <span></span><span></span><span></span>
            </div>
        </div>

        <div class="category-grid">
            @forelse ($categories as $kategori)
                <div class="category-card scroll-reveal" style="--reveal-delay: {{ $loop->index * 0.05 }}s;" onclick="window.location.href='{{ route('shop', ['category' => $kategori->id_kategori]) }}'">
                    <div class="cat-icon">
                        @if ($kategori->gambar_kategori && Storage::disk('public')->exists($kategori->gambar_kategori))
                            <img src="{{ asset('storage/' . $kategori->gambar_kategori) }}" alt="{{ $kategori->nama_kategori }}">
                        @else
                            <i class="fa-solid fa-cake-candles" style="font-size: 38px; color: #00b207;"></i>
                        @endif
                    </div>
                    <div class="cat-name">{{ $kategori->nama_kategori }}</div>
                    <div class="cat-count">{{ $kategori->produks_count }} Products</div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 25px; color: #777;">
                    Belum ada kategori produk tersedia.
                </div>
            @endforelse
        </div>

        <!-- Newest Products -->
        <div class="section-header">
            <div class="section-title-left" style="margin-bottom: 0;">
                Newest Products
                <div class="title-line" style="justify-content: flex-start;">
                    <span></span><span></span><span></span>
                </div>
            </div>
            <a href="{{ route('shop') }}" class="view-all">View All <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="product-grid">
            @forelse ($newestProducts as $product)
                <div class="product-card {{ $product->stok == 0 ? 'is-out-of-stock' : '' }} {{ $product->is_signature ? 'is-signature' : '' }} scroll-reveal" style="--reveal-delay: {{ $loop->index * 0.06 }}s; cursor: pointer;"
                    onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                    <div class="product-image">
                        @if ($product->has_diskon)
                            <div class="sale-badge">{{ $product->diskon_badge }}</div>
                        @endif
                        <div class="product-actions">
                            <div class="action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)"
                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                data-price="{{ $product->harga_diskon }}"
                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                data-desc="{{ $product->deskripsi }}"
                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                <i class="fa-regular fa-eye"></i>
                            </div>
                        </div>
                        <a href="{{ route('product.detail', $product->id_produk) }}" style="display:block;" onclick="event.stopPropagation();">
                            @if ($product->foto_1)
                                <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                            @endif
                        </a>
                    </div>
                    <div class="product-info">
                        @if ($product->is_signature)
                            <div class="product-meta-eyebrow">
                                <span class="signature-badge eyebrow-signature"><i class="fa-solid fa-crown"></i> Signature</span>
                            </div>
                        @endif
                        <a href="{{ route('product.detail', $product->id_produk) }}"
                            class="product-name" onclick="event.stopPropagation();">{{ $product->nama_produk }}</a>
                        <div class="product-price d-flex align-items-center justify-content-between">
                            <div>
                                @if ($product->has_diskon)
                                    Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}
                                    <span class="old">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                                @else
                                    Rp. {{ number_format($product->harga, 0, ',', '.') }}
                                @endif
                            </div>
                            @if ($product->stok == 0)
                                <span class="stock-status-inline">Habis</span>
                            @endif
                        </div>

                        @if ($product->stok > 0)
                            <div class="cart-btn" title="Lihat Preview & Pesan"
                                onclick="event.stopPropagation(); openProductModal(this);"
                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                data-price="{{ $product->harga_diskon }}"
                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                data-desc="{{ $product->deskripsi }}"
                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                <i class="fa-solid fa-bag-shopping"></i>
                            </div>
                        @else
                            <div class="cart-btn disabled" title="Stok Habis" onclick="event.stopPropagation();">
                                <i class="fa-solid fa-ban"></i>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                    Belum ada produk terbaru saat ini.
                </div>
            @endforelse
        </div>

    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/home.js') }}?v={{ file_exists(public_path('js/home.js')) ? filemtime(public_path('js/home.js')) : time() }}"></script>
@endsection
