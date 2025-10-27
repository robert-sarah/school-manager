<?php require_once VIEW_PATH . 'layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Create New Event</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="/events/create" id="eventForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="form-control-label">Event Title</label>
                                    <input class="form-control" type="text" id="title" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="event_type" class="form-control-label">Event Type</label>
                                    <select class="form-select" id="event_type" name="event_type" required>
                                        <option value="">Select Type</option>
                                        <?php foreach ($eventTypes as $value => $label): ?>
                                            <option value="<?= $value ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date" class="form-control-label">Start Date & Time</label>
                                    <input class="form-control" type="datetime-local" id="start_date" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date" class="form-control-label">End Date & Time</label>
                                    <input class="form-control" type="datetime-local" id="end_date" name="end_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="location" class="form-control-label">Location</label>
                            <input class="form-control" type="text" id="location" name="location" required>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-control-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="users" class="form-control-label">Select Participants (Users)</label>
                                    <select class="form-select" id="users" name="users[]" multiple>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?= $user['id'] ?>">
                                                <?= htmlspecialchars($user['name']) ?>
                                                (<?= htmlspecialchars($user['role']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="classes" class="form-control-label">Select Participants (Classes)</label>
                                    <select class="form-select" id="classes" name="classes[]" multiple>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?= $class['id'] ?>">
                                                <?= htmlspecialchars($class['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_public" name="is_public">
                            <label class="form-check-label" for="is_public">Make this event public</label>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Create Event</button>
                            <a href="/events" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#users, #classes').select2({
        placeholder: 'Select participants',
        allowClear: true
    });
    
    $('#eventForm').on('submit', function(e) {
        var startDate = new Date($('#start_date').val());
        var endDate = new Date($('#end_date').val());
        
        if (endDate <= startDate) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Dates',
                text: 'End date must be after start date'
            });
        }
    });
});
</script>

<?php require_once VIEW_PATH . 'layouts/footer.php'; ?>
