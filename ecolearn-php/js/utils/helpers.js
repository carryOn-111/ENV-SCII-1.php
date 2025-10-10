// Utility Helper Functions
function customAlert(message) {
    console.log("Action triggered: " + message);
    const alertBox = document.createElement('div');
    alertBox.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); padding: 20px; background: #fff; border: 2px solid var(--primary-color); border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); z-index: 5000;';
    alertBox.innerHTML = `<strong>Action Alert</strong><p style="margin-top: 10px;">${message}</p><button onclick="this.parentNode.remove()" style="margin-top: 15px; padding: 5px 10px; background: var(--secondary-color); color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>`;
    document.body.appendChild(alertBox);
}

function setActiveNavItem(section) {
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    const activeItem = document.querySelector(`.nav-item.${section}`);
    if (activeItem) {
        activeItem.classList.add('active');
    }
}

function scrollCatalogue(containerId, direction) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const scrollDistance = 320; 

    if (direction === 'left') {
        container.scrollLeft -= scrollDistance;
    } else if (direction === 'right') {
        container.scrollLeft += scrollDistance;
    }
}

function handleLogout() {
    customAlert('Logging out... Thank you for using EcoLearn!');
    setTimeout(() => {
        window.location.href = 'index.html'; 
    }, 1500);
}

function handleTeacherLogout() {
    customAlert('Teacher logging out... Thank you for using EcoLearn!');
    setTimeout(() => {
        window.location.href = 'student-dashboard.html'; 
    }, 1500);
}