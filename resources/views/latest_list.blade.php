@extends('layouts.app')

@section('title', 'Latest Vote List')

@section('content')

<div class="row g-5">
    <div class="col-md-12 col-lg-6 mx-auto">
        @if (isset($latestPoll))
            @php
                $pollDate        = \Carbon\Carbon::parse($latestPoll->poll_date)->locale('vi')->isoFormat('dddd DD/MM/YYYY');
                $number_court    = $latestPoll->total_court;
                $price           = $latestPoll->total_price;
                $totalRegistered = $latestPoll->total_registered;
                $is_poll_open    = is_null($latestPoll->closed_date) ? true : false;

                $statusClass = 'success';
                $statusText  = 'POLL ĐANG MỞ';

                if ($is_poll_open === false) {
                    $statusClass = 'danger';
                    $statusText  = 'POLL ĐÃ ĐÓNG';
                }
            @endphp

            <div class="alert alert-{{ $statusClass }} text-center" role="alert">
                <h4>Thông tin đặt sân:</h4>
                Ngày <strong>{{ $pollDate }}</strong>, <br>

                <strong>Số lượng sân:</strong> {{ $number_court }} sân<br>
                <br>
                <span class="text-{{ $statusClass }}"><strong>{{ $statusText }}</strong></span>
            </div>
        @endif

        @if ($totalRegistered <= 0)
            <div class="alert alert-danger text-center" role="alert">
                <strong>Chưa có ai đăng ký</strong>
            </div>
        @else
            @php
                $pricePerVote = $price / $totalRegistered;
            @endphp
            <table class="table table-bordered" cellpadding="10" cellspacing="0">
                <thead>
                    <tr>
                        <th>Thành viên</th>
                        <th>Số lượng chỗ</th>
                        <th>Tiền sân sẽ đóng</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @if($votes->isEmpty())
                        <tr>
                            <td colspan="2" class="text-center">No votes available</td>
                        </tr>
                    @else
                        @foreach($votes as $vote)
                            <tr>
                                <td>{{ $vote->player_name }}</td>
                                <td>{{ $vote->slot }}</td>
                                <td>${{ number_format(ceil($pricePerVote * $vote->slot * 100) / 100, 2) }}</td>
                                <td>
                                    @if ($is_poll_open)
                                        <form action="{{ url('cancel-vote') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="vote_uuid" value="{{ $vote->uuid }}">
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmCancelModal-{{ $vote->uuid }}">Rút</button>

                                            <!-- Modal -->
                                            <div class="modal fade" id="confirmCancelModal-{{ $vote->uuid }}" tabindex="-1" aria-labelledby="confirmCancelModalLabel-{{ $vote->uuid }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="confirmCancelModalLabel-{{ $vote->uuid }}">Rút vote?</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Không có bấm lộn phải không?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                            <button type="submit" class="btn btn-danger">Rút lui</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-secondary btn-sm" disabled>Rút</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td><strong>Tổng</strong></td>
                            <td><strong>{{ $totalRegistered }}</strong></td>
                            <td><strong>${{ $price }}</strong></td>
                            <td></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        @endif
        <div class="text-center mt-4">
            <a href="{{ url('/') }}" class="btn btn-primary">Quay về trang đặt chỗ</a>
        </div>
    </div>
</div>

@endsection
