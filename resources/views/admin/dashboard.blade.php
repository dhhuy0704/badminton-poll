@extends('layouts.admin')

@section('title', __('admin.dashboard'))

@section('content')
    <!-- Dashboard Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">{{ __('admin.total_polls') }}</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $totalPolls }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">{{ __('admin.total_players') }}</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $totalPlayers }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">{{ __('admin.total_votes') }}</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $totalVotes }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Create New Poll Button -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('admin.create_new_poll') }}</h5>
                </div>
                <div class="card-body">
                    <form action="/admin/create-poll" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="poll_date" class="form-label">
                                    {{ __('admin.play_date') }}
                                    <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" 
                                       title="{{ __('admin.date_help') }}"></i>
                                </label>
                                <input type="date" class="form-control" id="poll_date" name="poll_date" 
                                    value="{{ now()->next(config('constants.DEFAULT_DAY_OF_WEEK'))->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="total_court" class="form-label">
                                    {{ __('admin.total_courts') }}
                                    <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" 
                                       title="{{ __('admin.courts_help') }}"></i>
                                </label>
                                <input type="number" class="form-control" id="total_court" name="total_court" 
                                    value="{{ config('constants.DEFAULT_TOTAL_COURT') }}" min="1">
                            </div>
                            <div class="col-md-3">
                                <label for="total_hour" class="form-label">
                                    {{ __('admin.total_hours') }}
                                    <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" 
                                       title="{{ __('admin.hours_help') }}"></i>
                                </label>
                                <input type="number" class="form-control" id="total_hour" name="total_hour" 
                                    value="{{ config('constants.DEFAULT_TOTAL_HOURS') }}" min="1">
                            </div>
                            <div class="col-md-3">
                                <label for="unit_price" class="form-label">
                                    {{ __('admin.unit_price') }}
                                    <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" 
                                       title="{{ __('admin.price_help') }}"></i>
                                </label>
                                <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" 
                                    value="{{ config('constants.DEFAULT_PRICE_PER_COURT_PER_HOUR') }}" min="0">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> {{ __('admin.create_poll') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Polls -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('admin.latest_polls') }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('admin.play_date') }}</th>
                                <th>{{ __('poll.courts') }}</th>
                                <th>{{ __('poll.hours') }}</th>
                                <th>{{ __('poll.total_price') }}</th>
                                <th>{{ __('poll.status') }}</th>
                                <th>{{ __('admin.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($latestPolls as $poll)
                                <tr>
                                    <td>{{ $poll->poll_date->format('Y-m-d') }}</td>
                                    <td>{{ $poll->total_court }}</td>
                                    <td>{{ $poll->total_hours }}</td>
                                    <td>${{ number_format($poll->total_price, 2) }}</td>
                                    <td>
                                        @if ($poll->closed_date)
                                            <span class="badge bg-danger">{{ __('poll.closed') }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('poll.open') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <div class="btn-group" role="group">
                                                @if (!$poll->closed_date)
                                                    <button type="button" class="btn btn-sm btn-outline-primary edit-poll-btn me-2" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editPollModal" 
                                                        data-poll-uuid="{{ $poll->uuid }}"
                                                        data-poll-date="{{ $poll->poll_date->format('Y-m-d') }}"
                                                        data-total-court="{{ $poll->total_court }}"
                                                        data-total-hours="{{ $poll->total_hours }}"
                                                        data-total-price="{{ $poll->total_price }}">
                                                        {{ __('admin.edit') }}
                                                    </button>
                                                    <form action="/admin/close-poll" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="poll_uuid" value="{{ $poll->uuid }}">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('poll.close_poll') }}</button>
                                                    </form>
                                                @elseif ($poll->closed_date && $poll->uuid === $latestPollId && $poll->poll_date->startOfDay() >= $today)
                                                    <form action="/admin/reopen-poll" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="poll_uuid" value="{{ $poll->uuid }}">
                                                        <button type="submit" class="btn btn-sm btn-outline-success" 
                                                            data-bs-toggle="tooltip" data-bs-placement="top" 
                                                            title="{{ __('poll.reopen_help') }}">{{ __('poll.reopen') }}</button>
                                                    </form>
                                                @elseif ($poll->closed_date && $poll->uuid === $latestPollId && $poll->poll_date->startOfDay() < $today)
                                                    <span class="badge bg-secondary" data-bs-toggle="tooltip" data-bs-placement="top" 
                                                        title="{{ __('poll.cannot_reopen') }}">{{ __('poll.past_date') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Poll Modal -->
    <div class="modal fade" id="editPollModal" tabindex="-1" aria-labelledby="editPollModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/admin/update-poll" method="POST">
                    @csrf
                    <input type="hidden" id="edit_poll_uuid" name="poll_uuid" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPollModalLabel">{{ __('admin.edit_poll') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_poll_date" class="form-label">{{ __('admin.play_date') }}</label>
                            <input type="date" class="form-control" id="edit_poll_date" name="poll_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_total_court" class="form-label">
                                {{ __('admin.total_courts') }}
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" 
                                   title="{{ __('admin.courts_help') }}"></i>
                            </label>
                            <input type="number" class="form-control" id="edit_total_court" name="total_court" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_total_hours" class="form-label">
                                {{ __('admin.total_hours') }}
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" 
                                   title="{{ __('admin.hours_help') }}"></i>
                            </label>
                            <input type="number" class="form-control" id="edit_total_hours" name="total_hours" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_unit_price" class="form-label">
                                {{ __('admin.unit_price') }}
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" 
                                   title="{{ __('admin.price_help') }}"></i>
                            </label>
                            <input value="{{ config('constants.DEFAULT_PRICE_PER_COURT_PER_HOUR') }}" type="number" step="0.01" class="form-control" id="edit_unit_price" name="unit_price" min="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('admin.save_changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Handle edit poll button clicks
        const editButtons = document.querySelectorAll('.edit-poll-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Set modal form values from data attributes
                document.getElementById('edit_poll_uuid').value = this.getAttribute('data-poll-uuid');
                document.getElementById('edit_poll_date').value = this.getAttribute('data-poll-date');
                document.getElementById('edit_total_court').value = this.getAttribute('data-total-court');
                document.getElementById('edit_total_hours').value = this.getAttribute('data-total-hours');
                
                // Calculate unit price based on total price, courts, and hours
                const totalPrice = parseFloat(this.getAttribute('data-total-price'));
                const totalCourt = parseInt(this.getAttribute('data-total-court'));
                const totalHours = parseInt(this.getAttribute('data-total-hours'));
                const taxRate = {{ config('constants.PROVINCE_TAX_RATE') }};
            });
        });
    });
</script>
@endsection
