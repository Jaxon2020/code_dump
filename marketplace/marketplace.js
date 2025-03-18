document.addEventListener('DOMContentLoaded', () => {
    const supabaseUrl = 'https://owvtdphfvwmvcnstlfnz.supabase.co';
    const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im93dnRkcGhmtndtdmNuc3RsZm56Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDIyNTUwMjEsImV4cCI6MjA1NzgzMTAyMX0.0ohoAWFipfpWGDKTyPAOlo-IoCJtQTCJEG7ucnWROaE';
    const supabase = window.supabase.createClient(supabaseUrl, supabaseKey);
    console.log('Supabase client initialized:', supabase);

    const loginButton = document.getElementById('loginButton');
    const logoutButton = document.getElementById('logoutButton');
    const createListingButton = document.getElementById('createListingButton');
    const testButton = document.getElementById('testButton');
    const resultDiv = document.getElementById('result');

    // Check authentication status
    async function checkAuth() {
        const { data: { session } } = await supabase.auth.getSession();
        return session?.user || null;
    }

    // Login function
    loginButton.addEventListener('click', async () => {
        const { error } = await supabase.auth.signInWithPassword({
            email: prompt('Enter your email:'),
            password: prompt('Enter your password:'),
        });
        if (error) {
            resultDiv.textContent = `Login Failed: ${error.message}`;
            resultDiv.className = 'failure';
            console.error('Login error:', error);
        } else {
            resultDiv.textContent = 'Logged in successfully!';
            resultDiv.className = 'success';
            console.log('Logged in user:', await checkAuth());
        }
    });

    // Logout function
    logoutButton.addEventListener('click', async () => {
        await supabase.auth.signOut();
        resultDiv.textContent = 'Logged out.';
        resultDiv.className = 'success';
        console.log('Logged out');
    });

    // Create listing function
    createListingButton.addEventListener('click', async () => {
        const user = await checkAuth();
        if (!user) {
            resultDiv.textContent = 'Please log in to create a listing.';
            resultDiv.className = 'failure';
            return;
        }

        const title = document.getElementById('listing-title').value;
        const price = parseFloat(document.getElementById('listing-price').value);
        const type = document.getElementById('listing-type').value;
        const location = document.getElementById('listing-location').value;
        const imageInput = document.getElementById('listing-image');
        let imageUrl = ''; // Placeholder for now; we'll handle image upload later

        if (!title || !price || !type || !location) {
            resultDiv.textContent = 'All fields are required.';
            resultDiv.className = 'failure';
            return;
        }

        try {
            console.log('Attempting to create listing...', { title, price, type, location, imageUrl });
            const { data, error } = await supabase
                .from('listings')
                .insert({
                    title,
                    price,
                    type,
                    location,
                    image_url: imageUrl,
                    user_id: user.id, // Ties the listing to the authenticated user
                });

            console.log('Full response:', { data, error });

            if (error) throw error;

            resultDiv.textContent = 'Listing created successfully!';
            resultDiv.className = 'success';
            // Clear form (optional)
            document.getElementById('listingForm').reset();
        } catch (error) {
            resultDiv.textContent = `Create Failed: ${error.message}`;
            resultDiv.className = 'failure';
            console.error('Create error:', error);
        }
    });

    // Fetch listings function
    async function fetchListings() {
        resultDiv.textContent = 'Fetching data...';
        resultDiv.className = '';

        try {
            console.log('Attempting to fetch data...');
            const { data, error } = await supabase
                .from('listings')
                .select('*')
                .order('created_at', { ascending: false });

            console.log('Full response:', { data, error });

            if (error) throw error;

            if (data.length === 0) {
                resultDiv.textContent = 'Success! No data found in table.';
                resultDiv.className = 'success';
            } else {
                resultDiv.textContent = 'Data fetched successfully:';
                resultDiv.className = 'success';
                let tableHTML = '<table><tr><th>ID</th><th>Title</th><th>Price</th><th>Type</th><th>Location</th><th>Image URL</th><th>Created At</th></tr>';
                data.forEach(row => {
                    tableHTML += `<tr>
                        <td>${row.id}</td>
                        <td>${row.title}</td>
                        <td>$${row.price.toFixed(2)}</td>
                        <td>${row.type}</td>
                        <td>${row.location}</td>
                        <td>${row.image_url || 'N/A'}</td>
                        <td>${new Date(row.created_at).toLocaleString()}</td>
                    </tr>`;
                });
                tableHTML += '</table>';
                resultDiv.innerHTML = tableHTML; // Overwrite to avoid duplicate tables
            }
        } catch (error) {
            resultDiv.textContent = `Fetch Failed: ${error.message}`;
            resultDiv.className = 'failure';
            console.error('Fetch error:', error);
        }
    }

    // Fetch data on test button click
    testButton.addEventListener('click', async () => {
        await fetchListings();
    });
});