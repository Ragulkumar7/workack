<?php
// calendar.php - Workack Calendar (with delete + better week/day default + PRG to prevent refresh duplicates)

session_start();
include 'include/db_connect.php';

// Hardcoded user (no login yet)
$current_user_id = 1;

// ────────────────────────────────────────────────
// Handle messages from GET (after redirect)
// ────────────────────────────────────────────────
$alert_msg  = '';
$alert_type = 'info';

if (isset($_GET['msg']) && isset($_GET['type'])) {
    $alert_msg  = htmlspecialchars(urldecode($_GET['msg']));
    $alert_type = $_GET['type'];
}

// ────────────────────────────────────────────────
// Handle NEW EVENT from modal + prevent duplicates + PRG
// ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $title       = trim($_POST['title'] ?? '');
    $start_date  = $_POST['start_date'] ?? '';
    $start_time  = $_POST['start_time'] ?? '09:00';
    $end_time    = $_POST['end_time'] ?? '10:00';
    $location    = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = $_POST['category'] ?? 'Work';

    if (empty($title) || empty($start_date)) {
        header("Location: ?msg=" . urlencode("Please fill Event Name and Date.") . "&type=warning");
        exit;
    }

    $start_datetime = "$start_date $start_time:00";
    $end_datetime   = "$start_date $end_time:00";

    // Check for exact duplicate (title + start datetime + organizer)
    $check_sql = "SELECT id FROM meetings 
                  WHERE title = ? 
                    AND start_time = ? 
                    AND organizer_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ssi", $title, $start_datetime, $current_user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        header("Location: ?msg=" . urlencode("Event '$title' already exists.") . "&type=warning");
        exit;
    }

    $meeting_link = 'meet/' . bin2hex(random_bytes(16));
    $passcode     = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);

    $sql = "INSERT INTO meetings 
            (title, organizer_id, start_time, end_time, description, location, meeting_link, passcode, category) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssssss", $title, $current_user_id, $start_datetime, $end_datetime, $description, $location, $meeting_link, $passcode, $category);

    if ($stmt->execute()) {
        header("Location: ?msg=" . urlencode("Event created successfully!") . "&type=success");
        exit;
    } else {
        header("Location: ?msg=" . urlencode("Database error: " . $conn->error) . "&type=danger");
        exit;
    }

    // Cleanup (not reached due to exit, but good practice)
    $stmt->close();
    $check_stmt->close();
}

// ────────────────────────────────────────────────
// Handle DELETE EVENT (AJAX)
// ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_event') {
    $event_id = (int)($_POST['event_id'] ?? 0);

    if ($event_id > 0) {
        $sql = "DELETE FROM meetings WHERE id = ? AND organizer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $event_id, $current_user_id);
        $success = $stmt->execute();
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// ────────────────────────────────────────────────
// Handle drag & drop (AJAX)
// ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'move_event') {
    $event_id = (int)($_POST['event_id'] ?? 0);
    $new_date = $_POST['new_date'] ?? '';

    if ($event_id && preg_match('/^\d{4}-\d{2}-\d{2}$/', $new_date)) {
        $sql = "UPDATE meetings 
                SET start_time = CONCAT(?, ' ', TIME(start_time)),
                    end_time   = CONCAT(?, ' ', TIME(end_time))
                WHERE id = ? AND organizer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $new_date, $new_date, $event_id, $current_user_id);
        $success = $stmt->execute();
        echo json_encode(['success' => $success]);
        exit;
    }
    echo json_encode(['success' => false]);
    exit;
}

// ────────────────────────────────────────────────
// View & Navigation
// ────────────────────────────────────────────────
$view  = $_GET['view']  ?? 'month';
$month = (int)($_GET['month'] ?? date('n'));
$year  = (int)($_GET['year']  ?? date('Y'));
$day   = (int)($_GET['day']   ?? date('j'));

$today = date('Y-m-d');

// Default to today if invalid date for week/day view
if (($view === 'week' || $view === 'day') && !checkdate($month, $day, $year)) {
    $day   = date('j');
    $month = date('n');
    $year  = date('Y');
}

// Navigation links
$prev_month = $month - 1; $prev_year = $year; if ($prev_month < 1) { $prev_month = 12; $prev_year--; }
$next_month = $month + 1; $next_year = $year; if ($next_month > 12) { $next_month = 1; $next_year++; }

// ────────────────────────────────────────────────
// Calculate main calendar range
// ────────────────────────────────────────────────
$first_day_ts   = mktime(0, 0, 0, $month, 1, $year);
$days_in_month  = date('t', $first_day_ts);
$first_weekday  = date('w', $first_day_ts);

// ────────────────────────────────────────────────
// Fetch events for current view range
// ────────────────────────────────────────────────
if ($view === 'month') {
    $range_start = date('Y-m-01 00:00:00', $first_day_ts);
    $range_end   = date('Y-m-t 23:59:59', $first_day_ts);
} elseif ($view === 'week') {
    $weekday     = date('w', mktime(0,0,0,$month,$day,$year));
    $start_ts    = mktime(0,0,0,$month,$day,$year) - $weekday * 86400;
    $range_start = date('Y-m-d 00:00:00', $start_ts);
    $range_end   = date('Y-m-d 23:59:59', $start_ts + 6*86400);
} else { // day
    $range_start = date('Y-m-d 00:00:00', mktime(0,0,0,$month,$day,$year));
    $range_end   = date('Y-m-d 23:59:59', mktime(0,0,0,$month,$day,$year));
}

$sql_events = "
    SELECT m.id, m.title, m.start_time, m.end_time, m.description, m.location, m.meeting_link, m.passcode,
           COALESCE(m.category, 'Work') AS category
    FROM meetings m
    LEFT JOIN meeting_attendees ma ON m.id = ma.meeting_id
    WHERE (m.organizer_id = ? OR ma.emp_id = ?)
      AND m.start_time BETWEEN ? AND ?
    GROUP BY m.id
    ORDER BY m.start_time ASC
";
$stmt_events = $conn->prepare($sql_events);
$stmt_events->bind_param("iiss", $current_user_id, $current_user_id, $range_start, $range_end);
$stmt_events->execute();
$events_result = $stmt_events->get_result();

$events = [];
while ($row = $events_result->fetch_assoc()) {
    $date_key = date('Y-m-d', strtotime($row['start_time']));
    $events[$date_key][] = $row;
}

// ────────────────────────────────────────────────
// Upcoming events (next 30 days)
// ────────────────────────────────────────────────
$upcoming_start = date('Y-m-d 00:00:00');
$upcoming_end   = date('Y-m-d 23:59:59', strtotime('+30 days'));
$sql_upcoming = "
    SELECT m.title, m.start_time, COALESCE(m.category, 'Work') AS category
    FROM meetings m
    LEFT JOIN meeting_attendees ma ON m.id = ma.meeting_id
    WHERE (m.organizer_id = ? OR ma.emp_id = ?)
      AND m.start_time BETWEEN ? AND ?
    ORDER BY m.start_time ASC
    LIMIT 15
";
$stmt_upcoming = $conn->prepare($sql_upcoming);
$stmt_upcoming->bind_param("iiss", $current_user_id, $current_user_id, $upcoming_start, $upcoming_end);
$stmt_upcoming->execute();
$upcoming_events = $stmt_upcoming->get_result()->fetch_all(MYSQLI_ASSOC);

// ────────────────────────────────────────────────
// Category colors
// ────────────────────────────────────────────────
$categories = [
    'Team Events'  => '#d4f4e2',
    'Work'         => '#fff3c4',
    'External'     => '#ffd4d9',
    'Projects'     => '#d4f4ff',
    'Applications' => '#e2d4f4',
    'Design'       => '#d4e2ff'
];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar | Workack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #ff6b6b;
            --light-bg: #f8f9fc;
            --text-muted: #6c757d;
        }
        body { background: var(--light-bg); font-family: 'Segoe UI', sans-serif; padding: 1rem 0; }
        .container { max-width: 1400px; }
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem; }
        .mini-calendar { background: white; border-radius: 8px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; }
        .calendar-date {
            min-height: 80px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 4px;
            background: white;
            position: relative;
            overflow: hidden;
        }
        .calendar-date.today {
            background: var(--primary) !important;
            color: white !important;
            font-weight: bold;
        }
        .calendar-date.other-month { opacity: 0.45; background: #f8f9fa; }
        .event-tag {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.78rem;
            padding: 2px 6px;
            margin: 1px 0;
            border-radius: 4px;
            color: #333;
            cursor: move;
            user-select: none;
            position: relative;
        }
        .event-tag .delete-btn {
            font-size: 0.9rem;
            color: #dc3545;
            cursor: pointer;
            margin-left: 6px;
            opacity: 0.7;
        }
        .event-tag .delete-btn:hover {
            opacity: 1;
            color: #c82333;
        }
        .droppable.hover { background: #fff3cd !important; border-color: #ffc107; }
        .view-btn { border: none; background: none; color: var(--text-muted); padding: 0.4rem 1rem; }
        .view-btn.active { color: var(--primary); font-weight: bold; border-bottom: 2px solid var(--primary); }
        .upcoming-item { padding: 0.6rem 0; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>

<?php include 'include/sidebar.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h3 class="mb-0">Calendar</h3>
        <div>
            <button class="btn btn-outline-secondary me-2"><i class="fas fa-file-export me-1"></i> Export</button>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createEventModal">
                <i class="fas fa-plus me-1"></i> Create
            </button>
        </div>
    </div>

    <?php if ($alert_msg): ?>
    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($alert_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-3">
            <!-- Mini calendar -->
            <div class="mini-calendar mb-4">
                <?php
                $mini_first_day_ts  = mktime(0, 0, 0, $month, 1, $year);
                $mini_days_in_month = date('t', $mini_first_day_ts);
                $mini_first_weekday = date('w', $mini_first_day_ts);
                ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>&view=<?= $view ?>" class="text-decoration-none"><i class="fas fa-chevron-left"></i></a>
                    <span class="fw-bold"><?= date('F Y', $mini_first_day_ts) ?></span>
                    <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>&view=<?= $view ?>" class="text-decoration-none"><i class="fas fa-chevron-right"></i></a>
                </div>
                <div class="calendar-grid fw-bold text-muted small">
                    <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                </div>
                <div class="calendar-grid">
                    <?php
                    $day_num = 1;
                    for ($i = 0; $i < 42; $i++) {
                        if ($i < $mini_first_weekday || $day_num > $mini_days_in_month) {
                            echo '<div class="calendar-date bg-light text-muted"></div>';
                        } else {
                            $class = ($day_num == date('d') && $month == date('m') && $year == date('Y')) ? 'today' : '';
                            echo "<div class='calendar-date $class'>$day_num</div>";
                            $day_num++;
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Upcoming events -->
            <h6 class="mt-4 mb-3">Upcoming Events <span class="badge bg-success rounded-pill"><?= count($upcoming_events) ?></span></h6>
            <ul class="upcoming-list list-unstyled">
                <?php foreach ($upcoming_events as $ue): ?>
                    <li class="upcoming-item">
                        <strong><?= htmlspecialchars($ue['title']) ?></strong><br>
                        <small class="text-muted">
                            <?= date('d M Y', strtotime($ue['start_time'])) ?>
                        </small>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($upcoming_events)): ?>
                    <li class="text-muted small">No upcoming events</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="col-md-9">
            <!-- Header with navigation buttons -->
            <div class="calendar-header">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-outline-secondary btn-sm" onclick="goToToday()">Today</button>
                    <span class="fw-bold fs-5">
                        <?php
                        if ($view == 'month') {
                            echo date('F Y', mktime(0,0,0,$month,1,$year));
                        } elseif ($view == 'week') {
                            $week_start = date('M j', strtotime($range_start));
                            $week_end   = date('M j, Y', strtotime($range_end));
                            echo "$week_start – $week_end";
                        } else {
                            echo date('F j, Y', mktime(0,0,0,$month,$day,$year));
                        }
                        ?>
                    </span>
                </div>
                <div>
                    <button class="view-btn <?= $view=='month'?'active':'' ?>" data-view="month">Month</button>
                    <button class="view-btn <?= $view=='week'?'active':'' ?>" data-view="week">Week</button>
                    <button class="view-btn <?= $view=='day'?'active':'' ?>" data-view="day">Day</button>
                </div>
            </div>

            <!-- Month view -->
            <?php if ($view == 'month'): ?>
            <div class="calendar-grid fw-bold text-muted mb-2">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>
            <div class="calendar-grid" id="calendar-grid">
                <?php
                $day = 1;
                for ($i = 0; $i < 42; $i++) {
                    if ($i < $first_weekday || $day > $days_in_month) {
                        echo '<div class="calendar-date bg-light text-muted droppable" data-date=""></div>';
                    } else {
                        $date_key = sprintf("%04d-%02d-%02d", $year, $month, $day);
                        $class = 'calendar-date droppable';
                        if ($date_key == $today) $class .= ' today';
                        echo "<div class='$class' data-date='$date_key'>";
                        echo "<small class='fw-bold'>$day</small>";
                        if (isset($events[$date_key])) {
                            foreach ($events[$date_key] as $event) {
                                $cat_color = $categories[$event['category']] ?? '#e9ecef';
                                echo "<div class='event-tag draggable' draggable='true' data-id='{$event['id']}' style='background: $cat_color;'>";
                                echo htmlspecialchars($event['title']);
                                echo "<span class='delete-btn' data-id='{$event['id']}' title='Delete event'><i class='fas fa-trash-alt'></i></span>";
                                echo "</div>";
                            }
                        }
                        echo "</div>";
                        $day++;
                    }
                }
                ?>
            </div>
            <?php endif; ?>

            <!-- Week & Day view -->
            <?php if ($view == 'week' || $view == 'day'): ?>
            <div class="border rounded p-3 bg-white">
                <?php
                $start = $view == 'day' ? mktime(0,0,0,$month,$day,$year) : strtotime($range_start);
                $days_count = $view == 'day' ? 1 : 7;
                for ($d = 0; $d < $days_count; $d++) {
                    $ts = $start + $d * 86400;
                    $date = date('Y-m-d', $ts);
                    $label = date('D, M j', $ts);
                    $class = ($date == $today) ? 'today' : '';
                    ?>
                    <div class="mb-4">
                        <div class="fw-bold <?= $class ?> p-2 rounded mb-2"><?= $label ?></div>
                        <div class="calendar-date droppable p-3 border rounded" data-date="<?= $date ?>">
                            <?php
                            if (isset($events[$date])) {
                                foreach ($events[$date] as $ev) {
                                    $color = $categories[$ev['category']] ?? '#e9ecef';
                                    $time = date('H:i', strtotime($ev['start_time']));
                                    echo "<div class='event-tag draggable mb-2' draggable='true' data-id='{$ev['id']}' style='background:$color;'>";
                                    echo "<div class='d-flex align-items-center flex-grow-1'>";
                                    echo "<small class='me-2'>$time</small> " . htmlspecialchars($ev['title']);
                                    echo "</div>";
                                    echo "<span class='delete-btn' data-id='{$ev['id']}' title='Delete event'><i class='fas fa-trash-alt'></i></span>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<small class='text-muted'>No events</small>";
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Event Name <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Event Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="row mb-3 g-2">
                        <div class="col-6">
                            <label class="form-label fw-medium">Start Time</label>
                            <input type="time" name="start_time" class="form-control" value="09:00">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium">End Time</label>
                            <input type="time" name="end_time" class="form-control" value="10:00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Location</label>
                        <input type="text" name="location" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Category</label>
                        <select name="category" class="form-select">
                            <?php foreach ($categories as $cat => $color): ?>
                                <option value="<?= $cat ?>"><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_event" class="btn btn-danger">Add Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Go to today
function goToToday() {
    window.location.href = '?view=<?= $view ?>&month=<?= date('n') ?>&year=<?= date('Y') ?>&day=<?= date('j') ?>';
}

// View switching
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const view = btn.getAttribute('data-view');
        const params = new URLSearchParams(window.location.search);
        params.set('view', view);
        // Keep current date context when switching views
        window.location.href = '?' + params.toString();
    });
});

// Drag & Drop
document.querySelectorAll('.draggable').forEach(el => {
    el.addEventListener('dragstart', e => {
        e.dataTransfer.setData('text/plain', el.dataset.id);
    });
});

document.querySelectorAll('.droppable').forEach(cell => {
    cell.addEventListener('dragover', e => {
        e.preventDefault();
        cell.classList.add('hover');
    });
    cell.addEventListener('dragleave', () => cell.classList.remove('hover'));
    cell.addEventListener('drop', e => {
        e.preventDefault();
        cell.classList.remove('hover');
        const eventId = e.dataTransfer.getData('text/plain');
        const newDate = cell.dataset.date;

        if (eventId && newDate) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=move_event&event_id=${eventId}&new_date=${newDate}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) location.reload();
                else alert('Failed to move event');
            });
        }
    });
});

// Delete event
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const eventId = this.dataset.id;
        if (confirm('Are you sure you want to delete this event?')) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_event&event_id=${eventId}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete event');
                }
            });
        }
    });
});
</script>
</body>
</html>

<?php
$stmt_events->close();
$stmt_upcoming->close();
$conn->close();
?>