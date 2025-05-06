@extends('layouts.app')

@section('title', 'Poll')

@section('content')
    <div class="container my-5">
        <h1 class="text-center mb-4">NỘI QUY</h1>
        <p class="text-muted text-center">Revision: Mar 2025 | Control version: 1.5</p>
        <div class="card">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>Deadline:</strong> Báo Yes/No (chơi hay không) là <strong>1:00pm Tuesday</strong> hàng tuần. Đủ 8 người sẽ book 1 sân. (first come first served).
                    </li>
                    <li class="list-group-item">
                        Khi đã confirmed chơi thì nếu busy last minute cũng sẽ share tiền (không nhảy ra nhảy vào được). Bạn nào book sân vui lòng dựa vào danh sách đăng ký (để đòi tiền!).
                    </li>
                    <li class="list-group-item">
                        Người đặt sân có thể sẽ phải ứng tiền túi trước gây thâm hụt chi tiêu cá nhân, nên tốt nhất xin các bạn tự giác gửi tiền cho người đặt sân trước.
                    </li>
                    <li class="list-group-item">
                        Quý phụ huynh có con nhỏ, vui lòng nhắc con em nghỉ để người lớn rotate vào chơi.
                    </li>
                    <li class="list-group-item">
                        Các bạn có bạn chơi drop in có thể rủ vào chơi chung 1, 2 set, và nhắc các bạn đó đừng chơi "hăng" quá chiếm sân của member khác nhé.
                    </li>
                    <li class="list-group-item">
                        Để đảm bảo group này có active members and players: thành viên group cần mua membership của court thì sẽ được add vào group. Nếu ai trong group rồi mà <strong>4 buổi liên tiếp (1 tháng)</strong> không đi thì đại diện group sẽ remove bạn này ra -- cho bà con khác tham gia.
                    </li>
                    <li class="list-group-item">
                        Chúng ta sẽ book <strong>maximum 2 sân</strong> (max 14 người là đẹp cho việc chia tiền và việc rotation người chơi).
                    </li>
                    <li class="list-group-item">
                        <strong>Have fun</strong> trên tinh thần vui vẻ - thể thao. Không chính trị - không tôn giáo.
                    </li>
                    <li class="list-group-item">
                        Bất cứ nhóm, tổ chức nào cũng có quy định, nếu bạn không thích quy định của nhóm có thể không tham gia. Nếu bạn muốn đóng góp xin lên tiếng thẳng thắng, đừng nói xấu sau lưng, gây hấn mất đoàn kết với member khác. Đây là nhóm chơi cầu vận động chứ không phải chỗ tỵ nạnh đúng sai.
                    </li>
                </ul>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="{{ url('/') }}" class="btn btn-primary">Quay về trang đặt chỗ</a>
        </div>
    </div>
@endsection
