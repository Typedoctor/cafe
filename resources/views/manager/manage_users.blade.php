@extends('manager.layout')

@section('title', 'Manage Users')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .usr-form-group small {
            display: block;
            margin-top: 5px;
            font-size: 0.8em;
            color: #666;
        }
    </style>
@endpush

@section('content')
<h1 class="usr-manage-title">Manage Users</h1>

<div class="usr-top-bar">
    <button id="addUserBtn" class="usr-btn usr-add-btn">+ Add User</button>
</div>

<div id="userModal" class="usr-modal">
    <span class="usr-close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
    <div class="usr-modal-content">
        <h2 id="modalTitle">Add New User</h2>
        <div id="errorMessages" class="usr-error-messages" style="display: none; color: red; margin-bottom: 10px;"></div>
        <form id="userForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            <input type="hidden" name="user_id" id="userId">
            <div class="usr-form-group">
                <label>User name:</label>
                <input type="text" name="name" id="name" required maxlength="24">
                <small id="nameCount">0 / 24</small>
            </div>
            <div class="usr-form-group" name="pass">
                <label>Password:</label>
                <input type="password" name="password" id="password">
            </div>
            <div class="usr-form-group" id="confirmPasswordGroup" style="display: none;">
                <label>Confirm Password:</label>
                <input type="password" name="password_confirmation" id="passwordConfirmation">
            </div>
            <div class="usr-form-group">
                <label>Privilege:</label>
                <select name="privilege" id="privilege" required>
                    <option value="cashier">Cashier</option>
                    <option value="manager">Manager</option>
                </select>
            </div>
            <div class="usr-form-group">
                <label>Status:</label>
                <select name="is_active" id="isActive" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="usr-button-group">
                <button type="submit" class="usr-btn usr-save-btn" id="saveBtn">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="usr-table-container">
    <div class="usr-section-title">Users List</div>
    <table class="usr-table" id="usersTable">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>User name</th>
                <th>Privilege</th>
                <th>Status</th>
                <th style="width: 150px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->privilege }}</td>
                    <td>
                        <select class="status-dropdown" data-user-id="{{ $user->id }}">
                            <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </td>
                    <td>
                        <button class="usr-btn usr-edit-btn" 
                            data-id="{{ $user->id }}" 
                            data-name="{{ $user->name }}" 
                            data-privilege="{{ $user->privilege }}"
                            data-active="{{ $user->is_active }}"><i class="fa-solid fa-pencil"></i>
                        </button>
                        <form action="{{ route('manage_users.destroy', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="usr-btn usr-delete-btn"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<form id="status-form" method="POST" style="display: none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="is_active" id="status-value">
</form>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

<script>
$(document).ready(function () {
    let table = $('#usersTable').DataTable({
        pageLength: 10,
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: 4 } // Disable sorting on Actions column
        ]
    });

    // Ensure edit buttons work with DataTables
    $('#usersTable').on('click', '.usr-edit-btn', function() {
        const modalTitle = document.getElementById("modalTitle");
        const methodField = document.getElementById("methodField");
        const userForm = document.getElementById("userForm");
        const userModal = document.getElementById("userModal");
        const saveBtn = document.getElementById("saveBtn");
        const confirmPasswordGroup = document.getElementById("confirmPasswordGroup");
        const passwordInput = document.getElementById("password");
        const confirmPasswordInput = document.getElementById("passwordConfirmation");

        modalTitle.innerText = "Edit User";
        methodField.value = "PUT";
        userForm.action = `/manage_users/${this.dataset.id}`;
        document.getElementById("userId").value = this.dataset.id;
        document.getElementById("name").value = this.dataset.name;
        document.getElementById("privilege").value = this.dataset.privilege;
        document.getElementById("isActive").value = this.dataset.active;
        document.getElementById("password").value = "";
        document.getElementById("password").placeholder = "Change pass? (If no, leave blank)";
        confirmPasswordGroup.style.display = "none";
        passwordInput.required = false;
        confirmPasswordInput.required = false;
        saveBtn.innerText = "UPDATE";
        userModal.style.display = "block";
        document.getElementById("errorMessages").style.display = "none";

        // Update character counter
        updateCharacterCount('name', 'nameCount', 24);
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const userModal = document.getElementById("userModal");
    const closeBtn = document.querySelector(".usr-close-btn");
    const userForm = document.getElementById("userForm");
    const modalTitle = document.getElementById("modalTitle");
    const methodField = document.getElementById("methodField");
    const confirmPasswordGroup = document.getElementById("confirmPasswordGroup");
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("passwordConfirmation");
    const nameInput = document.getElementById("name");
    const saveBtn = document.getElementById("saveBtn");
    const errorMessages = document.getElementById("errorMessages");
    const statusForm = document.getElementById("status-form");
    const statusValue = document.getElementById("status-value");

    // Function to display error messages
    function showError(message) {
        errorMessages.style.display = 'block';
        errorMessages.innerHTML = message;
    }

    // Function to clear error messages
    function clearErrors() {
        errorMessages.style.display = 'none';
        errorMessages.innerHTML = '';
    }

    // Function to validate name (letters and spaces only)
    function validateName(name) {
        const nameRegex = /^[A-Za-z\s]+$/;
        return nameRegex.test(name);
    }

    // Function to validate password (minimum 6 characters)
    function validatePassword(password) {
        return password.length >= 6;
    }

    // Function to check if user name already exists
    function isUserNameExists(name) {
        let exists = false;
        $('#usersTable tbody tr').each(function() {
            const rowName = $(this).find('td').eq(1).text().trim();
            if (rowName.toLowerCase() === name.toLowerCase()) {
                exists = true;
                return false; // Break the loop
            }
        });
        return exists;
    }

    // Update character count
    function updateCharacterCount(inputId, countId, maxLength) {
        const input = document.getElementById(inputId);
        const count = document.getElementById(countId);
        if (input && count) {
            const currentCount = input.value.length;
            count.textContent = `${currentCount} / 24 characters`;
        }
    }

    if (nameInput) {
        nameInput.addEventListener("input", () => updateCharacterCount('name', 'nameCount', 24));
        updateCharacterCount('name', 'nameCount', 24); // Initial value
    }

    // Handle status dropdown change
    document.querySelectorAll('.status-dropdown').forEach(dropdown => {
        dropdown.addEventListener('change', function() {
            const userId = this.getAttribute('data-user-id');
            const newStatus = this.value;
            
            // Set form action and status value
            statusForm.action = `/manage_users/${userId}/update_status`;
            statusValue.value = newStatus;
            
            // Submit the form
            statusForm.submit();
        });
    });

    document.getElementById("addUserBtn").addEventListener("click", function () {
        modalTitle.innerText = "Add New User";
        methodField.value = "POST";
        userForm.action = "{{ route('manage_users.store') }}";
        passwordInput.placeholder = "";
        passwordInput.required = true;
        confirmPasswordGroup.style.display = "block";
        confirmPasswordInput.required = true;
        document.getElementById("isActive").value = "1"; // Default to active for new users
        saveBtn.innerText = "ADD";
        userModal.style.display = "block";
        userForm.reset();
        clearErrors();
        updateCharacterCount('name', 'nameCount', 24); // Reset counter
    });

    document.querySelectorAll(".usr-edit-btn").forEach(button => {
        button.addEventListener("click", function () {
            modalTitle.innerText = "Edit User";
            methodField.value = "PUT";
            userForm.action = `/manage_users/${this.dataset.id}`;
            document.getElementById("userId").value = this.dataset.id;
            document.getElementById("name").value = this.dataset.name;
            document.getElementById("privilege").value = this.dataset.privilege;
            document.getElementById("isActive").value = this.dataset.active;
            document.getElementById("password").value = "";
            document.getElementById("password").placeholder = "Change pass? (If no, leave blank)";
            confirmPasswordGroup.style.display = "none";
            passwordInput.required = false;
            confirmPasswordInput.required = false;
            saveBtn.innerText = "UPDATE";
            userModal.style.display = "block";
            clearErrors();
            updateCharacterCount('name', 'nameCount', 24); // Update counter
        });
    });

    closeBtn.addEventListener("click", () => {
        userModal.style.display = "none";
        clearErrors();
    });

    window.addEventListener("click", event => {
        if (event.target === userModal) {
            userModal.style.display = "none";
            clearErrors();
        }
    });

    userForm.addEventListener("submit", function (event) {
        clearErrors();
        let errors = [];

        // Validate name
        if (!validateName(nameInput.value)) {
            errors.push("User name must contain letters only.");
        }

        // Check for existing user name when adding new user
        if (methodField.value === "POST" && isUserNameExists(nameInput.value)) {
            errors.push("User name already exists. Please select a different username.");
        }

        if (methodField.value === "POST") {
            // Validate password
            if (!validatePassword(passwordInput.value)) {
                errors.push("Password must be at least 6 characters long.");
            }

            // Validate password confirmation
            if (passwordInput.value !== confirmPasswordInput.value) {
                errors.push("Passwords do not match!");
            }
        } else if (methodField.value === "PUT" && passwordInput.value) {
            // Validate password when updating if provided
            if (!validatePassword(passwordInput.value)) {
                errors.push("New password must be at least 6 characters long.");
            }
        }

        if (errors.length > 0) {
            event.preventDefault();
            showError(errors.join("<br>"));
        }
    });
});
</script>
@endpush
