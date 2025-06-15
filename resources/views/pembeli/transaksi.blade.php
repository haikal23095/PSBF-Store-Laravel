@extends('layouts.app')

@section('content')
<style>
  /* General Layout */
  body {
    background-color: #f8f9fa; /* Moved background color to body */
  }

  .page-container {
    display: flex;
    flex-direction: column; /* Main change: from row to column */
    min-height: 100vh;
  }
  
  /* =================================== */
  /* == NEW NAVBAR STYLES START HERE == */
  /* =================================== */

  .navbar-custom {
    background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
    padding: 1rem 2rem;
    color: white;
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.2);
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    z-index: 1000;
  }

  .navbar-brand-section {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .navbar-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
  }

  .navbar-subtitle {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.875rem;
    margin: 0;
    display: none; /* Hide subtitle on navbar to save space, show on mobile */
  }

  .navbar-nav-custom {
    display: flex;
    gap: 0.5rem;
  }

  .nav-item-custom {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    color: rgba(255, 255, 255, 0.8);
    border-bottom: 3px solid transparent; /* Prepare for active state */
    position: relative;
  }

  .nav-item-custom:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    text-decoration: none;
  }

  .nav-item-custom.active {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    font-weight: 600;
    border-bottom-color: white; /* Active indicator */
  }

  .nav-icon {
    margin-right: 0.75rem;
    font-size: 1.1rem;
  }

  .nav-label {
    font-size: 0.95rem;
  }

  .nav-count {
    background: rgba(0, 0, 0, 0.2);
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    margin-left: 0.75rem;
  }

  /* =============================== */
  /* == NAVBAR STYLES END HERE   == */
  /* =============================== */

  /* Main Content */
  .main-content {
    flex: 1;
    padding: 2.5rem; /* Increased padding for more breathing room */
    background: #ffffff;
  }
  
  .content-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0f2f5;
  }
  
  .content-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1a1a1a;
  }
  
  .content-subtitle {
    color: #6c757d;
    font-size: 1rem;
  }
  
  /* Transaction Cards (No changes needed, keeping original style) */
  .transaction-grid { display: grid; gap: 1.5rem; }
  .transaction-card { background: #ffffff; border-radius: 16px; padding: 2rem; border: 1px solid #e9ecef; box-shadow: 0 2px 12px rgba(0,0,0,0.04); transition: all 0.3s ease; text-decoration: none; color: inherit; position: relative; overflow: hidden; }
  .transaction-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #2196f3, #4caf50, #ff9800, #f44336); }
  .transaction-card:hover { transform: translateY(-4px); box-shadow: 0 8px 28px rgba(0,0,0,0.12); text-decoration: none; color: inherit; }
  .transaction-header { display: flex; align-items: flex-start; margin-bottom: 1.5rem; }
  .transaction-icon { width: 60px; height: 60px; border-radius: 16px; background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%); display: flex; align-items: center; justify-content: center; margin-right: 1.5rem; color: white; font-size: 1.5rem; flex-shrink: 0; }
  .transaction-info { flex: 1; min-width: 0; }
  .transaction-id { font-size: 1.25rem; font-weight: 600; color: #1a1a1a; margin-bottom: 0.5rem; }
  .transaction-date { color: #6c757d; font-size: 0.9rem; display: flex; align-items: center; }
  .transaction-status { margin-left: auto; flex-shrink: 0; }
  .status-badge { padding: 8px 16px; border-radius: 20px; font-weight: 500; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; }
  .status-menunggu_pembayaran { background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); color: white; }
  .status-dikemas { background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%); color: white; }
  .status-dikirim { background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%); color: white; }
  .status-diterima { background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%); color: white; }
  .transaction-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #f0f2f5; }
  .detail-item { text-align: center; }
  .detail-label { display: block; font-size: 0.8rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; font-weight: 500; }
  .detail-value { font-size: 1.1rem; font-weight: 600; color: #1a1a1a; }
  .detail-value.price { color: #2196f3; font-size: 1.2rem; }
  
  /* Empty State & Pagination (No changes needed) */
  .empty-state { text-align: center; padding: 4rem 2rem; background: #ffffff; border-radius: 16px; border: 2px dashed #e9ecef; }
  .empty-icon { font-size: 4rem; color: #dee2e6; margin-bottom: 1.5rem; }
  .empty-title { font-size: 1.5rem; font-weight: 600; color: #6c757d; margin-bottom: 1rem; }
  .empty-text { color: #6c757d; margin-bottom: 2rem; font-size: 1rem; }
  .pagination-wrapper { margin-top: 3rem; padding: 2rem; background: #f8f9fa; border-radius: 12px; text-align: center; }

  /* Mobile Responsive */
  @media (max-width: 992px) { /* Changed breakpoint for better responsive behavior */
    .navbar-custom {
      flex-direction: column;
      align-items: flex-start;
      padding: 1rem;
      gap: 1rem;
    }
    .navbar-brand-section {
      width: 100%;
    }
    .navbar-subtitle {
      display: block; /* Show subtitle on mobile */
    }
    .navbar-nav-custom {
      width: 100%;
      overflow-x: auto;
      padding-bottom: 10px; /* space for scrollbar */
      -ms-overflow-style: none; /* IE and Edge */
      scrollbar-width: none; /* Firefox */
    }
    .navbar-nav-custom::-webkit-scrollbar {
      display: none; /* Hide scrollbar for Chrome, Safari, and Opera */
    }
    .nav-item-custom {
      flex-shrink: 0; /* Prevent items from shrinking */
      min-width: 160px;
    }
    .main-content {
      padding: 1.5rem;
    }
    .content-title {
      font-size: 1.5rem;
    }
    .transaction-card {
      padding: 1.5rem;
    }
    .transaction-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 1rem;
    }
    .transaction-status {
      margin-left: 0;
    }
    .transaction-details {
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
  }
</style>

<div class="page-container">
  {{-- Navbar (Previously Sidebar) --}}
  <nav class="navbar-custom">
    <div class="navbar-brand-section">
      <i class="fas fa-shopping-bag fa-2x"></i>
      <div>
        <h3 class="navbar-title">Transaksi Saya</h3>
        <p class="navbar-subtitle">Kelola dan pantau status transaksi</p>
      </div>
    </div>
    
<nav>
    <div class="navbar-nav-custom">
      @php
        $statuses = [
          'menunggu_pembayaran' => ['label' => 'Menunggu Pembayaran', 'icon' => 'fas fa-clock'],
          'dikemas'               => ['label' => 'Dikemas',               'icon' => 'fas fa-box'],
          'dikirim'               => ['label' => 'Dikirim',               'icon' => 'fas fa-truck'],
          'diterima'              => ['label' => 'Diterima',              'icon' => 'fas fa-check-circle'],
        ];
        // currentStatus didapat dari controller
        // $currentStatus = request('status', 'menunggu_pembayaran'); 
      @endphp

      @foreach ($statuses as $key => $info)
        <a href="{{ route('pembeli.transaksi.index', ['status' => $key]) }}"
           class="nav-item-custom {{ $currentStatus === $key ? 'active' : '' }}">
          <i class="{{ $info['icon'] }} nav-icon"></i>
          <span class="nav-label">{{ $info['label'] }}</span>
          
          {{-- PERUBAHAN DI SINI --}}
          <span class="nav-count">{{ $statusCounts[$key] ?? 0 }}</span>

        </a>
      @endforeach
    </div>
</nav>
  </nav>

  {{-- Main Content --}}
  <div class="main-content">
    <div class="mb-4">
        <a href="{{ route('pembeli.store') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Halaman Utama
        </a>
    </div>
    <div class="content-header">
      <h1 class="content-title">
        {{ $statuses[$currentStatus]['label'] ?? 'Transaksi' }}
      </h1>
      <p class="content-subtitle">
        Menampilkan {{ $transaksis->count() }} dari {{ $transaksis->total() ?? 0 }} transaksi
      </p>
    </div>

    {{-- Transaction List --}}
    @if ($transaksis->count())
      <div class="transaction-grid">
        @foreach ($transaksis as $trx)
          @php
            $statusIcons = [
              'menunggu_pembayaran' => 'fas fa-clock',
              'dikemas'               => 'fas fa-box',
              'dikirim'               => 'fas fa-truck',
              'diterima'              => 'fas fa-check-circle',
            ];
          @endphp
          
          <a href="{{ route('pembeli.transaksi.show', $trx->id) }}" class="transaction-card">
            <div class="transaction-header">
              <div class="transaction-icon">
                <i class="{{ $statusIcons[$trx->status_transaksi] ?? 'fas fa-receipt' }}"></i>
              </div>
              <div class="transaction-info">
                <h3 class="transaction-id">
                  Transaksi #{{ $trx->id }}
                </h3>
                <div class="transaction-date">
                  <i class="fas fa-calendar-alt me-1"></i>
                  {{ $trx->created_at->format('d M Y, H:i') }}
                </div>
              </div>
              <div class="transaction-status">
                <span class="status-badge status-{{ $trx->status_transaksi }}">
                  <i class="{{ $statusIcons[$trx->status_transaksi] ?? 'fas fa-info' }}"></i>
                  {{ str_replace('_', ' ', ucwords($trx->status_transaksi, '_')) }}
                </span>
              </div>
            </div>
            
            <div class="transaction-details">
              <div class="detail-item">
                <span class="detail-label">
                  <i class="fas fa-shopping-cart me-1"></i>
                  Total Item
                </span>
                <div class="detail-value">{{ $trx->detail_transaksis_count ?? 0 }}</div>
              </div>
              <div class="detail-item">
                <span class="detail-label">
                  <i class="fas fa-money-bill-wave me-1"></i>
                  Total Bayar
                </span>
                <div class="detail-value price">Rp {{ number_format($trx->total_bayar ?? 0, 0, ',', '.') }}</div>
              </div>
              <div class="detail-item">
                <span class="detail-label">
                  <i class="fas fa-store me-1"></i>
                  Penjual
                </span>
                <div class="detail-value">{{ $trx->penjual->nama ?? 'Unknown' }}</div>
              </div>
            </div>
          </a>
        @endforeach
      </div>

      {{-- Pagination --}}
      @if ($transaksis->hasPages())
        <div class="pagination-wrapper">
          {{ $transaksis->appends(['status' => request('status')])->links() }}
        </div>
      @endif
    @else
      <div class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-inbox"></i>
        </div>
        <h3 class="empty-title">Tidak Ada Transaksi</h3>
        <p class="empty-text">
          Belum ada transaksi dengan status 
          <strong>{{ str_replace('_', ' ', ucwords($currentStatus, '_')) }}</strong>
        </p>
        <a href="{{ route('pembeli.store') }}" class="btn btn-primary btn-lg">
          <i class="fas fa-shopping-cart me-2"></i>
          Mulai Berbelanja
        </a>
      </div>
    @endif
  </div>
</div>
@endsection