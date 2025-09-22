<?php
$pageTitle = 'Feedback';
require_once '../includes/header.php';
require_login();

$user_role = getUserRole();
$success = '';
$error = '';

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'submit_feedback') {
        $category = trim($_POST['category']);
        $subject = trim($_POST['subject']);
        $message = trim($_POST['message']);
        $rating = (int)$_POST['rating'];
        $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
        
        if (empty($category) || empty($subject) || empty($message)) {
            $error = 'Please fill in all required fields.';
        } elseif ($rating < 1 || $rating > 5) {
            $error = 'Please provide a valid rating between 1 and 5.';
        } else {
            try {
                $db->query('INSERT INTO feedback (user_id, category, subject, message, rating, is_anonymous, status) 
                           VALUES (:user_id, :category, :subject, :message, :rating, :is_anonymous, "pending")');
                $db->bind(':user_id', getUserId());
                $db->bind(':category', $category);
                $db->bind(':subject', $subject);
                $db->bind(':message', $message);
                $db->bind(':rating', $rating);
                $db->bind(':is_anonymous', $is_anonymous);
                
                if ($db->execute()) {
                    $success = 'Thank you for your feedback! We will review it shortly.';
                } else {
                    $error = 'Failed to submit feedback. Please try again.';
                }
            } catch (Exception $e) {
                $error = 'Database error occurred. Please try again.';
            }
        }
    }
}

// Get user's feedback history (if not admin viewing all)
$my_feedback = [];
$all_feedback = [];

try {
    if ($user_role === 'admin' || $user_role === 'staff') {
        // Get all feedback for admin/staff
        $db->query('SELECT f.*, u.full_name as user_name, u.department 
                   FROM feedback f 
                   LEFT JOIN users u ON f.user_id = u.id 
                   ORDER BY f.created_at DESC');
        $all_feedback = $db->resultSet();
    } else {
        // Get user's own feedback
        $db->query('SELECT * FROM feedback WHERE user_id = :user_id ORDER BY created_at DESC');
        $db->bind(':user_id', getUserId());
        $my_feedback = $db->resultSet();
    }
} catch (Exception $e) {
    $error = 'Unable to load feedback data.';
}

// Feedback categories
$categories = [
    'general' => ['name' => 'General', 'icon' => 'comment-text'],
    'academic' => ['name' => 'Academic', 'icon' => 'school'],
    'transport' => ['name' => 'Transport', 'icon' => 'bus'],
    'facilities' => ['name' => 'Facilities', 'icon' => 'office-building'],
    'food' => ['name' => 'Food Services', 'icon' => 'food'],
    'other' => ['name' => 'Other', 'icon' => 'help-circle']
];
?>

<section class="section">
    <div class="container">
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <div>
                        <h1 class="title is-2">
                            <i class="mdi mdi-comment-text-multiple has-text-success"></i> 
                            Feedback System
                        </h1>
                        <p class="subtitle is-5">
                            <?php if ($user_role === 'admin' || $user_role === 'staff'): ?>
                                Manage and respond to user feedback
                            <?php else: ?>
                                Share your thoughts and help us improve
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <?php if ($user_role !== 'admin' && $user_role !== 'staff'): ?>
                        <button class="button is-success" id="newFeedbackBtn">
                            <i class="mdi mdi-plus"></i>&nbsp; Submit New Feedback
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="notification is-success is-light">
                <button class="delete"></button>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="notification is-danger is-light">
                <button class="delete"></button>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($user_role === 'admin' || $user_role === 'staff'): ?>
            <!-- Admin/Staff View - All Feedback -->
            <div class="box">
                <div class="level">
                    <div class="level-left">
                        <div class="level-item">
                            <h3 class="title is-4">
                                <i class="mdi mdi-format-list-bulleted"></i> All Feedback
                            </h3>
                        </div>
                    </div>
                    <div class="level-right">
                        <div class="level-item">
                            <div class="field has-addons">
                                <div class="control">
                                    <div class="select">
                                        <select id="statusFilter">
                                            <option value="">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="reviewed">Reviewed</option>
                                            <option value="resolved">Resolved</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="control">
                                    <div class="select">
                                        <select id="categoryFilter">
                                            <option value="">All Categories</option>
                                            <?php foreach ($categories as $key => $cat): ?>
                                                <option value="<?php echo $key; ?>"><?php echo $cat['name']; ?></option>
                                            <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Feedback Submission Modal -->
<?php if ($user_role !== 'admin' && $user_role !== 'staff'): ?>
<div class="modal" id="feedbackModal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">
                <i class="mdi mdi-comment-plus"></i> Submit Feedback
            </p>
            <button class="delete" aria-label="close"></button>
        </header>
        <section class="modal-card-body">
            <form id="feedbackForm" method="POST" action="">
                <input type="hidden" name="action" value="submit_feedback">
                
                <div class="field">
                    <label class="label">Category <span class="has-text-danger">*</span></label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="category" id="feedbackCategory" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $key => $category): ?>
                                    <option value="<?php echo $key; ?>">
                                        <?php echo $category['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Subject <span class="has-text-danger">*</span></label>
                    <div class="control">
                        <input class="input" type="text" name="subject" placeholder="Brief subject of your feedback" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Message <span class="has-text-danger">*</span></label>
                    <div class="control">
                        <textarea class="textarea" name="message" placeholder="Describe your feedback in detail..." rows="5" required></textarea>
                    </div>
                    <p class="help">Please provide as much detail as possible to help us understand and address your feedback.</p>
                </div>

                <div class="field">
                    <label class="label">Rating <span class="has-text-danger">*</span></label>
                    <div class="control">
                        <div class="field has-addons">
                            <div class="control">
                                <div class="select">
                                    <select name="rating" required>
                                        <option value="">Rate your experience</option>
                                        <option value="5">⭐⭐⭐⭐⭐ Excellent (5)</option>
                                        <option value="4">⭐⭐⭐⭐ Good (4)</option>
                                        <option value="3">⭐⭐⭐ Average (3)</option>
                                        <option value="2">⭐⭐ Poor (2)</option>
                                        <option value="1">⭐ Very Poor (1)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" name="is_anonymous">
                            Submit anonymously
                        </label>
                    </div>
                    <p class="help">When anonymous, your name and details won't be visible to administrators.</p>
                </div>
            </form>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" form="feedbackForm" type="submit">
                <i class="mdi mdi-send"></i>&nbsp; Submit Feedback
            </button>
            <button class="button" id="cancelFeedback">Cancel</button>
        </footer>
    </div>
</div>
<?php endif; ?>

<!-- View Feedback Modal (for admin) -->
<?php if ($user_role === 'admin' || $user_role === 'staff'): ?>
<div class="modal" id="viewFeedbackModal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">
                <i class="mdi mdi-eye"></i> Feedback Details
            </p>
            <button class="delete" aria-label="close"></button>
        </header>
        <section class="modal-card-body">
            <div id="feedbackDetails"></div>
            
            <div class="field mt-4">
                <label class="label">Admin Response</label>
                <div class="control">
                    <textarea class="textarea" id="adminResponse" placeholder="Add your response to this feedback..."></textarea>
                </div>
            </div>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-primary" id="saveResponseBtn">
                <i class="mdi mdi-content-save"></i>&nbsp; Save Response
            </button>
            <button class="button" id="closeFeedbackView">Close</button>
        </footer>
    </div>
</div>

<!-- Resolve Feedback Modal -->
<div class="modal" id="resolveFeedbackModal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">
                <i class="mdi mdi-check-circle"></i> Resolve Feedback
            </p>
            <button class="delete" aria-label="close"></button>
        </header>
        <section class="modal-card-body">
            <div class="notification is-info is-light">
                <p>Mark this feedback as resolved? This action indicates that the issue has been addressed.</p>
            </div>
            
            <div class="field">
                <label class="label">Resolution Notes</label>
                <div class="control">
                    <textarea class="textarea" id="resolutionNotes" placeholder="Add notes about how this feedback was resolved..."></textarea>
                </div>
            </div>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" id="confirmResolveBtn">
                <i class="mdi mdi-check"></i>&nbsp; Mark as Resolved
            </button>
            <button class="button" id="cancelResolve">Cancel</button>
        </footer>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($user_role !== 'admin' && $user_role !== 'staff'): ?>
    // Feedback submission modal
    const feedbackModal = document.getElementById('feedbackModal');
    const newFeedbackBtn = document.getElementById('newFeedbackBtn');
    const submitFeedbackBtn = document.getElementById('submitFeedbackBtn');
    const cancelFeedbackBtn = document.getElementById('cancelFeedback');
    const categoryCards = document.querySelectorAll('.category-card');

    function openFeedbackModal(category = '') {
        if (category) {
            document.getElementById('feedbackCategory').value = category;
        }
        feedbackModal.classList.add('is-active');
    }

    function closeFeedbackModal() {
        feedbackModal.classList.remove('is-active');
        document.getElementById('feedbackForm').reset();
    }

    newFeedbackBtn?.addEventListener('click', () => openFeedbackModal());
    submitFeedbackBtn?.addEventListener('click', () => openFeedbackModal());
    cancelFeedbackBtn?.addEventListener('click', closeFeedbackModal);
    feedbackModal?.querySelector('.delete').addEventListener('click', closeFeedbackModal);
    feedbackModal?.querySelector('.modal-background').addEventListener('click', closeFeedbackModal);

    // Category card interactions
    categoryCards.forEach(card => {
        card.addEventListener('click', function() {
            const category = this.dataset.category;
            openFeedbackModal(category);
        });

        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 8px 16px rgba(0,0,0,0.1)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });

    // Form validation
    document.getElementById('feedbackForm')?.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.classList.add('is-loading');
            submitBtn.disabled = true;
        }
    });

    <?php else: ?>
    // Admin view functionality
    const viewFeedbackModal = document.getElementById('viewFeedbackModal');
    const resolveFeedbackModal = document.getElementById('resolveFeedbackModal');
    const viewButtons = document.querySelectorAll('.view-feedback-btn');
    const resolveButtons = document.querySelectorAll('.resolve-feedback-btn');
    
    let currentFeedbackId = null;

    // View feedback details
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const feedbackData = JSON.parse(this.dataset.feedback);
            showFeedbackDetails(feedbackData);
        });
    });

    function showFeedbackDetails(feedback) {
        currentFeedbackId = feedback.id;
        
        const categories = <?php echo json_encode($categories); ?>;
        const detailsHtml = `
            <div class="content">
                <div class="level">
                    <div class="level-left">
                        <div class="level-item">
                            <div>
                                <h5 class="title is-5">${feedback.subject}</h5>
                                <p class="subtitle is-6">
                                    <span class="tag is-info">
                                        <i class="mdi mdi-${categories[feedback.category].icon}"></i>&nbsp;
                                        ${categories[feedback.category].name}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="level-right">
                        <div class="level-item">
                            <div class="stars">
                                ${Array.from({length: 5}, (_, i) => 
                                    `<i class="mdi mdi-star${i < feedback.rating ? '' : '-outline'} 
                                            has-text-${i < feedback.rating ? 'warning' : 'grey-light'}"></i>`
                                ).join('')}
                            </div>
                            <span class="ml-2">(${feedback.rating}/5)</span>
                        </div>
                    </div>
                </div>
                
                <div class="box has-background-light">
                    <h6 class="title is-6">Message:</h6>
                    <p>${feedback.message.replace(/\n/g, '<br>')}</p>
                </div>
                
                <div class="columns">
                    <div class="column">
                        <p><strong>Submitted by:</strong> 
                            ${feedback.is_anonymous ? 'Anonymous User' : feedback.user_name}
                        </p>
                        ${!feedback.is_anonymous ? `<p><strong>Department:</strong> ${feedback.department}</p>` : ''}
                    </div>
                    <div class="column">
                        <p><strong>Status:</strong> 
                            <span class="tag is-${feedback.status === 'pending' ? 'warning' : 
                                                  feedback.status === 'resolved' ? 'success' : 'info'}">
                                ${feedback.status.charAt(0).toUpperCase() + feedback.status.slice(1)}
                            </span>
                        </p>
                        <p><strong>Submitted:</strong> ${new Date(feedback.created_at).toLocaleString()}</p>
                    </div>
                </div>
            </div>
        `;
        
        document.getElementById('feedbackDetails').innerHTML = detailsHtml;
        document.getElementById('adminResponse').value = feedback.admin_response || '';
        viewFeedbackModal.classList.add('is-active');
    }

    // Resolve feedback
    resolveButtons.forEach(button => {
        button.addEventListener('click', function() {
            currentFeedbackId = this.dataset.feedbackId;
            resolveFeedbackModal.classList.add('is-active');
        });
    });

    // Modal close handlers
    function closeModals() {
        viewFeedbackModal.classList.remove('is-active');
        resolveFeedbackModal.classList.remove('is-active');
        currentFeedbackId = null;
    }

    document.getElementById('closeFeedbackView')?.addEventListener('click', closeModals);
    document.getElementById('cancelResolve')?.addEventListener('click', closeModals);
    
    document.querySelectorAll('.modal .delete, .modal-background').forEach(element => {
        element.addEventListener('click', closeModals);
    });

    // Save admin response
    document.getElementById('saveResponseBtn')?.addEventListener('click', function() {
        const response = document.getElementById('adminResponse').value;
        if (currentFeedbackId && response.trim()) {
            saveFeedbackResponse(currentFeedbackId, response);
        } else {
            showNotification('Please enter a response message.', 'warning');
        }
    });

    // Confirm resolve feedback
    document.getElementById('confirmResolveBtn')?.addEventListener('click', function() {
        const notes = document.getElementById('resolutionNotes').value;
        if (currentFeedbackId) {
            resolveFeedback(currentFeedbackId, notes);
        }
    });

    // Filter functionality
    const statusFilter = document.getElementById('statusFilter');
    const categoryFilter = document.getElementById('categoryFilter');

    function filterFeedback() {
        const statusValue = statusFilter?.value || '';
        const categoryValue = categoryFilter?.value || '';
        const rows = document.querySelectorAll('.feedback-row');

        rows.forEach(row => {
            const rowStatus = row.dataset.status;
            const rowCategory = row.dataset.category;

            const statusMatch = !statusValue || rowStatus === statusValue;
            const categoryMatch = !categoryValue || rowCategory === categoryValue;

            if (statusMatch && categoryMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    statusFilter?.addEventListener('change', filterFeedback);
    categoryFilter?.addEventListener('change', filterFeedback);

    // AJAX functions for admin actions
    function saveFeedbackResponse(feedbackId, response) {
        fetch('feedback.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=save_response&feedback_id=${feedbackId}&response=${encodeURIComponent(response)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Response saved successfully!', 'success');
                closeModals();
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Failed to save response: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            showNotification('Error saving response: ' + error.message, 'danger');
        });
    }

    function resolveFeedback(feedbackId, notes) {
        fetch('feedback.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=resolve_feedback&feedback_id=${feedbackId}&notes=${encodeURIComponent(notes)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Feedback marked as resolved!', 'success');
                closeModals();
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Failed to resolve feedback: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            showNotification('Error resolving feedback: ' + error.message, 'danger');
        });
    }
    <?php endif; ?>

    // Statistics animation
    function animateStats() {
        const statElements = document.querySelectorAll('.title.is-3');
        statElements.forEach(el => {
            const finalValue = parseInt(el.textContent);
            let currentValue = 0;
            const increment = Math.ceil(finalValue / 50);
            
            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= finalValue) {
                    currentValue = finalValue;
                    clearInterval(timer);
                }
                el.textContent = currentValue;
            }, 30);
        });
    }

    // Trigger stats animation on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateStats();
                observer.disconnect();
            }
        });
    });

    const statsSection = document.querySelector('.title.is-3');
    if (statsSection) {
        observer.observe(statsSection);
    }

    // Auto-refresh for admin view
    <?php if ($user_role === 'admin' || $user_role === 'staff'): ?>
    setInterval(() => {
        if (document.hidden === false && !document.querySelector('.modal.is-active')) {
            // Silently check for new feedback
            fetch('feedback.php?check_new=1')
                .then(response => response.json())
                .then(data => {
                    if (data.new_feedback > 0) {
                        showNotification(`${data.new_feedback} new feedback submission(s) received!`, 'info');
                    }
                })
                .catch(error => console.log('Auto-refresh failed:', error));
        }
    }, 60000); // Check every minute
    <?php endif; ?>

    // Enhanced form validation with real-time feedback
    const form = document.getElementById('feedbackForm');
    if (form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('is-danger');
                } else {
                    this.classList.remove('is-danger');
                    this.classList.add('is-success');
                }
            });

            input.addEventListener('input', function() {
                if (this.classList.contains('is-danger') && this.value.trim() !== '') {
                    this.classList.remove('is-danger');
                    this.classList.add('is-success');
                }
            });
        });
    }

    // Export functionality for admin
    <?php if ($user_role === 'admin' || $user_role === 'staff'): ?>
    function exportFeedback() {
        const feedback = <?php echo json_encode($all_feedback); ?>;
        if (feedback.length === 0) {
            showNotification('No feedback to export.', 'info');
            return;
        }
        
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "User,Category,Subject,Message,Rating,Status,Anonymous,Date,Admin Response\n";
        
        feedback.forEach(f => {
            csvContent += `"${f.is_anonymous ? 'Anonymous' : f.user_name}","${f.category}","${f.subject}","${f.message.replace(/"/g, '""')}","${f.rating}","${f.status}","${f.is_anonymous ? 'Yes' : 'No'}","${f.created_at}","${(f.admin_response || '').replace(/"/g, '""')}"\n`;
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `feedback_export_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Add export button
    const exportBtn = document.createElement('button');
    exportBtn.className = 'button is-info is-outlined';
    exportBtn.innerHTML = '<i class="mdi mdi-download"></i>&nbsp; Export CSV';
    exportBtn.onclick = exportFeedback;
    
    const levelRight = document.querySelector('.level-right .level-item');
    if (levelRight) {
        levelRight.appendChild(exportBtn);
    }
    <?php endif; ?>
});

// Handle additional admin actions via AJAX
<?php if ($user_role === 'admin' || $user_role === 'staff'): ?>
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'save_response':
                $feedback_id = (int)$_POST['feedback_id'];
                $response = trim($_POST['response']);
                
                $db->query('UPDATE feedback SET admin_response = :response, status = "reviewed", updated_at = NOW() WHERE id = :id');
                $db->bind(':response', $response);
                $db->bind(':id', $feedback_id);
                
                if ($db->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Response saved successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to save response']);
                }
                break;
                
            case 'resolve_feedback':
                $feedback_id = (int)$_POST['feedback_id'];
                $notes = trim($_POST['notes']);
                
                $currentResponse = '';
                if ($notes) {
                    $db->query('SELECT admin_response FROM feedback WHERE id = :id');
                    $db->bind(':id', $feedback_id);
                    $current = $db->single();
                    $currentResponse = $current->admin_response . ($current->admin_response ? "\n\n" : '') . "Resolution Notes: " . $notes;
                }

                $db->query('UPDATE feedback SET status = "resolved", admin_response = :response, updated_at = NOW() WHERE id = :id');
                $db->bind(':response', $currentResponse);
                $db->bind(':id', $feedback_id);
                
                if ($db->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Feedback marked as resolved']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to resolve feedback']);
                }
                break;
                
            case 'check_new':
                // Count pending feedback from last check (you'd need to implement session tracking)
                $db->query('SELECT COUNT(*) as count FROM feedback WHERE status = "pending" AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)');
                $result = $db->single();
                echo json_encode(['new_feedback' => $result->count]);
                break;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
    exit();
}
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (empty($all_feedback)): ?>
                    <div class="notification is-info is-light">
                        <i class="mdi mdi-information"></i> No feedback submissions yet.
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table is-fullwidth is-striped is-hoverable">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Category</th>
                                    <th>Subject</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_feedback as $feedback): ?>
                                    <tr class="feedback-row" 
                                        data-status="<?php echo $feedback->status; ?>" 
                                        data-category="<?php echo $feedback->category; ?>">
                                        <td>
                                            <?php if ($feedback->is_anonymous): ?>
                                                <span class="tag is-light">Anonymous</span>
                                            <?php else: ?>
                                                <div>
                                                    <p class="has-text-weight-semibold">
                                                        <?php echo htmlspecialchars($feedback->user_name); ?>
                                                    </p>
                                                    <p class="is-size-7 has-text-grey">
                                                        <?php echo htmlspecialchars($feedback->department); ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="tag is-info">
                                                <i class="mdi mdi-<?php echo $categories[$feedback->category]['icon']; ?>"></i>&nbsp;
                                                <?php echo $categories[$feedback->category]['name']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <p class="has-text-weight-semibold">
                                                <?php echo htmlspecialchars($feedback->subject); ?>
                                            </p>
                                            <p class="is-size-7 has-text-grey" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?php echo htmlspecialchars($feedback->message); ?>
                                            </p>
                                        </td>
                                        <td>
                                            <div class="stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="mdi mdi-star<?php echo $i <= $feedback->rating ? '' : '-outline'; ?> 
                                                              has-text-<?php echo $i <= $feedback->rating ? 'warning' : 'grey-light'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="is-size-7">(<?php echo $feedback->rating; ?>/5)</span>
                                        </td>
                                        <td>
                                            <span class="tag is-<?php 
                                                echo $feedback->status === 'pending' ? 'warning' : 
                                                    ($feedback->status === 'resolved' ? 'success' : 'info'); 
                                            ?>">
                                                <?php echo ucfirst($feedback->status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <p><?php echo date('M j, Y', strtotime($feedback->created_at)); ?></p>
                                            <p class="is-size-7 has-text-grey">
                                                <?php echo date('g:i A', strtotime($feedback->created_at)); ?>
                                            </p>
                                        </td>
                                        <td>
                                            <div class="buttons are-small">
                                                <button class="button is-info is-outlined view-feedback-btn" 
                                                        data-feedback='<?php echo json_encode($feedback); ?>'>
                                                    <i class="mdi mdi-eye"></i>&nbsp; View
                                                </button>
                                                <?php if ($feedback->status !== 'resolved'): ?>
                                                    <button class="button is-success is-outlined resolve-feedback-btn" 
                                                            data-feedback-id="<?php echo $feedback->id; ?>">
                                                        <i class="mdi mdi-check"></i>&nbsp; Resolve
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Regular User View -->
            <div class="columns">
                <!-- Feedback Categories -->
                <div class="column is-one-third">
                    <div class="box">
                        <h4 class="title is-4">
                            <i class="mdi mdi-view-grid"></i> Feedback Categories
                        </h4>
                        
                        <div class="columns is-multiline">
                            <?php foreach ($categories as $key => $category): ?>
                                <div class="column is-half">
                                    <div class="box category-card has-text-centered" 
                                         data-category="<?php echo $key; ?>"
                                         style="cursor: pointer; transition: transform 0.2s;">
                                        <i class="mdi mdi-<?php echo $category['icon']; ?> is-size-2 has-text-info mb-2"></i>
                                        <p class="has-text-weight-semibold"><?php echo $category['name']; ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="has-text-centered mt-4">
                            <button class="button is-success is-fullwidth" id="submitFeedbackBtn">
                                <i class="mdi mdi-comment-plus"></i>&nbsp; Submit Feedback
                            </button>
                        </div>
                    </div>
                </div>

                <!-- My Feedback History -->
                <div class="column is-two-thirds">
                    <div class="box">
                        <h4 class="title is-4">
                            <i class="mdi mdi-history"></i> My Feedback History
                        </h4>
                        
                        <?php if (empty($my_feedback)): ?>
                            <div class="notification is-info is-light">
                                <i class="mdi mdi-information"></i> You haven't submitted any feedback yet.
                            </div>
                        <?php else: ?>
                            <div style="max-height: 600px; overflow-y: auto;">
                                <?php foreach ($my_feedback as $feedback): ?>
                                    <div class="box">
                                        <div class="level">
                                            <div class="level-left">
                                                <div class="level-item">
                                                    <div>
                                                        <p class="title is-6">
                                                            <?php echo htmlspecialchars($feedback->subject); ?>
                                                        </p>
                                                        <p class="subtitle is-7">
                                                            <span class="tag is-info">
                                                                <i class="mdi mdi-<?php echo $categories[$feedback->category]['icon']; ?>"></i>&nbsp;
                                                                <?php echo $categories[$feedback->category]['name']; ?>
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="level-right">
                                                <div class="level-item">
                                                    <span class="tag is-<?php 
                                                        echo $feedback->status === 'pending' ? 'warning' : 
                                                            ($feedback->status === 'resolved' ? 'success' : 'info'); 
                                                    ?>">
                                                        <?php echo ucfirst($feedback->status); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="content">
                                            <p><?php echo nl2br(htmlspecialchars($feedback->message)); ?></p>
                                        </div>

                                        <div class="level">
                                            <div class="level-left">
                                                <div class="level-item">
                                                    <div class="stars">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="mdi mdi-star<?php echo $i <= $feedback->rating ? '' : '-outline'; ?> 
                                                                      has-text-<?php echo $i <= $feedback->rating ? 'warning' : 'grey-light'; ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <span class="ml-2">Rating: <?php echo $feedback->rating; ?>/5</span>
                                                </div>
                                            </div>
                                            <div class="level-right">
                                                <div class="level-item">
                                                    <p class="is-size-7 has-text-grey">
                                                        <?php echo $feedback->is_anonymous ? 'Anonymous • ' : ''; ?>
                                                        <?php echo date('M j, Y g:i A', strtotime($feedback->created_at)); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ($feedback->admin_response): ?>
                                            <div class="notification is-info is-light">
                                                <strong>Response:</strong><br>
                                                <?php echo nl2br(htmlspecialchars($feedback->admin_response)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>