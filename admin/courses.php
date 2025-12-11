<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
$result = $db->query($query);   
$totalStudents = $result->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM registrations WHERE payment_status = 'pending'";
$result = $db->query($query);
$pendingApprovals = $result->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM courses";
$result = $db->query($query);
$activeCourses = $result->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT SUM(amount) as total FROM payments WHERE status = 'completed'";
$result = $db->query($query);
$totalRevenue = $result->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$query = "SELECT COUNT(*) as total FROM users WHERE role = 'formateur'";
$result = $db->query($query);
$totalFormateurs = $result->fetch(PDO::FETCH_ASSOC)['total'];

// Get user's enrolled courses
$user_id = $_SESSION['user_id'];
$query = "SELECT course_id FROM user_courses";
$result_enrolled = $db->query($query);
$enrolled_courses = $result_enrolled->fetchAll(PDO::FETCH_COLUMN, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
 <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Lusitana:wght@400;700&display=swap" rel="stylesheet">     
    <title>Courses - Master Edu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

          :root {
                --bg-primary: #14002E;
                --bg-secondary: #220547;
                --bg-tertiary: #2b0f50;
                --bg-card: #2A2050;
                --bg-card1: #473e70;
                --bg-card-hover: #443a66;
                --text-primary: #E0D9FF;
                --text-secondary: #BFB6D9;
                --btn-bg: #9DFF57;
                --btn-text: #14002E;
                --btn-hover: #8BED4A;
                --sidebar-width: 260px;
            }
            
            .light-mode {
                --bg-primary: #f8f9fa;
                --bg-secondary: #BFB6D9;
                --bg-tertiary: #b4a8d8ff;
                --bg-card1: #aea0d8ff;
                --bg-card: #BFB6D9;
                --bg-card-hover: #e9ecef;
                --text-primary: #212529;
                --text-secondary: #495057;
                --btn-bg: #9DFF57;
                --btn-text: #14002E;
                --btn-hover: #8BED4A;
            }

      body {
                background: var(--bg-primary);
                color: var(--text-primary);
                font-family: "Lusitana", serif;
                display: flex;
                min-height: 100vh;
            }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 20px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: bold;
            color: var(--text-primary);
        }

        .logo-icon {
            width: 30px;
            height: 30px;
            background-color: var(--btn-bg);
            border-radius: 5px;
        }

        .tabs {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--bg-secondary);
            padding-bottom: 10px;
        }

        .tab {
            font-size: 16px;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 10px 5px;
            transition: color 0.3s;
            border-bottom: 2px solid transparent;
            margin-bottom: -12px;
        }

        .tab.active {
            color: var(--btn-bg);
            border-bottom-color: var(--btn-bg);
        }

        .courses-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
    align-items: center;
    width: 70%; 
    margin: 0 auto; 

        }

       /* Update the course card styles */
.course-card {
    background-color: var(--bg-card);
    border-radius: 12px;
    padding: 25px;
    transition: all 0.3s ease;
    border: 1px solid rgba(157, 255, 87, 0.1);
    display: flex;
    flex-direction: column;
    gap: 15px;
    position: relative;

}

.course-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
}

.course-title {
    font-size: 22px;
    font-weight: 600;
    color: var(--text-primary);
    flex: 1;
    margin-bottom: 0;
}

.course-image {
    width: 120px;
    height: 120px;
    border-radius: 8px;
    object-fit: cover;
}

.course-description {
    color: var(--text-secondary);
    line-height: 1.6;
    font-size: 14px;
    margin: 10px 0;
}

.course-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 15px 0;
}

.detail-column {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.detail-label {
    font-size: 12px;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 14px;
    color: var(--text-primary);
    font-weight: 500;
}

.course-dates {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin: 10px 0;
    background: var(--bg-tertiary);
    padding: 15px;
    border-radius: 8px;
}

.date-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.date-label {
    font-size: 11px;
    color: var(--text-secondary);
    text-transform: uppercase;
}

.date-value {
    font-size: 14px;
    color: var(--text-primary);
    font-weight: 500;
}

.course-progress {
    margin: 10px 0;
}

.progress-label {
    display: block;
    font-size: 12px;
    color: var(--text-secondary);
    margin-bottom: 8px;
    text-transform: uppercase;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background-color: var(--bg-tertiary);
    border-radius: 10px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background-color: var(--btn-bg);
    border-radius: 10px;
    transition: width 0.3s ease;
}

.course-action {
    margin-top: 15px;
    text-align: center;
}

.enroll-btn {
    background-color: var(--btn-bg);
    color: var(--btn-text);
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    width: 100%;
}

.enroll-btn:hover {
    background-color: var(--btn-hover);
    transform: scale(1.02);
}

.enroll-btn.enrolled {
    background-color: var(--bg-card1);
    color: var(--text-primary);
    cursor: default;
}

.enroll-btn.enrolled:hover {
    transform: none;
}

/* Course status badge */
.course-status {
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    z-index: 1;
}

.status-upcoming {
    background-color: #ffb74d;
    color: #3e2723;
}

.status-active {
    background-color: var(--btn-bg);
    color: var(--btn-text);
}

.status-completed {
    background-color: #78909c;
    color: white;
}
        .students-info {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .students-label {
            font-size: 11px;
            color: var(--text-secondary);
        }

        .students-count {
            font-size: 16px;
            color: var(--text-primary);
            font-weight: 600;
        }

        .dates-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 12px;
            color: var(--text-secondary);
        }

      
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
 .sidebar {
                width: var(--sidebar-width);
                background: var(--bg-secondary);
                position: fixed;
                height: 100vh;
                left: 0;
                top: 0;
                overflow-y: auto;
                border-right: 1px solid rgba(255, 255, 255, 0.1);
                transition: all 0.3s ease;
                z-index: 1000;
            }
            
            .sidebar-header {
                padding: 24px 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            .logo {
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: 20px;
                font-weight: 700;
                color: var(--text-primary);
            }
            
            .logo svg {
                width: 28px;
                height: 28px;
            }
            
            .sidebar-menu {
                padding: 20px 0;
            }
            
            .menu-item {
                display: flex;
                align-items: center;
                padding: 12px 20px;
                color: var(--text-secondary);
                text-decoration: none;
                transition: all 0.2s;
                cursor: pointer;
            }
            
            .menu-item:hover {
                background: var(--bg-card-hover);
                color: var(--text-primary);
            }
            
            .menu-item.active {
                background: var(--bg-card);
                color: var(--btn-bg);
                border-left: 3px solid var(--btn-bg);
            }
            
            .menu-item i {
                width: 20px;
                margin-right: 12px;
                font-size: 16px;
            }
            
            .menu-item span {
                flex: 1;
            }
            
            .menu-badge {
                background: var(--btn-bg);
                color: var(--btn-text);
                padding: 2px 8px;
                border-radius: 12px;
                font-size: 11px;
                font-weight: 600;
            }
            /* Main Content */
   .main-content {
                margin-left: var(--sidebar-width);
                flex: 1;
                padding: 24px;
                width: calc(100% - var(--sidebar-width));
            }

            .theme-toggle {
                position: fixed;
                bottom: 24px;
                right: 24px;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: transparent;
                color: var(--text-primary);
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                border: none;
                font-size: 20px;
                z-index: 100;
                transition: all 0.3s;
            }
            
            .theme-toggle:hover {
                transform: scale(1.1);
            }
    /* Add this CSS for course status */
        .course-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-upcoming {
            background-color: #ffb74d;
            color: #3e2723;
        }
        
        .status-active {
            background-color: var(--btn-bg);
            color: var(--btn-text);
        }
        
        .status-completed {
            background-color: #78909c;
            color: white;
        }
        
        .course-card {
            position: relative;
            /* rest of your course-card styles */
        }
        
        /* Hide courses when filtering */
        .course-card.hidden {
            display: none;
        }
button{
font-family: "Lusitana", serif;
}
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                </svg>
                <span>Master Edu</span>
            </div>
        </div>
        
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="students.php" class="menu-item active">
                <i class="fas fa-users"></i>
                <span>Students</span>
                <span class="menu-badge"><?php echo $totalStudents;?></span>
            </a>
            <a href="teachers.php" class="menu-item">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Teachers</span>
                <span class="menu-badge"><?php echo $totalFormateurs; ?></span>
            </a>
            <a href="courses.php" class="menu-item">
                <i class="fas fa-book"></i>
                <span>Courses</span>
                <span class="menu-badge"><?php echo $activeCourses; ?></span>
            </a>
            <a href="certifications.php" class="menu-item">
                <i class="fas fa-certificate"></i>
                <span>Certifications</span>
            </a>
            <a href="payments.php" class="menu-item">
                <i class="fas fa-money-bill-wave"></i>
                <span>Payments</span>
            </a>
            <a href="ratings.php" class="menu-item">
                <i class="fas fa-star"></i>
                <span>Ratings</span>
            </a>
            <a href="registrations.php" class="menu-item">
                    <i class="fas fa-user-plus"></i>
                    <span>Registrations</span>
                    <?php if($pendingApprovals > 0): ?>
                    <span class="menu-badge"><?php echo $pendingApprovals; ?></span>
                    <?php endif; ?>
                </a>
                <a href="pending-approvals.php" class="menu-item">
                    <i class="fas fa-clock"></i>
                    <span>Pending Approvals</span>
                    <?php if($pendingApprovals > 0): ?>
                    <span class="menu-badge"><?php echo $pendingApprovals; ?></span>
                    <?php endif; ?>
                </a>
          
            <a href="user-management.php" class="menu-item">
                <i class="fas fa-user-cog"></i>
                <span>User Management</span>
            </a>
           
            <a href="settings.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </nav>
    </aside>
  <main class="main-content">
        <h1 style="font-size: 32px; margin-bottom: 30px;">Courses</h1>

        <div class="tabs">
            <div class="tab active" onclick="filterCourses('all')">All Courses</div>
            <div class="tab" onclick="filterCourses('upcoming')">Upcoming</div>
            <div class="tab" onclick="filterCourses('active')">Active</div>
            <div class="tab" onclick="filterCourses('completed')">Completed</div>
        </div>

        <div class="courses-grid" id="coursesGrid">
            <?php 
            
            $query = "SELECT c.*, 
                      (SELECT COUNT(*) FROM user_courses WHERE course_id = c.id) as registered_students,
                      t.first_name as teacher_firstname,
                      t.last_name as teacher_lastname,
                      CONCAT(t.first_name, ' ', t.last_name) as teacher_name
                      FROM courses c
                      LEFT JOIN users t ON c.formateur_id = t.id
                      ORDER BY c.created_at DESC";
            $result = $db->query($query);
            $courses = $result->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($courses) > 0): 
                foreach ($courses as $course): 
                    $is_enrolled = in_array($course['id'], $enrolled_courses);
                    $start_date = date('d/m/Y', strtotime($course['start_date']));
                    $end_date = date('d/m/Y', strtotime($course['end_date']));
                    $duration = $course['duration_hours'];
                    $image = $course['image_url'] ? $course['image_url'] : 'image.png';
                    $status = strtolower($course['status']);
                    ?>
                  <!-- Replace the course card HTML section with this properly structured version -->
<div class="course-card" data-status="<?php echo $status; ?>">
    <!-- Course status badge -->
    <div class="course-status status-<?php echo $status; ?>">
        <?php echo ucfirst($status); ?>
    </div>
    
    <!-- Course header with image -->
    <div class="course-header">
        <div class="course-title"><?php echo htmlspecialchars($course['title']); ?></div>

    </div>

    <!-- Course description -->
    <div class="course-description">
        <?php echo htmlspecialchars($course['description']); ?>
    </div>

    <!-- Course details in two columns -->
    <div class="course-details">
        <div class="detail-column">
            <div class="detail-item">
                <span class="detail-label">Price</span>
                <span class="detail-value">da<?php echo number_format($course['price'], 2); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Teacher</span>
                <span class="detail-value"><?php echo htmlspecialchars($course['teacher_name']); ?></span>
            </div>
        </div>
        
        <div class="detail-column">
            <div class="detail-item">
                <span class="detail-label">Registered Students</span>
                <span class="detail-value"><?php echo $course['registered_students']; ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Duration</span>
                <span class="detail-value"><?php echo $duration; ?> hours</span>
            </div>
        </div>
    </div>

    <!-- Dates and progress -->
    <div class="course-dates">
        <div class="date-item">
            <span class="date-label">Start:</span>
            <span class="date-value"><?php echo $start_date; ?></span>
        </div>
        <div class="date-item">
            <span class="date-label">End:</span>
            <span class="date-value"><?php echo $end_date; ?></span>
        </div>
    </div>

    <!-- Progress bar -->
    <div class="course-progress">
        <span class="progress-label">Progress</span>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?php echo $is_enrolled ? rand(20, 80) : 0; ?>%"></div>
        </div>
    </div>

    <!-- Enroll button -->
    <div class="course-action">
        <?php if ($is_enrolled): ?>
            <button class="enroll-btn enrolled">Enrolled</button>
        <?php else: ?>
            <button class="enroll-btn" onclick="enrollCourse(<?php echo $course['id']; ?>)">Enroll Now</button>
        <?php endif; ?>
    </div>
</div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No courses available</h3>
                    <p>Check back later for new courses</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
   <button class="theme-toggle" id="theme-toggle">
        <i class="fas fa-moon"></i>
    </button>
       <script>
        function filterCourses(filter) {
        
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
          
            tabs.forEach(tab => {
                if (tab.textContent.toLowerCase().includes(filter.toLowerCase())) {
                    tab.classList.add('active');
                }
            });
            
    
            const courseCards = document.querySelectorAll('.course-card');
            
            courseCards.forEach(card => {
                const status = card.getAttribute('data-status');
                
                if (filter === 'all' || status === filter) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
            
            // Show empty state if no courses are visible
            const visibleCards = document.querySelectorAll('.course-card:not(.hidden)');
            const emptyState = document.querySelector('.empty-state');
            
            if (visibleCards.length === 0 && !emptyState) {
                const coursesGrid = document.getElementById('coursesGrid');
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'empty-state';
                emptyDiv.innerHTML = `
                    <h3>No ${filter} courses available</h3>
                    <p>Check back later for new courses</p>
                `;
                coursesGrid.appendChild(emptyDiv);
            } else if (visibleCards.length > 0 && emptyState) {
                emptyState.remove();
            }
        }
        
        // Theme toggle (same as before)
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = themeToggle.querySelector('i');
        const body = document.body;

        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            body.classList.add('light-mode');
            themeIcon.className = 'fas fa-moon';
        }

        themeToggle.addEventListener('click', function() {
            body.classList.toggle('light-mode');

            if (body.classList.contains('light-mode')) {
                themeIcon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'light');
            } else {
                themeIcon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'dark');
            }
        });
        
       
    </script>
</body>
</html>