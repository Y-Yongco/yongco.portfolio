// Constants
const API_URL = 'http://localhost:8080/api';
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/jpg'];

// DOM Elements
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const imagePreview = document.getElementById('imagePreview');
const previewContainer = document.getElementById('previewContainer');
const analyzeButton = document.getElementById('analyzeButton');
const resultsContainer = document.getElementById('resultsContainer');
const spinner = analyzeButton.querySelector('.spinner');
const buttonContent = analyzeButton.querySelector('.button-content');

// Initialize the application
function initializeApp() {
    createUploadIcon();
    setupEventListeners();
    checkBackendConnection();
}

// Setup event listeners
function setupEventListeners() {
    dropZone.addEventListener('dragover', handleDragOver);
    dropZone.addEventListener('drop', handleDrop);
    dropZone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', handleFileSelect);
    analyzeButton.addEventListener('click', analyzeImage);

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        document.body.addEventListener(eventName, preventDefaults, false);
    });
}

// Prevent default drag behaviors
function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

// Check backend connection
async function checkBackendConnection() {
    try {
        const response = await fetch(`${API_URL}/health`, { method: 'GET' });
        if (!response.ok) {
            console.warn('Backend health check failed');
        }
    } catch (error) {
        console.warn('Backend appears to be offline:', error);
    }
}

// Cleanup function
function cleanup() {
    // Remove any temporary data
    if (uploadIconUrl) {
        URL.revokeObjectURL(uploadIconUrl);
    }
}

// Store the upload icon URL
let uploadIconUrl;

// Create upload icon SVG
function createUploadIcon() {
    const svgContent = `
    <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M32 8L48 24H40V40H24V24H16L32 8Z" fill="#81C784"/>
        <path d="M56 48V56H8V48H12V52H52V48H56Z" fill="#81C784"/>
    </svg>
    `;

    const blob = new Blob([svgContent], { type: 'image/svg+xml' });
    uploadIconUrl = URL.createObjectURL(blob);
    document.getElementById('uploadIcon').src = uploadIconUrl;
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializeApp);

// Cleanup when page is unloaded
window.addEventListener('unload', cleanup);

// Remove existing event listener setup
dropZone.removeEventListener('dragover', handleDragOver);
dropZone.removeEventListener('drop', handleDrop);
dropZone.removeEventListener('click', () => fileInput.click());
fileInput.removeEventListener('change', handleFileSelect);
analyzeButton.removeEventListener('click', analyzeImage);

// Drag and Drop Handlers
function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    dropZone.style.borderColor = 'var(--primary-color)';
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    dropZone.style.borderColor = 'var(--accent-color)';
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        handleFile(files[0]);
    }
}

// File Selection Handler
function handleFileSelect(e) {
    const files = e.target.files;
    if (files.length > 0) {
        handleFile(files[0]);
    }
}

// File Processing
function handleFile(file) {
    if (!ALLOWED_TYPES.includes(file.type)) {
        alert('Please upload a valid image file (JPEG, PNG)');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        imagePreview.src = e.target.result;
        imagePreview.onload = function() {
            previewContainer.style.display = 'block';
            dropZone.style.display = 'none';
            if (!document.querySelector('.reset-button')) {
                addResetButton();
            }
        };
        imagePreview.onerror = function() {
            alert('Failed to load image. Please try another file.');
            resetUpload();
        };
    };
    reader.onerror = function() {
        alert('Failed to read file. Please try again.');
        resetUpload();
    };
    reader.readAsDataURL(file);
}

function resetUpload() {
    fileInput.value = '';
    previewContainer.style.display = 'none';
    dropZone.style.display = 'block';
    resultsContainer.style.display = 'none';
}

// Add reset button functionality
function addResetButton() {
    const resetButton = document.createElement('button');
    resetButton.className = 'upload-button reset-button';
    resetButton.style.marginLeft = '1rem';
    resetButton.textContent = 'Upload New Image';
    resetButton.onclick = function() {
        resetUpload();
        fileInput.click();
    };
    analyzeButton.parentNode.insertBefore(resetButton, analyzeButton.nextSibling);
}

// Image Analysis
async function analyzeImage() {
    const file = fileInput.files[0];
    if (!file) {
        alert('Please select an image first');
        return;
    }

    // Show loading state
    setLoadingState(true);

    try {
        const formData = new FormData();
        formData.append('image', file);

        const response = await fetch(`${API_URL}/analyze`, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => null);
            throw new Error(errorData?.message || `Analysis failed with status: ${response.status}`);
        }

        const result = await response.json();
        if (!validateAnalysisResult(result)) {
            throw new Error('Invalid response format from server');
        }
        displayResults(result);
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'Failed to analyze image. Please try again.');
    } finally {
        setLoadingState(false);
    }
}

// Validate analysis result
function validateAnalysisResult(result) {
    const requiredFields = [
        'wasteType',
        'detectedObjects',
        'disposalRecommendation',
        'recyclingTips',
        'diyProjects',
        'environmentalImpact'
    ];
    
    return requiredFields.every(field => {
        if (Array.isArray(result[field])) {
            return Array.isArray(result[field]);
        }
        return typeof result[field] === 'string' && result[field].length > 0;
    });
}

// UI Updates
function setLoadingState(isLoading) {
    spinner.style.display = isLoading ? 'inline-block' : 'none';
    buttonContent.textContent = isLoading ? 'Analyzing...' : 'Analyze Image';
    analyzeButton.disabled = isLoading;
}

function displayResults(result) {
    // Show results container
    resultsContainer.style.display = 'block';

    // Update waste type badge
    const wasteTypeBadge = document.getElementById('wasteTypeBadge');
    wasteTypeBadge.textContent = result.wasteType;
    wasteTypeBadge.className = `waste-type-badge ${result.wasteType.toLowerCase()}`;

    // Update detected items
    const detectedItems = document.getElementById('detectedItems');
    detectedItems.innerHTML = result.detectedObjects
        .map(item => `<li>${item}</li>`)
        .join('');

    // Update disposal recommendation
    document.getElementById('disposalRecommendation').textContent = 
        result.disposalRecommendation;

    // Update recycling tips
    const recyclingTips = document.getElementById('recyclingTips');
    recyclingTips.innerHTML = result.recyclingTips
        .map(tip => `<li>${tip}</li>`)
        .join('');

    // Update DIY projects
    const diyProjects = document.getElementById('diyProjects');
    diyProjects.innerHTML = result.diyProjects
        .map(project => `<li>${project}</li>`)
        .join('');

    // Update environmental impact
    document.getElementById('environmentalImpact').textContent = 
        result.environmentalImpact;

    // Scroll to results
    resultsContainer.scrollIntoView({ behavior: 'smooth' });
} 