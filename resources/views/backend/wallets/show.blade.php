@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Wallet Details')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.wallets.index') }}" class="btn btn-circle btn-info">
                <span>{{translate('Back to Wallets')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Customer Information')}}</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted">{{translate('Name')}}</div>
                    <div class="fw-600">{{ $user->name }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted">{{translate('Email')}}</div>
                    <div class="fw-600">{{ $user->email }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted">{{translate('Phone')}}</div>
                    <div class="fw-600">{{ $user->phone }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted">{{translate('Current Balance')}}</div>
                    <div class="fw-600 fs-20 text-success">{{ single_price($user->balance ?? 0) }}</div>
                </div>
                <hr>
                <button type="button" onclick="recharge_wallet('{{ $user->id }}', '{{ $user->name }}')" class="btn btn-success btn-block">
                    <i class="las la-plus"></i> {{translate('Recharge Wallet')}}
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Transaction History')}}</h5>
            </div>
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{translate('Date')}}</th>
                            <th>{{translate('Amount')}}</th>
                            <th>{{translate('Type')}}</th>
                            <th>{{translate('Payment Method')}}</th>
                            <th>{{translate('Admin')}}</th>
                            <th>{{translate('Description')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ date('d M Y, h:i A', strtotime($transaction->created_at)) }}</td>
                                <td>
                                    <span class="badge badge-inline badge-{{ $transaction->transaction_type == 'credit' ? 'success' : 'danger' }}">
                                        {{ $transaction->transaction_type == 'credit' ? '+' : '-' }}{{ single_price($transaction->amount) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-inline badge-{{ $transaction->transaction_type == 'credit' ? 'success' : 'danger' }}">
                                        {{ ucfirst($transaction->transaction_type) }}
                                    </span>
                                </td>
                                <td>{{ $transaction->wallet->payment_method ?? 'N/A' }}</td>
                                <td>
                                    @if($transaction->adminUser)
                                        {{ $transaction->adminUser->name }}
                                    @else
                                        <span class="text-muted">{{ translate('System') }}</span>
                                    @endif
                                </td>
                                <td>{{ $transaction->description ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <p class="text-muted my-3">{{translate('No transactions found')}}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="aiz-pagination mt-3">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Wallet Recharge History')}}</h5>
            </div>
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{translate('Date')}}</th>
                            <th>{{translate('Amount')}}</th>
                            <th>{{translate('Payment Method')}}</th>
                            <th>{{translate('Status')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->wallets as $wallet)
                            <tr>
                                <td>{{ date('d M Y, h:i A', strtotime($wallet->created_at)) }}</td>
                                <td>{{ single_price($wallet->amount) }}</td>
                                <td>{{ $wallet->payment_method }}</td>
                                <td>
                                    @if(isset($wallet->approval))
                                        @if($wallet->approval == 1)
                                            <span class="badge badge-inline badge-success">{{translate('Approved')}}</span>
                                        @elseif($wallet->approval == 0)
                                            <span class="badge badge-inline badge-warning">{{translate('Pending')}}</span>
                                        @else
                                            <span class="badge badge-inline badge-danger">{{translate('Rejected')}}</span>
                                        @endif
                                    @else
                                        <span class="badge badge-inline badge-success">{{translate('Completed')}}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    <p class="text-muted my-3">{{translate('No recharge history found')}}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Recharge Wallet Modal -->
<div class="modal fade" id="recharge-wallet-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{translate('Recharge Wallet')}}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="recharge-wallet-form">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{translate('Customer Name')}}</label>
                        <input type="text" class="form-control" id="customer-name" readonly>
                    </div>
                    <div class="form-group">
                        <label>{{translate('Amount')}} <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="{{ translate('Enter amount') }}" required>
                    </div>
                    <div class="form-group">
                        <label>{{translate('Description')}}</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="{{ translate('Optional description for this recharge') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('Recharge')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    function recharge_wallet(userId, userName) {
        $('#customer-name').val(userName);
        $('#recharge-wallet-form').attr('action', '{{ url('admin/wallets') }}/' + userId + '/recharge');
        $('#recharge-wallet-modal').modal('show');
    }
</script>
@endsection
