// Modal Component Functions
function showModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('active');
    }
}

function hideModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Profile Modal Functions
window.openEditModal = function() {
    // Determine which profile data to use based on current page
    const profileData = window.location.pathname.includes('student') ? studentProfileData : userProfileData;
    
    document.getElementById('editName').value = profileData.name;
    document.getElementById('editRole').value = profileData.role;
    document.getElementById('editEmail').value = profileData.email;
    document.getElementById('editPhone').value = profileData.phone;
    document.getElementById('editAddress').value = profileData.address;
    
    const editProfilePicUrlEl = document.getElementById('editProfilePicUrl');
    if(editProfilePicUrlEl) editProfilePicUrlEl.value = profileData.profilePicUrl;

    const editModalEl = document.getElementById('editModal');
    if (editModalEl) editModalEl.classList.add('active');
}

window.closeEditModal = function() {
    const editModalEl = document.getElementById('editModal');
    if (editModalEl) editModalEl.classList.remove('active');
}

window.saveProfile = function() {
    // Determine which profile data and load function to use
    const isStudent = window.location.pathname.includes('student');
    const profileData = isStudent ? studentProfileData : userProfileData;
    const loadFunction = isStudent ? loadStudentContent : loadContent;
    
    const name = document.getElementById('editName').value.trim() || (isStudent ? "New Student" : "New Teacher");
    const role = document.getElementById('editRole').value.trim() || (isStudent ? "Student" : "Teacher");
    const email = document.getElementById('editEmail').value.trim() || "N/A";
    const phone = document.getElementById('editPhone').value.trim() || "N/A";
    const address = document.getElementById('editAddress').value.trim() || "N/A";
    const profilePicUrl = document.getElementById('editProfilePicUrl').value.trim();

    profileData.name = name;
    profileData.role = role;
    profileData.email = email;
    profileData.phone = phone;
    profileData.address = address;
    profileData.profilePicUrl = profilePicUrl;

    loadFunction('profile');
    customAlert(`Profile updated for ${name}.`);
    closeEditModal();
}