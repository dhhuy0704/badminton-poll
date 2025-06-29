@extends('layouts.app')

@section('title', $player->name . ' - Profile')

@section('content')
<div class="container">
    <div class="row g-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2>{{ $player->name }}</h2>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h3>{{ __('general.player_information') }}</h3>
                            <table class="table">
                                <tr>
                                    <th>{{ __('general.name') }}:</th>
                                    <td>{{ $player->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('general.email') }}:</th>
                                    <td>{{ $player->email ?? __('admin.not_provided') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('general.joined') }}:</th>
                                    <td>{{ $player->created_at->format('Y-m-d') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('general.status') }}:</th>
                                    <td>
                                        @if($player->is_active)
                                            <span class="badge bg-success">{{ __('general.active') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('general.inactive') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h3>{{ __('general.participation_summary') }}</h3>
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <div class="row">
                                        <div class="col-md-6 border-end">
                                            <h4 class="display-4">{{ $statistics['totalGames'] }}</h4>
                                            <p class="text-muted">{{ __('general.games_played') }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h4 class="display-4">${{ number_format($statistics['totalMoneySpent'], 2) }}</h4>
                                            <p class="text-muted">{{ __('general.total_money_spent') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <h3>{{ __('general.participation_statistics') }}</h3>
                            
                            <!-- Date Range Filter Form -->
                            <form class="row g-3 mb-4" method="GET">
                                <div class="col-md-4">
                                    <label for="start_date" class="form-label">{{ __('general.start_date') }}</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="end_date" class="form-label">{{ __('general.end_date') }}</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">{{ __('general.filter') }}</button>
                                </div>
                            </form>
                            
                            @if(isset($debug) && config('app.debug'))
                            <div class="alert alert-info small mb-4">
                                <h6>Debug Information:</h6>
                                <ul class="mb-0">
                                    <li>Date Range: {{ $debug['startDate'] }} to {{ $debug['endDate'] }}</li>
                                    <li>Has Votes: {{ $debug['hasVotes'] ? 'Yes' : 'No' }}</li>
                                    <li>Has Monthly Stats: {{ $debug['hasMonthlyStats'] ? 'Yes' : 'No' }}</li>
                                    <li>Votes Count: {{ $debug['votesCount'] }}</li>
                                    <li>Monthly Stats Count: {{ $debug['monthlyStatsCount'] }}</li>
                                </ul>
                            </div>
                            @endif
                            
                            <!-- Charts -->
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="chart-container" style="position: relative; height:300px; width:100%; border: 1px solid #eee; padding: 15px; border-radius: 5px;">
                                        <h5 class="text-center mb-3">{{ __('general.games_played') }}</h5>
                                        @if(count($statistics['monthlyStats']) > 0)
                                            <canvas id="gamesChart"></canvas>
                                        @else
                                            <div class="d-flex justify-content-center align-items-center h-100">
                                                <p class="text-muted">{{ __('general.no_data_available') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="chart-container" style="position: relative; height:300px; width:100%; border: 1px solid #eee; padding: 15px; border-radius: 5px;">
                                        <h5 class="text-center mb-3">{{ __('general.money_spent') }}</h5>
                                        @if(count($statistics['monthlyStats']) > 0)
                                            <canvas id="moneyChart"></canvas>
                                        @else
                                            <div class="d-flex justify-content-center align-items-center h-100">
                                                <p class="text-muted">{{ __('general.no_data_available') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h3>{{ __('general.participation_history') }}</h3>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ __('poll.date') }}</th>
                                            <th>{{ __('latest_list.slots') }}</th>
                                            <th>{{ __('poll.cost') }}</th>
                                            <th>{{ __('poll.vote_date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($statistics['votes'] as $vote)
                                            @php
                                                // Calculate cost for this vote
                                                $totalVotesInPoll = App\Models\Vote::where('poll_uuid', $vote->poll_uuid)->sum('slot');
                                                $costPerVote = $totalVotesInPoll > 0 ? $vote->total_price / $totalVotesInPoll : 0;
                                                $voteCost = $costPerVote * $vote->slot;
                                            @endphp
                                            <tr>
                                                <td>{{ Carbon\Carbon::parse($vote->poll_date)->format('M d, Y') }}</td>
                                                <td>{{ $vote->slot }}</td>
                                                <td>${{ number_format($voteCost, 2) }}</td>
                                                <td>{{ Carbon\Carbon::parse($vote->voted_date)->format('M d, Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">No participation records found for the selected date range.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ url('/latest-list') }}" class="btn btn-secondary">{{ __('general.back_to_latest_list') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing charts');
        try {
            // Check if Chart.js is available
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                return;
            }

            // Parse data from PHP with error handling
            const monthlyStats = @json($statistics['monthlyStats'] ?? []);

            // Extract labels and data for chart
            const labels = monthlyStats.map(stat => stat.month || '');
            const gameCountData = monthlyStats.map(stat => stat.count || 0);
            const moneySpentData = monthlyStats.map(stat => stat.moneySpent || 0);
            
            // Shared chart options
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                elements: {
                    line: {
                        tension: 0.4
                    }
                }
            };
            
            // Create games played chart
            const gamesChartCanvas = document.getElementById('gamesChart');
            if (gamesChartCanvas) {
                const gamesCtx = gamesChartCanvas.getContext('2d');
                const gamesChart = new Chart(gamesCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '{{ __("general.games_played") }}',
                            data: gameCountData,
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                            pointRadius: 4,
                            fill: true
                        }]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    stepSize: 1
                                },
                                title: {
                                    display: true,
                                    text: '{{ __("general.games_played") }}'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: '{{ __("general.month") }}'
                                }
                            }
                        }
                    }
                });
            }
            
            // Create money spent chart
            const moneyChartCanvas = document.getElementById('moneyChart');
            if (moneyChartCanvas) {
                const moneyCtx = moneyChartCanvas.getContext('2d');
                const moneyChart = new Chart(moneyCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '{{ __("general.money_spent") }}',
                            data: moneySpentData,
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                            pointRadius: 4,
                            fill: true
                        }]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toFixed(2);
                                    }
                                },
                                title: {
                                    display: true,
                                    text: '{{ __("general.amount") }}'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: '{{ __("general.month") }}'
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '{{ __("general.amount") }}: $' + context.parsed.y.toFixed(2);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Error initializing charts:', error);
            const errorMessage = '<div class="alert alert-danger text-center">Error loading chart data</div>';
            
            const gamesChartCanvas = document.getElementById('gamesChart');
            if (gamesChartCanvas) {
                const gamesContainer = gamesChartCanvas.parentElement;
                gamesContainer.innerHTML = errorMessage;
            }
            
            const moneyChartCanvas = document.getElementById('moneyChart');
            if (moneyChartCanvas) {
                const moneyContainer = moneyChartCanvas.parentElement;
                moneyContainer.innerHTML = errorMessage;
            }
        }
    });
</script>
@endsection
