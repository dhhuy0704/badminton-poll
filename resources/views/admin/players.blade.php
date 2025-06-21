@extends('layouts.admin')

@section('title', 'Players')

@section('content')
    <!-- Players Stats -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-bg-success mb-3">
                <div class="card-header">Total Players</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $totalPlayers }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-bg-info mb-3">
                <div class="card-header">Active Players</div>
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
                    <h5>Active Players List</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPlayerModal">
                        <i class="bi bi-plus-circle"></i> Add New Player
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Created</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activePlayers as $player)
                                    <tr>
                                        <td>
                                            <a href="{{ route('player.profile', $player->uuid) }}" class="text-decoration-none">
                                                {{ $player->name }}
                                            </a>
                                        </td>
                                        <td>{{ $player->email ?? 'Not provided' }}</td>
                                        <td>{{ $player->created_at->format('M d, Y') }}</td>
                                        <td>{{ $player->updated_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#editPlayerModal" 
                                                   data-player-id="{{ $player->uuid }}"
                                                   data-player-name="{{ $player->name }}"
                                                   data-player-email="{{ $player->email }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#deactivatePlayerModal"
                                                   data-player-id="{{ $player->uuid }}"
                                                   data-player-name="{{ $player->name }}">
                                                    <i class="bi bi-person-x"></i> Deactivate
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No active players found.</td>
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
                    <h5 class="modal-title" id="addPlayerModalLabel">Add New Player</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/admin/players" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email (optional)</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Player</button>
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
                    <h5 class="modal-title" id="editPlayerModalLabel">Edit Player</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/admin/players/update" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" id="edit-player-id" name="player_id">
                        <div class="mb-3">
                            <label for="edit-name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit-name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-email" class="form-label">Email (optional)</label>
                            <input type="email" class="form-control" id="edit-email" name="email">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Player</button>
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
                    <h5 class="modal-title" id="deactivatePlayerModalLabel">Deactivate Player</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/admin/players/deactivate" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" id="deactivate-player-id" name="player_id">
                        <p>Are you sure you want to deactivate <span id="player-name-to-deactivate" class="fw-bold"></span>? This player will no longer appear in polls.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Deactivate Player</button>
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
    });
</script>
@endsection
