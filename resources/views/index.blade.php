@extends('layouts.app')

@section('title', 'Poll')

@section('content')
@if (isset($voteStatus) && $voteStatus === true)
<div class="alert alert-success text-center" role="alert">
    Bạn đã đăng ký thành công!
</div>
@endif

<div class="row g-5">
    <div class="col-md-12 col-lg-6 mx-auto">
        @if (empty($latestPoll))
            <div class="alert alert-danger text-center" role="alert">
                Không có poll nào hiện tại đang mở <br>hoặc đã đóng vì đủ số lượng tham gia, hẹn bạn tuần sau <br>
            </div>

            <div class="text-center mt-4 mb-4">
                <a href="{{ url('latest-list') }}" class="">Xem danh sách tham gia lần cuối</a>
            </div>
        @else

            @php
                $pollUuid        = $latestPoll->uuid;
                $pollDate        = $latestPoll ? \Carbon\Carbon::parse($latestPoll->poll_date)->locale('vi')->isoFormat('dddd DD/MM/YYYY') : 'N/A';
                $number_court    = $latestPoll->expected_number_court;
                $price           = $latestPoll->expected_price;
                $totalRegistered = $latestPoll->total_registered;
            @endphp

            <h4 class="mb-3 text-center">Thông tin đặt sân:</h4>
            <div class="alert alert-info text-center" role="alert">
                <p>Nếu bạn lần đầu đặt sân, xin vui lòng đọc kỹ <a href="/rule">Nội Quy Nhóm</a></p>
                Ngày <strong>{{ $pollDate }}</strong>, <br>
                @if ($latestPoll->save_money_mode == 1)
                    01 sân (6pm - 9pm) & 01 sân (7pm - 9pm) <br>
                @else
                    02 sân (6pm - 9pm) <br>
                @endif
                <strong>Số lượng sân:</strong> 0{{ $number_court }} sân<br>
                <strong>Tổng số tiền ước tính: <span class="text-danger">${{ $price }}</span></strong> <br>

                @if ($totalRegistered > 0)
                    @php
                        $statusClass = $totalRegistered >= config('constants.MAX_NUMBER_COURT_REGISTER') ? 'text-danger' : 'text-success';
                    @endphp
                    Hiện đã có <strong><span class="{{ $statusClass }}">{{ $totalRegistered }}</span></strong> chỗ được đặt<br>
                @else
                    <strong><span class="text-success">Chưa có ai đăng ký</span></strong> <br>
                @endif
            </div>

            <div class="text-center mt-4 mb-4">
                <a href="{{ url('latest-list') }}" class="">Xem danh sách tham gia</a>
            </div>

            <form id="member_vote" class="needs-validation" action="{{ url('poll') }}" method="POST">
                @csrf
                <input type="hidden" name="poll_uuid" value="{{ $pollUuid }}">
                <div class="col-md-12">
                    <select class="form-select" id="player" name="player_uuid" required="">
                        <option value="">Chọn tên của bạn</option>
                        @foreach ($allPlayer as $id => $player)
                            <option value="{{ $id }}">{{ $player }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">
                        Xin hãy chọn tên đúng của bạn
                    </div>
                </div>

                <div class="my-3 px-4">
                    <div class="form-check">
                        <input id="go_with" name="go_with" type="radio" class="form-check-input" value='1' checked="" required="">
                        <label class="form-check-label" for="go_with">Đi mình ên</label>
                    </div>
                    <div class="form-check">
                        <input id="go_with" name="go_with" type="radio" class="form-check-input" value='2' required="">
                        <label class="form-check-label" for="go_with">Đi 2 người</label>
                    </div>
                    <div class="form-check">
                        <input id="go_with" name="go_with" type="radio" class="form-check-input" value='3' required="">
                        <label class="form-check-label" for="go_with">Đi 3 người</label>
                    </div>
                </div>

                <small class="text-muted text-center">*Nếu bạn muốn đăng ký cho số người nhiều hơn ở trên, xin hãy liên lạc với người đặt sân trước ngày mở poll</small>

                <hr class="my-4">

                <button class="w-100 btn btn-primary btn-lg mb-5" type="button" data-bs-toggle="modal" data-bs-target="#confirmation_modal">Đặt chỗ</button>

                <!-- Confirmation Modal -->
                <div class="modal fade" id="confirmation_modal" tabindex="-1" aria-labelledby="confirmation_modal_label" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmation_modal_label">Xác nhận đặt chỗ</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <small class="text-muted text-center">Lưu ý nè! Nếu poll đóng, bạn sẽ phải đóng tiền phần của bạn cho dù bạn không đi được vì bất cứ lý do gì</small>
                                <ul class="mt-3">
                                    <li><strong>Tên người chơi:</strong> <span id="modal_player_name"></span></li>
                                    <li><strong>Số người đi:</strong> <span id="modal_go_with"></span></li>
                                </ul>
                                Có chắc chưa?

                                <script>
                                    document.getElementById('player').addEventListener('change', function () {
                                        const selectedOption = this.options[this.selectedIndex];
                                        document.getElementById('modal_player_name').textContent = selectedOption.text || 'Chưa chọn';
                                    });

                                    document.querySelectorAll('input[name="go_with"]').forEach(function (radio) {
                                        radio.addEventListener('change', function () {
                                            document.getElementById('modal_go_with').textContent = this.value + ' người';
                                        });
                                    });

                                    // Initialize modal content on page load
                                    const playerNameSelect = document.getElementById('player');
                                    const selectedOption = playerNameSelect.options[playerNameSelect.selectedIndex];
                                    document.getElementById('modal_player_name').textContent = selectedOption.text || 'Chưa chọn';
                                    const selectedGoWith = document.querySelector('input[name="go_with"]:checked');
                                    document.getElementById('modal_go_with').textContent = selectedGoWith ? selectedGoWith.value + ' người' : 'Chưa chọn';
                                </script>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ê, để suy nghĩ lại</button>
                                <button type="button" class="btn btn-primary" id="confirm_submit" disabled>Chắc rồi nha</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.getElementById('confirm_submit').addEventListener('click', function () {
                        document.getElementById('member_vote').submit();
                    });
                </script>
            </form>

        @endif

    </div>
</div>
<script>
    const confirmButton = document.getElementById('confirm_submit');

    playerNameSelect.addEventListener('change', function () {
        confirmButton.disabled = !this.value;
    });

    // Initialize button state on page load
    confirmButton.disabled = !playerNameSelect.value;
</script>
@endsection
