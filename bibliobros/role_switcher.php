<?php
// role_switcher.php - Componente reutilizable para cambiar entre roles
// Incluir este archivo en Topsubject_mentor.php y Topsubject_mentee.php

function renderRoleSwitcher($pdo, $userId, $subjectId, $currentRole) {
    // Check if user has both roles
    $stmt = $pdo->prepare("
        SELECT role
        FROM user_subject_role
        WHERE user_id = :uid AND subject_id = :sid
        ORDER BY role
    ");
    $stmt->execute(['uid' => $userId, 'sid' => $subjectId]);
    $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($roles) <= 1) {
        return ''; // No switcher needed if user has only one role
    }
    
    $oppositeRole = $currentRole === 'mentor' ? 'mentee' : 'mentor';
    $oppositeLabel = $currentRole === 'mentor' ? 'Switch to Mentee' : 'Switch to Mentor';
    $oppositeIcon = $currentRole === 'mentor' ? 'fa-user' : 'fa-user-graduate';
    $oppositeColor = $currentRole === 'mentor' ? 'primary' : 'warning';
    
    return '
    <style>
        .role-switcher {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .role-switcher-btn {
            background: white;
            border: 2px solid #' . ($currentRole === 'mentor' ? '007bff' : 'ffc107') . ';
            color: #333;
            padding: 0.75rem 1.25rem;
            border-radius: 50px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .role-switcher-btn:hover {
            background: #' . ($currentRole === 'mentor' ? '007bff' : 'ffc107') . ';
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        
        .current-role-badge {
            background: #' . ($currentRole === 'mentor' ? 'ffc107' : '007bff') . ';
            color: ' . ($currentRole === 'mentor' ? '#333' : 'white') . ';
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .role-switcher {
                bottom: 70px;
                right: 10px;
            }
            
            .role-switcher-btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
    
    <div class="role-switcher">
        <form method="post" action="Topsubject.php">
            <input type="hidden" name="subject_id" value="' . $subjectId . '">
            <input type="hidden" name="action" value="switch_role">
            <button type="submit" name="role" value="' . $oppositeRole . '" class="role-switcher-btn">
                <i class="fas ' . $oppositeIcon . ' me-2"></i>
                ' . $oppositeLabel . '
            </button>
        </form>
    </div>
    
    <script>
        // Add tooltip to role switcher
        document.addEventListener("DOMContentLoaded", function() {
            const switcher = document.querySelector(".role-switcher-btn");
            if (switcher) {
                switcher.setAttribute("data-bs-toggle", "tooltip");
                switcher.setAttribute("data-bs-placement", "left");
                switcher.setAttribute("title", "You can be both mentor and mentee!");
                new bootstrap.Tooltip(switcher);
            }
        });
    </script>
    ';
}

// Function to display current role badge in the header
function renderCurrentRoleBadge($currentRole) {
    $icon = $currentRole === 'mentor' ? 'fa-user-graduate' : 'fa-user';
    $color = $currentRole === 'mentor' ? 'warning' : 'primary';
    
    return '
    <span class="badge bg-' . $color . ' ms-2">
        <i class="fas ' . $icon . ' me-1"></i>
        ' . ucfirst($currentRole) . ' Mode
    </span>
    ';
}

// Function to check if user can add the opposite role
function canAddOppositeRole($pdo, $userId, $subjectId, $currentRole) {
    $oppositeRole = $currentRole === 'mentor' ? 'mentee' : 'mentor';
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM user_subject_role 
        WHERE user_id = :uid AND subject_id = :sid AND role = :role
    ");
    $stmt->execute(['uid' => $userId, 'sid' => $subjectId, 'role' => $oppositeRole]);
    
    return $stmt->fetchColumn() == 0;
}

// Function to render "Add opposite role" prompt
function renderAddRolePrompt($subjectId, $currentRole) {
    if ($currentRole === 'mentor') {
        return '
        <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-lightbulb me-2"></i>
            <strong>Did you know?</strong> You can also be a mentee in this subject if you need help!
            <form method="post" action="Topsubject.php" class="d-inline ms-3">
                <input type="hidden" name="subject_id" value="' . $subjectId . '">
                <input type="hidden" name="action" value="add_role">
                <button type="submit" name="role" value="mentee" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Mentee Role
                </button>
            </form>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        ';
    } else {
        return '
        <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-lightbulb me-2"></i>
            <strong>Want to help others?</strong> You can also become a mentor in this subject!
            <form method="post" action="Topsubject.php" class="d-inline ms-3">
                <input type="hidden" name="subject_id" value="' . $subjectId . '">
                <input type="hidden" name="action" value="add_role">
                <button type="submit" name="role" value="mentor" class="btn btn-sm btn-warning">
                    <i class="fas fa-plus me-1"></i>Become a Mentor
                </button>
            </form>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        ';
    }
}
?>