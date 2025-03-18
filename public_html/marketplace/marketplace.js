// Initialize Supabase client
const supabaseUrl = 'https://owvtdphfvwmvcnstlfnz.supabase.co'; // Replace with your Project URL
const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im93dnRkcGhmdndtdmNuc3RsZm56Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDIyNTUwMjEsImV4cCI6MjA1NzgzMTAyMX0.0ohoAWFipfpWGDKTyPAOlo-IoCJtQTCJEG7ucnWROaE'; // Replace with your anon key
const supabase = window.supabase.createClient(supabaseUrl, supabaseKey);

// Store listings (fetched from Supabase instead of localStorage)
let listings = [];

// Function to fetch listings from Supabase
async function fetchListings() {
    const { data, error } = await supabase.from('listings').select('*');
    if (error) console.error('Error fetching listings:', error);
    else listings = data || [];
    displayListings(listings);
}

// Function to display listings
function displayListings(listingsToShow) {
    const listingsContainer = document.querySelector('.listings-container');
    listingsContainer.innerHTML = ''; // Clear existing listings

    if (listingsToShow.length === 0) {
        document.querySelector('.no-results').style.display = 'block';
    } else {
        document.querySelector('.no-results').style.display = 'none';
        listingsToShow.forEach(listing => {
            const card = document.createElement('div');
            card.classList.add('featured-animal');
            card.innerHTML = `
                <img src="${listing.image_url}" alt="${listing.type}">
                <div class="animal-info">
                    <h3>${listing.title}</h3>
                    <p class="price">$${listing.price}</p>
                    <p class="details">Type: ${listing.type} | Location: ${listing.location}</p>
                    <button class="add-to-cart">Add to Cart</button>
                </div>
            `;
            listingsContainer.appendChild(card);
        });
    }
}

// Initial fetch of listings when the page loads
document.addEventListener('DOMContentLoaded', () => {
    fetchListings();

    // Handle image preview
    const imageInput = document.getElementById('listing-image');
    const preview = document.getElementById('image-preview');

    imageInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (event) => {
                preview.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
                preview.querySelector('img').style.maxWidth = '100%';
                preview.querySelector('img').style.maxHeight = '200px';
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '<p>Invalid file type. Please upload an image.</p>';
        }
    });

    // Handle drag and drop for image upload
    const uploadArea = document.querySelector('.upload-area');
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (event) => {
                preview.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
                preview.querySelector('img').style.maxWidth = '100%';
                preview.querySelector('img').style.maxHeight = '200px';
                imageInput.files = e.dataTransfer.files; // Update input files
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '<p>Invalid file type. Please upload an image.</p>';
        }
    });
});

// Handle form submission to create a new listing
document.getElementById('create-listing-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const title = document.getElementById('listing-title').value;
    const price = parseFloat(document.getElementById('listing-price').value);
    const type = document.getElementById('listing-type').value;
    const location = document.getElementById('listing-location').value;
    const imageInput = document.getElementById('listing-image');
    const file = imageInput.files[0];

    let imageUrl = null;
    if (file) {
        // Upload image to Supabase Storage
        const fileName = `${Date.now()}-${file.name}`;
        const { data, error: uploadError } = await supabase.storage
            .from('listing-images')
            .upload(fileName, file, {
                cacheControl: '3600',
                upsert: false
            });
        if (uploadError) {
            console.error('Error uploading image:', uploadError);
            return;
        }
        // Get public URL of the uploaded image
        const { data: urlData, error: urlError } = supabase.storage
            .from('listing-images')
            .getPublicUrl(fileName);
        if (urlError) {
            console.error('Error getting public URL:', urlError);
            return;
        }
        imageUrl = urlData.publicUrl;
    }

    // Insert listing into Supabase database
    const { data, error } = await supabase.from('listings').insert({
        title,
        price,
        type,
        location,
        image_url: imageUrl
    }).select();
    if (error) console.error('Error inserting listing:', error);
    else {
        listings.push(data[0]); // Update local listings array
        document.getElementById('create-listing-form').reset();
        document.getElementById('image-preview').innerHTML = '<p>Drag and drop an image here or click to upload</p>';
        displayListings(listings);
    }
});

// Handle search/filter functionality
document.querySelector('.search-btn').addEventListener('click', filterListings);

function filterListings() {
    const usersInput = document.querySelector('input[placeholder="Search users by name or email..."]').value.toLowerCase();
    const typeInput = document.querySelector('input[placeholder="e.g. Chicken"]').value.toLowerCase();
    const locationInput = document.querySelector('input[placeholder="e.g. Springfield, IL"]').value.toLowerCase();
    const minPrice = parseFloat(document.querySelector('input[placeholder="$"]').value) || 0;
    const maxPrice = parseFloat(document.querySelector('input[placeholder="$"]:nth-of-type(2)').value) || Infinity;

    const filteredListings = listings.filter(listing => {
        const titleMatch = usersInput ? listing.title.toLowerCase().includes(usersInput) : true;
        const typeMatch = typeInput ? listing.type.toLowerCase().includes(typeInput) : true;
        const locationMatch = locationInput ? listing.location.toLowerCase().includes(locationInput) : true;
        const priceMatch = listing.price >= minPrice && listing.price <= maxPrice;

        return titleMatch && typeMatch && locationMatch && priceMatch;
    });

    displayListings(filteredListings);
}

// Handle reset functionality
document.querySelector('.reset-btn').addEventListener('click', () => {
    document.querySelectorAll('input').forEach(input => input.value = '');
    document.getElementById('image-preview').innerHTML = '<p>Drag and drop an image here or click to upload</p>';
    displayListings(listings);
});