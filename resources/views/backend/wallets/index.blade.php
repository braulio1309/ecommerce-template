@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="align-items-center">
        <h1 class="h3">{{translate('Wallet Management')}}</h1>
    </div>
</div>

<div class="card">
    <form class="" id="sort_wallets" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-0 h6">{{translate('Customer Wallets')}}</h5>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type email or name & Enter') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>{{translate('Name')}}</th>
                        <th data-breakpoints="lg">{{translate('Email Address')}}</th>
                        <th data-breakpoints="lg">{{translate('Phone')}}</th>
                        <th data-breakpoints="lg">{{translate('Wallet Balance')}}</th>
                        <th data-breakpoints="lg">{{translate('Total Recharges')}}</th>
                        <th class="text-right">{{translate('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $key => $user)
                        @if ($user != null)
                            <tr>
                                <td>
                                    {{ $user->name }}
                                </td>
                                <td>
                                    {{ $user->email }}
                                </td>
                                <td>
                                    {{ $user->phone }}
                                </td>
                                <td>
                                    {{ single_price($user->total_balance) }}
                                </td>
                                <td>
                                    {{ $user->wallets->count() }}
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.wallets.show', $user->id) }}" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ translate('View Wallet Details') }}">
                                        <i class="las la-eye"></i>
                                    </a>
                                    <a href="javascript:void(0)" onclick="recharge_wallet('{{ $user->id }}', '{{ $user->name }}')" class="btn btn-soft-success btn-icon btn-circle btn-sm" title="{{ translate('Recharge Wallet') }}">
                                        <i class="las la-plus"></i>
                                    </a>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $users->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
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

    $(document).ready(function(){
        $('#search').on('keyup', function(){
            if($(this).val() != ''){
                $('#sort_wallets').submit();
            }
        });
    });
</script>
@endsection
