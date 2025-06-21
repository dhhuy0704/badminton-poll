@extends('layouts.app')

@section('title', 'Latest Vote List')

@section('content')

<div class="row g-5">
    <div class="col-md-12 col-lg-6 mx-auto">
        @if (isset($latestPoll))
            @php
                $pollDate        = \Carbon\Carbon::parse($latestPoll->poll_date)->locale(app()->getLocale())->isoFormat('dddd DD/MM/YYYY');
                $number_court    = $latestPoll->total_court;
                $price           = $latestPoll->total_price;
                $totalRegistered = $latestPoll->total_registered;
                $is_poll_open    = is_null($latestPoll->closed_date) ? true : false;

                $statusClass = 'success';
                $statusText  = __('latest_list.poll_open');

                if ($is_poll_open === false) {
                    $statusClass = 'danger';
                    $statusText  = __('latest_list.poll_closed');
                }
            @endphp

            <div class="alert alert-{{ $statusClass }} text-center" role="alert">
                <h4>{{ __('latest_list.booking_info') }}</h4>
                {{ __('latest_list.date') }} <strong>{{ $pollDate }}</strong>, <br>

                <strong>{{ __('latest_list.number_of_courts') }}</strong> {{ $number_court }} {{ __('poll.courts') }}<br>
                <br>
                <span class="text-{{ $statusClass }}"><strong>{{ $statusText }}</strong></span>
            </div>
        @endif

        @if ($totalRegistered <= 0)
            <div class="alert alert-danger text-center" role="alert">
                <strong>{{ __('latest_list.no_registrations') }}</strong>
            </div>
        @else
            @php
                $pricePerVote = $price / $totalRegistered;
            @endphp
            <table class="table table-bordered" cellpadding="10" cellspacing="0">
                <thead>
                    <tr>
                        <th>{{ __('latest_list.member') }}</th>
                        <th>{{ __('latest_list.slots') }}</th>
                        <th>{{ __('latest_list.court_fee') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @if($votes->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center">{{ __('latest_list.no_votes') }}</td>
                        </tr>
                    @else
                        @foreach($votes as $vote)
                            <tr>
                                <td>
                                    <a href="{{ route('player.profile', $vote->player_uuid) }}" class="text-decoration-none">
                                        {{ $vote->player_name }}
                                    </a>
                                </td>
                                <td>{{ $vote->slot }}</td>
                                <td>${{ number_format(ceil($pricePerVote * $vote->slot * 100) / 100, 2) }}</td>
                                <td>
                                    @if ($is_poll_open)
                                        <form action="{{ url('cancel-vote') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="vote_uuid" value="{{ $vote->uuid }}">
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmCancelModal-{{ $vote->uuid }}">{{ __('latest_list.withdraw') }}</button>

                                            <!-- Modal -->
                                            <div class="modal fade" id="confirmCancelModal-{{ $vote->uuid }}" tabindex="-1" aria-labelledby="confirmCancelModalLabel-{{ $vote->uuid }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="confirmCancelModalLabel-{{ $vote->uuid }}">{{ __('latest_list.withdraw') }}?</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            {{ __('latest_list.confirm_withdrawal') }}
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                                                            <button type="submit" class="btn btn-danger">{{ __('latest_list.withdraw') }}</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-secondary btn-sm" disabled>{{ __('latest_list.withdraw') }}</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td><strong>{{ __('latest_list.total') }}</strong></td>
                            <td><strong>{{ $totalRegistered }}</strong></td>
                            <td><strong>${{ $price }}</strong></td>
                            <td></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        @endif
        <div class="text-center mt-4">
            <a href="{{ url('/') }}" class="btn btn-primary">{{ __('general.back_to_booking_page') }}</a>
        </div>
    </div>
</div>

@endsection
