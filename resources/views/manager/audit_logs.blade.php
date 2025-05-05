@extends('manager.layout')

@section('title', 'Audit Logs')


@section('content')
    <div class="audit-header">Audit Logs</div>

    <!-- Display validation errors -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Display flash messages -->
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="audit-filters">
        <form action="{{ route('audit.index') }}" method="GET" class="audit-filter-form">
            <div class="filter-group">
                <label for="model_type">Model Type:</label>
                <select name="model_type" id="model_type">
                    <option value="">All</option>
                    <option value="App\Models\Product" {{ request('model_type') == 'App\Models\Product' ? 'selected' : '' }}>Products</option>
                    <option value="App\Models\Order" {{ request('model_type') == 'App\Models\Order' ? 'selected' : '' }}>Orders</option>
                    <option value="App\Models\Transaction" {{ request('model_type') == 'App\Models\Transaction' ? 'selected' : '' }}>Transactions</option>
                    <option value="App\Models\User" {{ request('model_type') == 'App\Models\User' ? 'selected' : '' }}>Users</option>
                    <option value="App\Models\Trash" {{ request('model_type') == 'App\Models\Trash' ? 'selected' : '' }}>Trash</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="event">Event Type:</label>
                <select name="event" id="event">
                    <option value="">All</option>
                    <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}">
            </div>

            <div class="filter-group">
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}">
            </div>

            <button type="submit" class="filter-submit">Apply Filters</button>
            <a href="{{ route('audit.index') }}" class="filter-reset">Reset</a>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="audit-table-container">
        <table class="audit-table" id="auditLogsTable">
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>User</th>
                    <th>Event</th>
                    <th>Model</th>
                    <th>Changes</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @foreach($auditLogs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('F j Y / g:i A') }}</td>
                        <td>{{ $log->user ? $log->user->name : 'System' }}</td>
                        <td>{{ ucfirst($log->event) }}</td>
                        <td>{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                        <td>
                            @if($log->event === 'updated')
                                <button class="btn-view-changes" onclick="showChanges({{ json_encode($log->old_values) }}, {{ json_encode($log->new_values) }}, '{{ $log->user ? addslashes($log->user->name) : 'System' }}')">
                                    View Changes
                                </button>
                            @elseif($log->event === 'created')
                                <button class="btn-view-changes" onclick="showNewRecord({{ json_encode($log->new_values) }}, '{{ $log->user ? addslashes($log->user->name) : 'System' }}')">
                                    View Details
                                </button>
                            @elseif($log->event === 'deleted')
                                <button class="btn-view-changes" onclick="showDeletedRecord({{ json_encode($log->old_values) }}, '{{ $log->user ? addslashes($log->user->name) : 'System' }}')">
                                    View Details
                                </button>
                            @endif
                        </td>
                        <td>{{ $log->ip_address }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Changes Modal -->
    <div id="changesModal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h2 id="modalTitle" style="text-align:center;">Changes</h2>
            <div id="changesContent"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#auditLogsTable').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[0, 'desc']],
                searching: true
            });
        });

        // Modal handling
        const modal = document.getElementById('changesModal');
        const closeBtn = document.getElementsByClassName('close')[0];
        const changesContent = document.getElementById('changesContent');
        const modalTitle = document.getElementById('modalTitle');

        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        function formatDate(value) {
            if (!value) return value ?? 'N/A';
          
            const date = moment(value, moment.ISO_8601, true);
            if (date.isValid()) {
                return date.format('MMMM D YYYY / h:mm A');
            }
            return value;
        }

        function showChanges(oldValues, newValues, userName) {
            modalTitle.innerText = `Changes by ${userName}`;
            let content = '<table class="changes-table"><tr><th>Field</th><th>Old Value</th><th>New Value</th></tr>';
            for (const field in newValues) {
                if (JSON.stringify(oldValues[field]) !== JSON.stringify(newValues[field])) {
                    const oldValue = field === 'created_at' || field === 'updated_at' ? formatDate(oldValues[field]) : oldValues[field] ?? 'N/A';
                    const newValue = field === 'created_at' || field === 'updated_at' ? formatDate(newValues[field]) : newValues[field];
                    content += `<tr>
                        <td>${field}</td>
                        <td>${oldValue}</td>
                        <td>${newValue}</td>
                    </tr>`;
                }
            }
            content += '</table>';
            changesContent.innerHTML = content;
            modal.style.display = "block";
        }

        function showNewRecord(values, userName) {
            modalTitle.innerText = `New Record by ${userName}`;
            let content = '<table class="changes-table"><tr><th>Field</th><th>Value</th></tr>';
            for (const field in values) {
                const value = field === 'created_at' || field === 'updated_at' ? formatDate(values[field]) : values[field];
                content += `<tr><td>${field}</td><td>${value}</td></tr>`;
            }
            content += '</table>';
            changesContent.innerHTML = content;
            modal.style.display = "block";
        }

        function showDeletedRecord(values, userName) {
            modalTitle.innerText = `Deleted Record by ${userName}`;
            let content = '<table class="changes-table"><tr><th>Field</th><th>Value</th></tr>';
            for (const field in values) {
                const value = field === 'created_at' || field === 'updated_at' ? formatDate(values[field]) : values[field];
                content += `<tr><td>${field}</td><td>${value}</td></tr>`;
            }
            content += '</table>';
            changesContent.innerHTML = content;
            modal.style.display = "block";
        }
    </script>
@endpush