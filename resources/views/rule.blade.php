@extends('layouts.app')

@section('title', 'Poll')

@section('content')
    <div class="container my-5">
        <h1 class="text-center mb-4">{{ __('rules.rules') }}</h1>
        <p class="text-muted text-center">Revision: Mar 2025 | Control version: 1.5</p>
        <div class="card">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        {!! __('rules.deadline') !!}
                    </li>
                    <li class="list-group-item">
                        {!! __('rules.cancellation') !!}
                    </li>
                    <li class="list-group-item">
                        {!! __('rules.upfront_payment') !!}
                    </li>
                    <li class="list-group-item">
                        {!! __('rules.children') !!}
                    </li>
                    <li class="list-group-item">
                        {!! __('rules.drop_in') !!}
                    </li>
                    <li class="list-group-item">
                        {!! __('rules.active_members') !!}
                    </li>
                    <li class="list-group-item">
                        {!! __('rules.max_courts') !!}
                    </li>
                    <li class="list-group-item">
                        {!! __('rules.have_fun') !!}
                    </li>
                    <li class="list-group-item">
                        {!! __('rules.rules_note') !!}
                    </li>
                </ul>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="{{ url('/') }}" class="btn btn-primary">{{ __('general.back_to_booking_page') }}</a>
        </div>
    </div>
@endsection
