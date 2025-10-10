// Teacher Activities Page Functions
function renderActivities(filterType = 'all', filterStatus = 'all') {
    const activityGrid = document.getElementById('activityGrid');
    if (!activityGrid) return;

    const filteredActivities = activitiesData.filter(activity => {
        const statusMatch = (filterStatus === 'all' || activity.status === filterStatus);
        const typeMatch = (filterType === 'all' || activity.type === filterType);
        return statusMatch && typeMatch;
    });

    let activityHtml = '';
    let pendingCount = 0;

    if (filteredActivities.length === 0) {
        activityHtml = '<p style="text-align: center; padding: 50px; color: #7f8c8d;">No activities found matching the current filter criteria.</p>';
    } else {
        filteredActivities.forEach(activity => {
            const pendingGrades = activity.submissions - activity.graded;
            if (activity.status === 'closed' && pendingGrades > 0) {
                pendingCount += pendingGrades;
            }
            
            let statusBadgeClass = activity.status;
            let statusText = activity.status.charAt(0).toUpperCase() + activity.status.slice(1);
            
            let actions = '';

            if (activity.status === 'open') {
                actions = `
                    <button class="action-small-btn view-btn" onclick="gradeActivity(${activity.id}, '${activity.title}')"><i class="fas fa-eye"></i> View Submissions</button>
                    <button class="action-small-btn qr-btn" onclick="generateQRCode(${activity.id}, 'activity', '${activity.title}')"><i class="fas fa-qrcode"></i> Get QR</button>
                    <button class="action-small-btn edit-btn" onclick="editActivity(${activity.id}, '${activity.title}')"><i class="fas fa-edit"></i> Edit Settings</button>
                    <button class="action-small-btn delete-btn" onclick="deleteActivity(${activity.id}, '${activity.title}')"><i class="fas fa-trash-alt"></i> Delete</button>
                `;
            } else if (activity.status === 'draft') {
                 actions = `
                    <button class="action-small-btn edit-btn" onclick="editActivity(${activity.id}, '${activity.title}')"><i class="fas fa-edit"></i> Configure</button>
                    <button class="action-small-btn publish-btn" onclick="customAlert('Publishing Activity ${activity.id}.')"><i class="fas fa-upload"></i> Publish</button>
                    <button class="action-small-btn delete-btn" onclick="deleteActivity(${activity.id}, '${activity.title}')"><i class="fas fa-trash-alt"></i> Delete</button>
                 `;
            } else if (activity.status === 'closed') {
                actions = `
                    <button class="action-small-btn archive-btn" onclick="gradeActivity(${activity.id}, '${activity.title}')"><i class="fas fa-clipboard-check"></i> Grade (${pendingGrades})</button>
                    <button class="action-small-btn qr-btn" onclick="generateQRCode(${activity.id}, 'activity', '${activity.title}')"><i class="fas fa-qrcode"></i> Get QR</button>
                    <button class="action-small-btn delete-btn" onclick="deleteActivity(${activity.id}, '${activity.title}')"><i class="fas fa-trash-alt"></i> Delete/Archive</button>
                `;
            }

            activityHtml += `
                <div class="lesson-card status-${activity.status}">
                    <div class="lesson-header-status">
                        <span class="status-badge ${statusBadgeClass}">${statusText}</span>
                        <i class="fas fa-ellipsis-v action-icon" onclick="customAlert('Options for ${activity.title} activity.')"></i>
                    </div>
                    <h4>${activity.title}</h4>
                    <p class="topic-detail">Type: <strong>${activity.type}</strong> | Lesson: ${activity.relatedLesson}</p>
                    <div class="lesson-metrics">
                        <span><i class="fas fa-calendar-alt"></i> Due: ${activity.dueDate}</span>
                        <span><i class="fas fa-inbox"></i> Submissions: ${activity.submissions} / Graded: ${activity.graded}</span>
                    </div>
                    ${actions}
                </div>
            `;
        });
    }
    
    activityHtml += `
        <div class="lesson-card status-new">
            <div class="lesson-placeholder" onclick="initActivityCreation()">
                <i class="fas fa-seedling"></i>
                <h4>Create New Activity</h4>
                <p>Build a quiz, project, or simulation exercise.</p>
            </div>
        </div>
    `;

    activityGrid.innerHTML = activityHtml;

    const pendingGradesEl = document.getElementById('pendingGradesCount');
    if (pendingGradesEl) {
         const totalPending = activitiesData.reduce((sum, activity) => {
             return activity.status === 'closed' ? sum + (activity.submissions - activity.graded) : sum;
         }, 0);
        pendingGradesEl.textContent = totalPending;
    }
}

function initActivityCreation() {
    showModal('createActivityModal');
    
    const form = document.getElementById('newActivityForm');
    form.onsubmit = function(event) {
        event.preventDefault();
        
        let nextId = activitiesData.length > 0 ? Math.max(...activitiesData.map(a => a.id)) + 1 : 101;
        
        const title = document.getElementById('activityTitle').value;
        const type = document.getElementById('activityType').value;
        const isNoDueDate = document.getElementById('noDueDate').checked;
        const dueDate = isNoDueDate ? 'N/A' : document.getElementById('activityDueDate').value;

        const newActivity = {
            id: nextId,
            title: title,
            type: type,
            status: 'draft',
            submissions: 0,
            graded: 0,
            dueDate: dueDate,
            relatedLesson: 'Unlinked'
        };

        activitiesData.push(newActivity);

        hideModal('createActivityModal');
        editActivity(nextId, title);
        
        renderActivities(document.getElementById('activityTypeFilter')?.value || 'all', document.getElementById('activityStatusFilter')?.value || 'all');
        form.reset();
        document.getElementById('activityDueDate').disabled = false; 
        document.getElementById('noDueDate').checked = false;
    };
}

function editActivity(activityId, title) {
    document.getElementById('configuratorTitle').textContent = `Configurator: ${title} (ID: ${activityId})`;
    customAlert(`Opening configurator for Activity ID ${activityId}: "${title}"`);
    showModal('activityConfiguratorModal');
}

function gradeActivity(activityId, title) {
    customAlert(`Opening grading interface for Activity ID ${activityId}: "${title}"`);
}

function deleteActivity(activityId, title) {
    customAlert(`Deleting activity: ${title} (ID: ${activityId})`);
}

function filterActivities() {
    const typeFilter = document.getElementById('activityTypeFilter').value;
    const statusFilter = document.getElementById('activityStatusFilter').value;
    renderActivities(typeFilter, statusFilter);
    customAlert(`Filtering activities by Type: ${typeFilter} and Status: ${statusFilter}`);
}