@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <!-- Dashboard Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-bg-primary mb-3">
                <div class="card-header">Total Polls</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $totalPolls }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-success mb-3">
                <div class="card-header">Total Players</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $totalPlayers }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-info mb-3">
                <div class="card-header">Total Votes</div>
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
                    <h5>Create New Poll</h5>
                </div>
                <div class="card-body">
                    <form action="/admin/create-poll" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="poll_date" class="form-label">
                                    Play Date
                                    <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" 
                                       title="Date of the badminton session"></i>
                                </label>
                                <input type="date" class="form-control" id="poll_date" name="poll_date" 
                                    value="{{ now()->next(config('constants.DEFAULT_DAY_OF_WEEK'))->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="total_court" class="form-label">
                                    Total Courts
                                    <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" 
                                       title="Number of courts to book (for historical records only)"></i>
                                </label>
                                <input type="number" class="form-control" id="total_court" name="total_court" 
                                    value="{{ config('constants.DEFAULT_TOTAL_COURT') }}" min="1">
                            </div>
                            <div class="col-md-3">
                                <label for="total_hour" class="form-label">
                                    Total Hours
                                    <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" 
                                       title="Number of hours per session"></i>
                                </label>
                                <input type="number" class="form-control" id="total_hour" name="total_hour" 
                                    value="{{ config('constants.DEFAULT_TOTAL_HOURS') }}" min="1">
                            </div>
                            <div class="col-md-3">
                                <label for="unit_price" class="form-label">
                                    Unit Price
                                    <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" 
                                       title="Base price for one court for one hour, before tax"></i>
                                </label>
                                <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" 
                                    value="{{ config('constants.DEFAULT_PRICE_PER_COURT_PER_HOUR') }}" min="0">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Create New Poll</button>
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
                    <h5>Latest Polls</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Play Date</th>
                                <th>Courts</th>
                                <th>Hours</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Actions</th>
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
                                            <span class="badge bg-danger">Closed</span>
                                        @else
                                            <span class="badge bg-success">Open</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            @if (!$poll->closed_date)
                                                <button type="button" class="btn btn-sm btn-primary edit-poll-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editPollModal" 
                                                    data-poll-uuid="{{ $poll->uuid }}"
                                                    data-poll-date="{{ $poll->poll_date->format('Y-m-d') }}"
                                                    data-total-court="{{ $poll->total_court }}"
                                                    data-total-hours="{{ $poll->total_hours }}"
                                                    data-total-price="{{ $poll->total_price }}">
                                                    Edit
                                                </button>
                                                <form action="/admin/close-poll" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="poll_uuid" value="{{ $poll->uuid }}">
                                                    <button type="submit" class="btn btn-sm btn-warning">Close Poll</button>
                                                </form>
                                            @elseif ($poll->closed_date && $poll->uuid === $latestPollId && $poll->poll_date->startOfDay() >= $today)
                                                <form action="/admin/reopen-poll" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="poll_uuid" value="{{ $poll->uuid }}">
                                                    <button type="submit" class="btn btn-sm btn-success" 
                                                           data-bs-toggle="tooltip" data-bs-placement="top" 
                                                           title="Poll can only be reopened if the play date is in the future">Reopen</button>
                                                </form>
                                            @elseif ($poll->closed_date && $poll->uuid === $latestPollId && $poll->poll_date->startOfDay() < $today)
                                                <span class="badge bg-secondary" data-bs-toggle="tooltip" data-bs-placement="top" 
                                                      title="Cannot reopen polls with past dates">Past date</span>
                                            @endif
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
                        <h5 class="modal-title" id="editPollModalLabel">Edit Poll</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_poll_date" class="form-label">Poll Date</label>
                            <input type="date" class="form-control" id="edit_poll_date" name="poll_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_total_court" class="form-label">
                                Total Courts
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" 
                                   title="Only for historical records, doesn't affect total price"></i>
                            </label>
                            <input type="number" class="form-control" id="edit_total_court" name="total_court" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_total_hours" class="form-label">
                                Total Hours
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" 
                                   title="Number of hours in total"></i>
                            </label>
                            <input type="number" class="form-control" id="edit_total_hours" name="total_hours" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_unit_price" class="form-label">
                                Price 1 Court 1 Hour
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" 
                                   title="Base price for one court for one hour, before tax"></i>
                            </label>
                            <input value="{{ config('constants.DEFAULT_PRICE_PER_COURT_PER_HOUR') }}" type="number" step="0.01" class="form-control" id="edit_unit_price" name="unit_price" min="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
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
