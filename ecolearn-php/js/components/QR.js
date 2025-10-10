// QR Code Component Functions
let qrCodeInstance = null;

function generateQRCode(resourceId, type, title) {
    const qrCanvasDiv = document.getElementById('qrCodeCanvas');
    document.getElementById('qrTitle').textContent = `QR Code for: ${title}`;
    const qrDataUrl = `https://ecolearn.com/public/view?type=${type}&id=${resourceId}`;
    qrCanvasDiv.innerHTML = ''; 
    qrCodeInstance = new QRCode(qrCanvasDiv, {
        text: qrDataUrl,
        width: 256,
        height: 256,
        colorDark : "#34495e",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
    customAlert(`Generated QR code for ${title} pointing to: ${qrDataUrl}`);
    showModal('qrGeneratorModal');
}

function handleQRAccess(resourceId, resourceType) {
    const anonResourceIdEl = document.getElementById('anonResourceId');
    const anonResourceTypeEl = document.getElementById('anonResourceType');
    if(anonResourceIdEl) anonResourceIdEl.value = resourceId;
    if(anonResourceTypeEl) anonResourceTypeEl.value = resourceType;

    const title = resourceType.charAt(0).toUpperCase() + resourceType.slice(1);
    const qrResourceTitleEl = document.getElementById('qrResourceTitle');
    if(qrResourceTitleEl) qrResourceTitleEl.textContent = `Resource: ${title} ID ${resourceId}`;

    showModal('qrLandingModal');
    hideModal('qrScannerModal'); 
}

function processAnonymousAccess() {
    const anonNameEl = document.getElementById('anonName');
    const resourceIdEl = document.getElementById('anonResourceId');
    const resourceTypeEl = document.getElementById('anonResourceType');
    
    if(!anonNameEl || !resourceIdEl || !resourceTypeEl) return;
    
    const name = anonNameEl.value;
    const resourceId = resourceIdEl.value;
    const resourceType = resourceTypeEl.value;

    if (!name.trim()) {
        customAlert("Please enter your name/initials to proceed anonymously.");
        return;
    }

    customAlert(`Success! Session started for Anonymous User: ${name}. Loading ${resourceType} ${resourceId}.`);
    
    hideModal('qrLandingModal');
    anonNameEl.value = '';
}