@extends('layouts.app')

@section('title', 'Poll')

@section('content')
<div class="row g-5">
    <div class="col-md-12 col-lg-6 mx-auto">
        @if (isset($latestPoll))
            @php
                $pollDate        = \Carbon\Carbon::parse($latestPoll->poll_date)->locale('vi')->isoFormat('dddd DD/MM/YYYY');
                $number_court    = $latestPoll->expected_number_court;
                $price           = $latestPoll->expected_price;
                $totalRegistered = $latestPoll->total_registered;
            @endphp

            <div class="alert alert-info text-center" role="alert">
                <h4>Thông tin đặt sân:</h4>
                Ngày <strong>{{ $pollDate }}</strong>, <br>
                @if ($latestPoll->save_money_mode == 1)
                    01 sân (6pm - 9pm) & 01 sân (7pm - 9pm) <br>
                @else
                    02 sân (6pm - 9pm) <br>
                @endif
                <strong>Số lượng sân:</strong> 0{{ $number_court }} sân<br>
                <strong>Tổng số tiền ước tính: <span class="text-danger">${{ $price }}</span></strong> <br>

                @if (is_null($latestPoll->closed_date))
                    <span class="text-success">Poll đang mở</span>
                @else
                    <span class="text-danger">Poll đã đóng</span>
                @endif
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
                                <td>{{ $vote->number_go_with }}</td>
                                <td>${{ number_format(ceil($pricePerVote * $vote->number_go_with * 100) / 100, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td><strong>Tổng số đăng ký</strong></td>
                            <td><strong>{{ $totalRegistered }}</strong></td>
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
