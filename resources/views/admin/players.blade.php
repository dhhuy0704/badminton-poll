@extends('layouts.admin')

@section('title', __('admin.players'))

@section('content')
    <!-- Players Stats -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">{{ __('admin.total_players') }}</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $totalPlayers }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">{{ __('admin.active_players') }}</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $activeTotalPlayers }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Players List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('admin.all_players_list') }}</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPlayerModal">
                        <i class="bi bi-plus-circle"></i> {{ __('admin.add_new_player') }}
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('admin.name') }}</th>
                                    <th>{{ __('admin.email') }}</th>
                                    <th>{{ __('general.status') }}</th>
                                    <th>{{ __('admin.created') }}</th>
                                    <th>{{ __('admin.last_updated') }}</th>
                                    <th>{{ __('admin.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($allPlayers as $player)
                                    <tr>
                                        <td>
                                            <a href="{{ route('player.profile', $player->uuid) }}" class="text-decoration-none">
                                                {{ $player->name }}
                                            </a>
                                        </td>
                                        <td>{{ $player->email ?? __('admin.not_provided') }}</td>
                                        <td>
                                            @if($player->is_active)
                                                <span class="badge bg-success">{{ __('general.active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('general.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $player->created_at->format('Y-m-d') }}</td>
                                        <td>{{ $player->updated_at->format('Y-m-d') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#editPlayerModal" 
                                                   data-player-id="{{ $player->uuid }}"
                                                   data-player-name="{{ $player->name }}"
                                                   data-player-email="{{ $player->email }}">
                                                    <i class="bi bi-pencil"></i> {{ __('admin.edit') }}
                                                </button>
                                                @if($player->is_active)
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                       data-bs-toggle="modal" 
                                                       data-bs-target="#deactivatePlayerModal"
                                                       data-player-id="{{ $player->uuid }}"
                                                       data-player-name="{{ $player->name }}">
                                                        <i class="bi bi-person-x"></i> {{ __('admin.deactivate') }}
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                       data-bs-toggle="modal" 
                                                       data-bs-target="#reactivatePlayerModal"
                                                       data-player-id="{{ $player->uuid }}"
                                                       data-player-name="{{ $player->name }}">
                                                        <i class="bi bi-person-check"></i> {{ __('admin.reactivate') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('admin.no_players') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Player Modal -->
    <div class="modal fade" id="addPlayerModal" tabindex="-1" aria-labelledby="addPlayerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPlayerModalLabel">{{ __('admin.add_new_player') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/admin/players" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('admin.name') }}</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('admin.email') }} ({{ __('general.optional') }})</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('admin.add_new_player') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Player Modal -->
    <div class="modal fade" id="editPlayerModal" tabindex="-1" aria-labelledby="editPlayerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPlayerModalLabel">{{ __('admin.edit_player') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/admin/players/update" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" id="edit-player-id" name="player_id">
                        <div class="mb-3">
                            <label for="edit-name" class="form-label">{{ __('admin.name') }}</label>
                            <input type="text" class="form-control" id="edit-name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-email" class="form-label">{{ __('admin.email') }} ({{ __('general.optional') }})</label>
                            <input type="email" class="form-control" id="edit-email" name="email">
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

    <!-- Deactivate Player Modal -->
    <div class="modal fade" id="deactivatePlayerModal" tabindex="-1" aria-labelledby="deactivatePlayerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deactivatePlayerModalLabel">{{ __('admin.deactivate_player') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/admin/players/deactivate" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" id="deactivate-player-id" name="player_id">
                        <p>{{ __('general.are_you_sure_deactivate') }} <span id="player-name-to-deactivate" class="fw-bold"></span>? {{ __('general.player_will_not_appear') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('admin.deactivate') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reactivate Player Modal -->
    <div class="modal fade" id="reactivatePlayerModal" tabindex="-1" aria-labelledby="reactivatePlayerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reactivatePlayerModalLabel">{{ __('admin.reactivate_player') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/admin/players/reactivate" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" id="reactivate-player-id" name="player_id">
                        <p>{{ __('general.are_you_sure_activate') }} <span id="player-name-to-reactivate" class="fw-bold"></span>? {{ __('general.player_will_appear') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                        <button type="submit" class="btn btn-success">{{ __('admin.reactivate') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Edit Player Modal
        const editPlayerModal = document.getElementById('editPlayerModal');
        if (editPlayerModal) {
            editPlayerModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const playerId = button.getAttribute('data-player-id');
                const playerName = button.getAttribute('data-player-name');
                const playerEmail = button.getAttribute('data-player-email');
                
                const modalIdField = editPlayerModal.querySelector('#edit-player-id');
                const modalNameField = editPlayerModal.querySelector('#edit-name');
                const modalEmailField = editPlayerModal.querySelector('#edit-email');
                
                modalIdField.value = playerId;
                modalNameField.value = playerName;
                modalEmailField.value = playerEmail || '';
            });
        }
        
        // Handle Deactivate Player Modal
        const deactivatePlayerModal = document.getElementById('deactivatePlayerModal');
        if (deactivatePlayerModal) {
            deactivatePlayerModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const playerId = button.getAttribute('data-player-id');
                const playerName = button.getAttribute('data-player-name');
                
                const modalIdField = deactivatePlayerModal.querySelector('#deactivate-player-id');
                const playerNameSpan = deactivatePlayerModal.querySelector('#player-name-to-deactivate');
                
                modalIdField.value = playerId;
                playerNameSpan.textContent = playerName;
            });
        }
        
        // Handle Reactivate Player Modal
        const reactivatePlayerModal = document.getElementById('reactivatePlayerModal');
        if (reactivatePlayerModal) {
            reactivatePlayerModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const playerId = button.getAttribute('data-player-id');
                const playerName = button.getAttribute('data-player-name');
                
                const modalIdField = reactivatePlayerModal.querySelector('#reactivate-player-id');
                const playerNameSpan = reactivatePlayerModal.querySelector('#player-name-to-reactivate');
                
                modalIdField.value = playerId;
                playerNameSpan.textContent = playerName;
            });
        }
    });
</script>
@endsection
