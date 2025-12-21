// ===== CRUD OPERATIONS FOR ALL TABLES =====

// ===== PATIENT CRUD =====
async function createPatient(patientData) {
    try {
        const response = await fetch(`${API_BASE_URL}/patient.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(patientData)
        });
        return await response.json();
    } catch (error) {
        console.error('Error creating patient:', error);
        return { status: 'error', message: error.message };
    }
}

async function updatePatient(patientId, patientData) {
    try {
        const response = await fetch(`${API_BASE_URL}/patient.php`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ...patientData, Patient_ID: patientId })
        });
        return await response.json();
    } catch (error) {
        console.error('Error updating patient:', error);
        return { status: 'error', message: error.message };
    }
}

async function deletePatient(patientId) {
    try {
        const response = await fetch(`${API_BASE_URL}/patient.php`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ Patient_ID: patientId })
        });
        return await response.json();
    } catch (error) {
        console.error('Error deleting patient:', error);
        return { status: 'error', message: error.message };
    }
}

// ===== DAILY LOGS CRUD =====
async function createDailyLog(logData) {
    try {
        const response = await fetch(`${API_BASE_URL}/dailylogs.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(logData)
        });
        return await response.json();
    } catch (error) {
        console.error('Error creating daily log:', error);
        return { status: 'error', message: error.message };
    }
}

// ===== MODAL MANAGEMENT =====
let currentModal = null;
let currentTable = null;
let currentEditId = null;

function showModal(tableName, action = 'add', data = null) {
    currentTable = tableName;
    currentEditId = data ? data.id : null;
    
    // Create modal HTML based on table
    const modalHTML = getModalHTML(tableName, action, data);
    
    // Create modal element
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal">
            <div class="modal-header">
                <h3>${action === 'add' ? 'Add New' : 'Edit'} ${getTableTitle(tableName)}</h3>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                ${modalHTML}
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button class="btn-primary" onclick="save${tableName.charAt(0).toUpperCase() + tableName.slice(1)}()">
                    ${action === 'add' ? 'Save' : 'Update'}
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    currentModal = modal;
    
    // Add CSS if not exists
    if (!document.querySelector('#modal-styles')) {
        const style = document.createElement('style');
        style.id = 'modal-styles';
        style.textContent = `
            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            }
            .modal {
                background: var(--dark-card);
                border-radius: 8px;
                width: 500px;
                max-width: 90%;
                max-height: 90vh;
                overflow-y: auto;
                border: 1px solid var(--dark-border);
            }
            .modal-header {
                padding: 20px;
                border-bottom: 1px solid var(--dark-border);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .modal-header h3 {
                margin: 0;
                color: var(--text-primary);
            }
            .close-modal {
                font-size: 24px;
                cursor: pointer;
                color: var(--text-secondary);
            }
            .modal-body {
                padding: 20px;
            }
            .modal-footer {
                padding: 20px;
                border-top: 1px solid var(--dark-border);
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }
            .form-group {
                margin-bottom: 15px;
            }
            .form-group label {
                display: block;
                margin-bottom: 5px;
                color: var(--text-primary);
                font-weight: 500;
            }
            .form-group input,
            .form-group select,
            .form-group textarea {
                width: 100%;
                padding: 8px 12px;
                background: var(--dark-bg);
                border: 1px solid var(--dark-border);
                border-radius: 4px;
                color: var(--text-primary);
                font-size: 14px;
            }
            .btn-primary {
                background: var(--primary);
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 500;
            }
            .btn-secondary {
                background: var(--dark-border);
                color: var(--text-primary);
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 500;
            }
        `;
        document.head.appendChild(style);
    }
}

function closeModal() {
    if (currentModal) {
        currentModal.remove();
        currentModal = null;
        currentTable = null;
        currentEditId = null;
    }
}

function getTableTitle(tableName) {
    const titles = {
        'patient': 'Patient',
        'dailylog': 'Daily Log',
        'counsellor': 'Counselor',
        'session': 'Session',
        'payment': 'Payment',
        'feedback': 'Feedback',
        'analysis': 'AI Analysis',
        'recommendation': 'Recommendation',
        'crisis': 'Crisis Alert',
        'caregiver': 'Caregiver'
    };
    return titles[tableName] || tableName;
}

function getModalHTML(tableName, action, data) {
    const forms = {
        'patient': `
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" id="modal-name" value="${data?.Name || ''}" required>
            </div>
            <div class="form-group">
                <label>Age *</label>
                <input type="number" id="modal-age" value="${data?.Age || ''}" required>
            </div>
            <div class="form-group">
                <label>Gender *</label>
                <select id="modal-gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male" ${data?.Gender === 'Male' ? 'selected' : ''}>Male</option>
                    <option value="Female" ${data?.Gender === 'Female' ? 'selected' : ''}>Female</option>
                    <option value="Other" ${data?.Gender === 'Other' ? 'selected' : ''}>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Contact Number *</label>
                <input type="text" id="modal-contact" value="${data?.Contact || ''}" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" id="modal-email" value="${data?.Email || ''}" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" id="modal-address" value="${data?.address || ''}">
            </div>
            <div class="form-group">
                <label>Emergency Contact *</label>
                <input type="text" id="modal-emergency" value="${data?.emergencyContact || ''}" required>
            </div>
        `,
        
        'dailylog': `
            <div class="form-group">
                <label>Patient *</label>
                <select id="modal-patient-id" required>
                    <option value="">Select Patient</option>
                    <!-- Will be populated with patients -->
                </select>
            </div>
            <div class="form-group">
                <label>Mood *</label>
                <select id="modal-mood" required>
                    <option value="">Select Mood</option>
                    <option value="Very Sad" ${data?.Mood === 'Very Sad' ? 'selected' : ''}>😢 Very Sad</option>
                    <option value="Sad" ${data?.Mood === 'Sad' ? 'selected' : ''}>😔 Sad</option>
                    <option value="Neutral" ${data?.Mood === 'Neutral' ? 'selected' : ''}>😐 Neutral</option>
                    <option value="Happy" ${data?.Mood === 'Happy' ? 'selected' : ''}>😊 Happy</option>
                    <option value="Very Happy" ${data?.Mood === 'Very Happy' ? 'selected' : ''}>😄 Very Happy</option>
                </select>
            </div>
            <div class="form-group">
                <label>Stress Level (1-10) *</label>
                <input type="range" id="modal-stress" min="1" max="10" value="${data?.StressLevel || 5}">
                <span id="stress-value">${data?.StressLevel || 5}</span>
            </div>
            <div class="form-group">
                <label>Sleep Hours *</label>
                <input type="number" id="modal-sleep" min="0" max="24" value="${data?.SleepHours || 7}" required>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea id="modal-notes" rows="3">${data?.Notes || ''}</textarea>
            </div>
        `,
        
        'counsellor': `
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" id="modal-counselor-name" value="${data?.Name || ''}" required>
            </div>
            <div class="form-group">
                <label>Specialization *</label>
                <input type="text" id="modal-specialization" value="${data?.Specialization || ''}" required>
            </div>
            <div class="form-group">
                <label>Contact *</label>
                <input type="text" id="modal-counselor-contact" value="${data?.Contact || ''}" required>
            </div>
            <div class="form-group">
                <label>Available Day *</label>
                <input type="date" id="modal-available-day" value="${data?.AvailableDay || ''}" required>
            </div>
            <div class="form-group">
                <label>Start Time *</label>
                <input type="time" id="modal-start-time" value="${data?.StartTime || '09:00'}" required>
            </div>
            <div class="form-group">
                <label>End Time *</label>
                <input type="time" id="modal-end-time" value="${data?.EndTime || '17:00'}" required>
            </div>
        `
    };
    
    return forms[tableName] || `<p>Form for ${tableName} not implemented yet</p>`;
}

// ===== SAVE FUNCTIONS FOR EACH TABLE =====
async function savePatient() {
    const patientData = {
        Name: document.getElementById('modal-name').value,
        Age: document.getElementById('modal-age').value,
        Gender: document.getElementById('modal-gender').value,
        Contact: document.getElementById('modal-contact').value,
        address: document.getElementById('modal-address').value,
        Email: document.getElementById('modal-email').value,
        emergencyContact: document.getElementById('modal-emergency').value
    };
    
    let result;
    if (currentEditId) {
        result = await updatePatient(currentEditId, patientData);
    } else {
        result = await createPatient(patientData);
    }
    
    if (result.status === 'success') {
        alert(result.message);
        closeModal();
        // Reload patients table
        if (currentUser.role === 'admin') {
            await loadAdminData();
        }
    } else {
        alert('Error: ' + result.message);
    }
}

async function saveDailylog() {
    const logData = {
        Patient_ID: document.getElementById('modal-patient-id').value,
        Mood: document.getElementById('modal-mood').value,
        StressLevel: document.getElementById('modal-stress').value,
        SleepHours: document.getElementById('modal-sleep').value,
        Notes: document.getElementById('modal-notes').value
    };
    
    const result = await createDailyLog(logData);
    if (result.status === 'success') {
        alert(result.message);
        closeModal();
    } else {
        alert('Error: ' + result.message);
    }
}

// ===== SETUP TABLE BUTTONS =====
function setupTableButtons() {
    // Add button click handlers
    document.querySelectorAll('.add-btn').forEach(btn => {
        if (!btn.hasAttribute('data-listener')) {
            btn.setAttribute('data-listener', 'true');
            btn.addEventListener('click', function() {
                const tableId = this.closest('.log-history').querySelector('table').id || 
                               this.closest('.content-section').id;
                const tableName = tableId.replace('-table', '').replace('all-', '');
                showModal(tableName, 'add');
            });
        }
    });
    
    // Edit button click handlers
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('action-icon') && e.target.textContent.includes('✏️')) {
            const row = e.target.closest('tr');
            const table = row.closest('table');
            const tableName = table.id ? table.id.replace('-table', '').replace('all-', '') : 'patient';
            const id = row.cells[0].textContent;
            
            // Get row data
            const rowData = {
                id: id,
                Name: row.cells[1]?.textContent || '',
                Age: row.cells[2]?.textContent || '',
                Gender: row.cells[3]?.textContent || '',
                Contact: row.cells[4]?.textContent || '',
                Email: row.cells[5]?.textContent || ''
            };
            
            showModal(tableName, 'edit', rowData);
        }
        
        // Delete button
        if (e.target.classList.contains('action-icon') && e.target.textContent.includes('🗑️')) {
            const row = e.target.closest('tr');
            const id = row.cells[0].textContent;
            const table = row.closest('table');
            const tableName = table.id ? table.id.replace('-table', '').replace('all-', '') : 'patient';
            
            if (confirm(`Are you sure you want to delete this ${getTableTitle(tableName)}?`)) {
                deleteRecord(tableName, id, row);
            }
        }
    });
}

async function deleteRecord(tableName, id, rowElement) {
    try {
        let result;
        switch(tableName) {
            case 'patient':
                result = await deletePatient(id);
                break;
            // Add other tables here
            default:
                result = { status: 'error', message: 'Delete not implemented for this table' };
        }
        
        if (result.status === 'success') {
            alert(result.message);
            rowElement.remove();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Delete error:', error);
        alert('Delete failed: ' + error.message);
    }
}
// Save record (add or edit)
document.getElementById('record-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', currentAction);
    formData.append('table', currentTable);
    
    if (currentAction === 'edit') {
        formData.append('id', currentRecordId);
    }
    
    // Get all form values
    const inputs = document.querySelectorAll('#form-fields input, #form-fields textarea, #form-fields select');
    inputs.forEach(input => {
        if (input.value.trim() !== '') {
            formData.append(input.name, input.value);
        }
    });
    
    // Show loading
    const saveBtn = document.querySelector('#record-form .btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Saving...';
    saveBtn.disabled = true;
    
    try {
        const response = await fetch('table_crud.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        if (data.success) {
            alert('✅ Record saved successfully!');
            closeModal();
            loadTableData(currentTable);
        } else {
            alert('❌ Error: ' + data.message);
            console.error('Save error:', data);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Network error. Check console for details.');
    } finally {
        // Restore button
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    }
});
// ===== INITIALIZE =====
document.addEventListener('DOMContentLoaded', function() {
    setupTableButtons();
    
    // Also setup on section changes
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            setTimeout(setupTableButtons, 100);
        });
    });
});