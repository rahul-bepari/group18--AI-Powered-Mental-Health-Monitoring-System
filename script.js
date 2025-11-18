// ===== USER DATA - ORGANIZED BY USER TYPE AND FEATURES =====

// ===== PATIENT USER =====
const patientData = {
    email: 'patientuser@bondhu.app',
    password: 'patient123',
    name: 'Patient User',
    features: ['dashboard', 'daily-log', 'ai-analysis', 'recommendations', 'counseling', 'progress', 'pharmacy', 'crisis-support'],
    dashboard: {
        avgMood: 4.1,
        avgStress: 2.8,
        avgSleep: 7.6,
        wellnessScore: 82
    },
    dailyLog: {
        history: [
            { date: '2024-11-18 08:00', mood: 4, stress: 2, sleep: 8, notes: 'Felt productive and calm.' },
            { date: '2024-11-17 08:30', mood: 3, stress: 3, sleep: 7, notes: 'A bit stressed about work, but managed.' }
        ]
    }
};

// ===== COUNSELOR USER =====
const counselorData = {
    email: 'counselor@bondhu.app',
    password: 'counselor123',
    name: 'Dr. Sarah Johnson',
    features: ['dashboard', 'counseling', 'progress'],
    dashboard: {
        avgMood: 3.9,
        avgStress: 2.5,
        avgSleep: 7.4,
        wellnessScore: 80
    }
};

// ===== ADMIN USER =====
const adminData = {
    email: 'admin@bondhu.app',
    password: 'admin123',
    name: 'Admin',
    features: ['dashboard', 'user-management', 'manage-counselors', 'all-sessions', 'all-daily-logs', 'ai-analysis-log', 'content', 'crisis-log'],
    dashboard: {
        totalUsers: 1254,
        activeCounselors: 12,
        sessionsWeek: 88,
        crisisAlerts: 3
    },
    userManagement: {
        users: [
            { name: 'Patient User', email: 'patientuser@bondhu.app', role: 'patient', status: 'Active' },
            { name: 'Dr. Sarah Johnson', email: 'counselor@bondhu.app', role: 'counselor', status: 'Active' },
            { name: 'Admin', email: 'admin@bondhu.app', role: 'admin', status: 'Active' },
            { name: 'Pharmacist', email: 'pharmacist@bondhu.app', role: 'pharmacist', status: 'Active' },
            { name: 'Finance Manager', email: 'finance@bondhu.app', role: 'finance', status: 'Active' }
        ]
    },
    manageCounselors: {
        counselors: [
            { name: 'Dr. Sarah Johnson', specialization: 'Anxiety & Stress', activePatients: 8, sessions: 12 }
        ]
    },
    allSessions: {
        sessions: [
            { date: '2024-11-20 10:00', patient: 'Patient User', counselor: 'Dr. Sarah Johnson', duration: '60 min', status: 'Completed' }
        ]
    },
    allDailyLogs: {
        logs: [
            { date: '2024-11-18 08:00', patient: 'Patient User', mood: 4, stress: 2, sleep: 8, notes: 'Felt productive and calm.' }
        ]
    },
    aiAnalysisLog: {
        logs: [
            { date: '2024-11-18 09:30', patient: 'Patient User', analysisType: 'Mood Pattern Analysis', score: '82/100', status: 'Completed' }
        ]
    },
    content: {
        articles: [
            { title: 'Stress Management Techniques', type: 'Article', created: '2024-11-15', status: 'Published' }
        ]
    },
    crisisLog: {
        logs: [
            { date: '2024-11-18 15:45', patient: 'Patient User', severity: 'Medium', status: 'Resolved', assignedTo: 'Dr. Sarah Johnson' }
        ]
    }
};

// ===== PHARMACIST USER =====
const pharmacistData = {
    email: 'pharmacist@bondhu.app',
    password: 'pharmacist123',
    name: 'Pharmacist',
    features: ['dashboard', 'prescription-queue'],
    dashboard: {
        pendingPrescriptions: 24,
        approvedToday: 18,
        outForDelivery: 12,
        delivered: 156
    },
    prescriptionQueue: [
        { date: '2024-07-21', patient: 'Demo User', medication: 'Propranolol', dosage: '10mg', status: 'Approved' },
        { date: '2024-07-21', patient: 'John Smith', medication: 'Lexapro', dosage: '20mg', status: 'Pending' },
        { date: '2024-07-20', patient: 'Jane Doe', medication: 'Xanax', dosage: '0.5mg', status: 'Out for Delivery' },
        { date: '2024-07-15', patient: 'Demo User', medication: 'Sertraline', dosage: '50mg', status: 'Delivered' }
    ]
};

// ===== FINANCE USER =====
const financeData = {
    email: 'finance@bondhu.app',
    password: 'finance123',
    name: 'Finance Manager',
    features: ['dashboard', 'billing-dashboard'],
    dashboard: {
        revenueMonth: 10560,
        outstandingInvoices: 1320
    },
    billingRecords: [
        { date: '2024-07-22', patient: 'Demo User', counselor: 'Dr. Emily Carter', amount: 120, status: 'Paid' },
        { date: '2024-07-21', patient: 'Jane Smith', counselor: 'Dr. Ben Adams', amount: 120, status: 'Paid' },
        { date: '2024-07-19', patient: 'John Doe', counselor: 'Dr. Emily Carter', amount: 120, status: 'Unpaid' }
    ]
};

// ===== CONSOLIDATED USER DATA =====
const userData = {
    patient: patientData,
    counselor: counselorData,
    admin: adminData,
    pharmacist: pharmacistData,
    finance: financeData
};

let currentUser = null;

// ===== DOM ELEMENTS =====
const loginPage = document.getElementById('login-page');
const dashboardPage = document.getElementById('dashboard-page');
const loginForm = document.getElementById('login-form');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const roleButtons = document.querySelectorAll('.role-btn');
const navItems = document.querySelectorAll('.nav-item');
const contentSections = document.querySelectorAll('.content-section');
const logoutBtn = document.getElementById('logout-btn');
const userGreeting = document.getElementById('user-greeting');

let selectedRole = null;

// ===== ROLE SELECTION =====
roleButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        roleButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedRole = btn.dataset.role;
        
        const user = userData[selectedRole];
        emailInput.value = user.email;
        passwordInput.value = user.password;
    });
});

// ===== LOGIN FORM =====
loginForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    if (!selectedRole) {
        alert('Please select a user role');
        return;
    }
    
    const user = userData[selectedRole];
    if (emailInput.value === user.email && passwordInput.value === user.password) {
        currentUser = {
            role: selectedRole,
            name: user.name,
            features: user.features,
            data: user
        };
        localStorage.setItem('currentUser', JSON.stringify(currentUser));
        showDashboard();
    } else {
        alert('Invalid credentials');
    }
});

// ===== SHOW DASHBOARD =====
function showDashboard() {
    loginPage.style.display = 'none';
    dashboardPage.style.display = 'flex';
    userGreeting.textContent = `Welcome, ${currentUser.name}`;
    updateFeatureVisibility();
    updateDashboardDisplay();
    showContentSection(currentUser.features[0]);
}

// ===== UPDATE FEATURE VISIBILITY =====
function updateFeatureVisibility() {
    navItems.forEach(item => {
        const shouldShow = currentUser.features.includes(item.dataset.page);
        item.style.display = shouldShow ? 'flex' : 'none';
    });
}

// ===== NAVIGATION =====
navItems.forEach(item => {
    item.addEventListener('click', () => {
        if (currentUser.features.includes(item.dataset.page)) {
            showContentSection(item.dataset.page);
            updateActiveNav(item);
        }
    });
});

// ===== SHOW CONTENT SECTION =====
function showContentSection(sectionId) {
    contentSections.forEach(s => s.classList.remove('active'));
    const section = document.getElementById(sectionId);
    if (section) {
        section.classList.add('active');
    }
}

// ===== UPDATE ACTIVE NAV =====
function updateActiveNav(item) {
    navItems.forEach(i => i.classList.remove('active'));
    item.classList.add('active');
}

// ===== LOGOUT =====
logoutBtn.addEventListener('click', () => {
    if (confirm('Are you sure you want to logout?')) {
        currentUser = null;
        localStorage.removeItem('currentUser');
        loginForm.reset();
        roleButtons.forEach(b => b.classList.remove('active'));
        loginPage.style.display = 'flex';
        dashboardPage.style.display = 'none';
    }
});

// ===== SLIDER UPDATES =====
document.querySelectorAll('.mood-slider').forEach(slider => {
    slider.addEventListener('input', e => {
        const faces = ['😢', '😔', '😊', '😄', '🤩'];
        e.target.nextElementSibling.textContent = faces[e.target.value - 1];
    });
});

document.querySelectorAll('.stress-slider').forEach(slider => {
    slider.addEventListener('input', e => {
        e.target.nextElementSibling.textContent = e.target.value + '/5';
    });
});

document.querySelectorAll('.sleep-slider').forEach(slider => {
    slider.addEventListener('input', e => {
        e.target.nextElementSibling.textContent = e.target.value + ' hrs';
    });
});

// ===== INITIALIZE ON LOAD =====
window.addEventListener('load', () => {
    const saved = localStorage.getItem('currentUser');
    if (saved) {
        const parsed = JSON.parse(saved);
        currentUser = { ...parsed, data: userData[parsed.role] };
        showDashboard();
    }
});

// ===== BUTTON LISTENERS =====
document.querySelectorAll('.save-btn, .start-btn, .crisis-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        if (e.target.textContent !== 'Logout' && e.target.textContent !== 'Sign in as Patient User') {
            alert('Action completed!');
        }
    });
});

// ===== UPDATE DASHBOARD DISPLAY =====
function updateDashboardDisplay() {
    const patientDash = document.getElementById('patient-dashboard');
    const adminDash = document.getElementById('admin-dashboard');
    const pharmacistDash = document.getElementById('pharmacist-dashboard');
    const financeDash = document.getElementById('finance-dashboard');
    
    // Hide all dashboards first
    if (patientDash) patientDash.style.display = 'none';
    if (adminDash) adminDash.style.display = 'none';
    if (pharmacistDash) pharmacistDash.style.display = 'none';
    if (financeDash) financeDash.style.display = 'none';
    
    if (currentUser.role === 'patient' && patientDash) patientDash.style.display = 'block';
    if (currentUser.role === 'admin' && adminDash) adminDash.style.display = 'block';
    if (currentUser.role === 'pharmacist' && pharmacistDash) pharmacistDash.style.display = 'block';
    if (currentUser.role === 'finance' && financeDash) financeDash.style.display = 'block';
    if (currentUser.role === 'counselor' && patientDash) patientDash.style.display = 'block'; // Counselor sees patient-like dashboard
}

console.log('[v0] Admin features:', adminData.features);

console.log('[v0] Current user role:', currentUser?.role);
console.log('[v0] Features for this user:', currentUser?.features);
