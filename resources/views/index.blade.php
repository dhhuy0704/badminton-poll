@extends('layouts.app')

@section('title', 'Poll')

@section('content')
@if (isset($voteStatus) && $voteStatus === true)
<div class="alert alert-success text-center" role="alert">
    {{ __('poll.registration_success') }}
</div>
@endif

<div class="row g-3">
    <div class="col-md-12 col-lg-6 mx-auto">
        @if (empty($latestPoll))

            <div class="alert alert-danger text-center" role="alert">
                {!! __('poll.no_poll_available') !!} <br><br>
                {!! __('poll.next_poll_opens') !!}
            </div>

            <div class="text-center mt-4 mb-4">
                <a href="{{ url('latest-list') }}" class="">{{ __('poll.view_latest_list') }}</a>
            </div>
        @else

            @php
                $pollUuid        = $latestPoll->uuid;
                $pollDate        = $latestPoll ? \Carbon\Carbon::parse($latestPoll->poll_date)->locale(app()->getLocale())->isoFormat('dddd DD/MM/YYYY') : 'N/A';
                $number_court    = $latestPoll->total_court;
                $totalRegistered = $latestPoll->total_registered;
            @endphp

            <h4 class="mb-3 text-center">{{ __('poll.booking_info') }}</h4>
            <div class="alert alert-warning text-center" role="alert">
                <p>{!! __('poll.first_time_note') !!}</p>
                {{ __('poll.date') }} <strong>{{ $pollDate }}</strong>, <br>

                <strong>{{ __('poll.number_of_courts') }}</strong> {{ $number_court }} {{ __('poll.courts') }}<br>

                @if ($totalRegistered > 0)
                    @php
                        $statusClass = $totalRegistered >= config('constants.MAX_MEMBER_REGISTER') ? 'text-danger' : 'text-success';
                    @endphp
                    {!! __('poll.registered_slots', ['class' => $statusClass, 'current' => $totalRegistered, 'max' => config('constants.MAX_MEMBER_REGISTER')]) !!}<br>
                @else
                    {!! __('poll.no_registrations') !!}<br>
                @endif
            </div>

            <div class="text-center mt-4 mb-4">
                <a href="{{ url('latest-list') }}" class="">{{ __('poll.view_latest_list') }}</a>
            </div>

            <form id="member_vote" class="needs-validation" action="{{ url('poll') }}" method="POST">
                @csrf
                <input type="hidden" name="poll_uuid" value="{{ $pollUuid }}">
                <div class="col-md-12">
                    <select class="form-select" id="player" name="player_uuid" required="">
                        <option value="">{{ __('poll.choose_your_name') }}</option>
                        @foreach ($allPlayer as $id => $player)
                            <option value="{{ $id }}">{{ $player }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">
                        {{ __('poll.invalid_name_feedback') }}
                    </div>
                </div>

                <div class="my-3 px-4">
                    <div class="form-check">
                        <input id="go_with" name="slot" type="radio" class="form-check-input" value='1' checked="" required="">
                        <label class="form-check-label" for="go_with">{{ __('poll.go_alone') }}</label>
                    </div>
                    <div class="form-check">
                        <input id="go_with" name="slot" type="radio" class="form-check-input" value='2' required="">
                        <label class="form-check-label" for="go_with">{{ __('poll.going_with_two') }}</label>
                    </div>
                    <!--<div class="form-check">
                        <input id="go_with" name="go_with" type="radio" class="form-check-input" value='3' required="">
                        <label class="form-check-label" for="go_with">Đi 3 người</label>
                    </div>-->
                </div>

                <small class="text-muted text-center">{{ __('poll.additional_members_note') }}</small>

                <hr class="my-4">

                <button class="w-100 btn btn-primary btn-lg mb-5" type="button" data-bs-toggle="modal" data-bs-target="#confirmation_modal">{{ __('poll.booking') }}</button>

                <!-- Confirmation Modal -->
                <div class="modal fade" id="confirmation_modal" tabindex="-1" aria-labelledby="confirmation_modal_label" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmation_modal_label">{{ __('poll.confirm_booking') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <small class="text-muted text-center">{{ __('poll.booking_note') }}</small>
                                <ul class="mt-3">
                                    <li><strong>{{ __('poll.player_selection') }}</strong> <span id="modal_player_name"></span></li>
                                    <li><strong>{{ __('poll.slots_selection') }}</strong> <span id="modal_go_with"></span></li>
                                </ul>
                                {{ __('poll.are_you_sure') }}

                                <script>
                                    // Define translations for JavaScript use
                                    const translations = {
                                        notSelected: "{{ __('poll.not_selected') }}",
                                        people: "{{ __('poll.people') }}"
                                    };
                                
                                    document.getElementById('player').addEventListener('change', function () {
                                        const selectedOption = this.options[this.selectedIndex];
                                        document.getElementById('modal_player_name').textContent = selectedOption.text || translations.notSelected;
                                    });

                                    document.querySelectorAll('input[name="slot"]').forEach(function (radio) {
                                        radio.addEventListener('change', function () {
                                            document.getElementById('modal_go_with').textContent = this.value + ' ' + translations.people;
                                        });
                                    });

                                    // Initialize modal content on page load
                                    const playerNameSelect = document.getElementById('player');
                                    const selectedOption = playerNameSelect.options[playerNameSelect.selectedIndex];
                                    document.getElementById('modal_player_name').textContent = selectedOption.text || translations.notSelected;
                                    const selectedGoWith = document.querySelector('input[name="slot"]:checked');
                                    document.getElementById('modal_go_with').textContent = selectedGoWith ? selectedGoWith.value + ' ' + translations.people : translations.notSelected;
                                </script>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('poll.no_reconsider') }}</button>
                                <button type="button" class="btn btn-primary" id="confirm_submit" disabled>{{ __('poll.yes_confirm') }}</button>
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

            <script>
                const confirmButton = document.getElementById('confirm_submit');

                playerNameSelect.addEventListener('change', function () {
                    confirmButton.disabled = !this.value;
                });

                // Initialize button state on page load
                confirmButton.disabled = !playerNameSelect.value;
            </script>
        @endif

    </div>
</div>

@endsection
