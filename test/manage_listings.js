// Fallback debug to confirm script load
console.log('manage_listings.js loaded successfully.');

document.addEventListener('DOMContentLoaded', () => {
    const supabaseUrl = 'https://owvtdphfvwmvcnstlfnz.supabase.co';
    const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im93dnRkcGhmdndtdmNuc3RsZm56Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDIyNTUwMjEsImV4cCI6MjA1NzgzMTAyMX0.0ohoAWFipfpWGDKTyPAOlo-IoCJtQTCJEG7ucnWROaE';
    const supabase = window.supabase.createClient(supabaseUrl, supabaseKey);
    console.log('Supabase client initialized:', supabase);

    const signUpButton = document.getElementById('signUpButton');
    const signInButton = document.getElementById('signInButton');
    const signOutButton = document.getElementById('signOutButton');
    const fetchButton = document.getElementById('fetchButton');
    const resultDiv = document.getElementById('result');
    const editForm = document.getElementById('editForm');
    const cancelEditButton = document.getElementById('cancelEdit');

    // Check authentication status
    async function checkAuth() {
        const { data: { session } } = await supabase.auth.getSession();
        console.log('Checking auth session:', session);
        return session?.user || null;
    }

    // Sign Up function
    signUpButton.addEventListener('click', async () => {
        console.log('Sign Up button clicked.');
        const email = prompt('Enter your email:');
        const password = prompt('Enter your password:');
        if (email && password) {
            try {
                console.log('Attempting to sign up...', { email });
                const { data, error } = await supabase.auth.signUp({
                    email,
                    password,
                });
                console.log('Sign up response:', { data, error });
                if (error) throw error;
                resultDiv.textContent = 'Sign up successful! Check your email for confirmation.';
                resultDiv.className = 'success';
            } catch (error) {
                resultDiv.textContent = `Sign Up Failed: ${error.message}`;
                resultDiv.className = 'failure';
                console.error('Sign up error:', error);
            }
        }
    });

    // Sign In function
    signInButton.addEventListener('click', async () => {
        console.log('Sign In button clicked.');
        const email = prompt('Enter your email:');
        const password = prompt('Enter your password:');
        if (email && password) {
            try {
                console.log('Attempting to sign in...', { email });
                const { data, error } = await supabase.auth.signInWithPassword({
                    email,
                    password,
                });
                console.log('Sign in response:', { data, error });
                if (error) throw error;
                resultDiv.textContent = 'Sign in successful!';
                resultDiv.className = 'success';
                console.log('Logged in user:', await checkAuth());
            } catch (error) {
                resultDiv.textContent = `Sign In Failed: ${error.message}`;
                resultDiv.className = 'failure';
                console.error('Sign in error:', error);
            }
        }
    });

    // Sign Out function
    signOutButton.addEventListener('click', async () => {
        console.log('Sign Out button clicked.');
        try {
            console.log('Attempting to sign out...');
            await supabase.auth.signOut();
            resultDiv.textContent = 'Signed out successfully.';
            resultDiv.className = 'success';
            console.log('Signed out');
        } catch (error) {
            resultDiv.textContent = `Sign Out Failed: ${error.message}`;
            resultDiv.className = 'failure';
            console.error('Sign out error:', error);
        }
    });

    // Edit listing function
    editForm.addEventListener('submit', async (event) => {
        console.log('Update Listing form submitted.');
        event.preventDefault();
        const user = await checkAuth();
        if (!user) {
            resultDiv.textContent = 'Please sign in to edit a listing.';
            resultDiv.className = 'failure';
            return;
        }

        const id = document.getElementById('editId').value;
        const title = document.getElementById('editTitle').value;
        const price = parseFloat(document.getElementById('editPrice').value);
        const type = document.getElementById('editType').value;
        const location = document.getElementById('editLocation').value;
        const imageFile = document.getElementById('editImage').files[0];
        let imageUrl = null;

        if (!title || !price || !type || !location) {
            resultDiv.textContent = 'All fields are required.';
            resultDiv.className = 'failure';
            return;
        }

        try {
            console.log('Starting edit process for listing ID:', id);
            let updateData = { title, price, type, location };
            if (imageFile) {
                console.log('Updating with new image...');
                const fileName = `${Date.now()}_${imageFile.name}`;
                const { data: uploadData, error: uploadError } = await supabase.storage
                    .from('listings-images')
                    .upload(`public/${fileName}`, imageFile, {
                        cacheControl: '3600',
                        upsert: false
                    });

                if (uploadError) throw uploadError;

                const { data: urlData } = supabase.storage
                    .from('listings-images')
                    .getPublicUrl(`public/${fileName}`);
                imageUrl = urlData.publicUrl;
                updateData.image_url = imageUrl;
                console.log('New image URL generated:', imageUrl);
            }

            console.log('Attempting to update listing with data:', updateData);
            const { data, error } = await supabase
                .from('listings')
                .update(updateData)
                .eq('id', id)
                .eq('user_id', user.id);

            if (error) throw error;

            resultDiv.textContent = 'Listing updated successfully!';
            resultDiv.className = 'success';
            editForm.classList.remove('active');
            fetchListings(); // Refresh the table
            console.log('Listing updated successfully, refreshing table.');
        } catch (error) {
            resultDiv.textContent = `Edit Failed: ${error.message}`;
            resultDiv.className = 'failure';
            console.error('Edit error details:', error);
        }
    });

    // Cancel edit
    cancelEditButton.addEventListener('click', () => {
        console.log('Cancel button clicked.');
        editForm.classList.remove('active');
        console.log('Edit form cancelled.');
    });

    // Delete listing function with debug messages
    async function deleteListing(id, userId) {
        console.log('deleteListing function called with id:', id, 'and userId:', userId);
        const user = await checkAuth();
        console.log('Current user from checkAuth:', user);

        if (!user || user.id !== userId) {
            resultDiv.textContent = 'You are not authorized to delete this listing.';
            resultDiv.className = 'failure';
            console.log('Authorization check failed. User:', user, 'Expected userId:', userId);
            return;
        }

        if (confirm('Are you sure you want to delete this listing?')) {
            console.log('User confirmed deletion for id:', id);
            try {
                console.log('Attempting to delete listing with id:', id);
                const { error } = await supabase
                    .from('listings')
                    .delete()
                    .eq('id', id)
                    .eq('user_id', user.id);

                console.log('Delete operation response error:', error);

                if (error) throw error;

                resultDiv.textContent = 'Listing deleted successfully!';
                resultDiv.className = 'success';
                fetchListings(); // Refresh the table
                console.log('Listing deleted successfully, refreshing table.');
            } catch (error) {
                resultDiv.textContent = `Delete Failed: ${error.message}`;
                resultDiv.className = 'failure';
                console.error('Delete operation failed with error:', error);
            }
        } else {
            console.log('User cancelled deletion for id:', id);
        }
    }

    // Fetch listings function with edit/delete buttons using event delegation
    async function fetchListings() {
        resultDiv.textContent = 'Fetching data...';
        resultDiv.className = '';

        try {
            console.log('Attempting to fetch data...');
            const { data, error } = await supabase
                .from('listings')
                .select('*')
                .order('created_at', { ascending: false});

            console.log('Full response from fetch:', { data, error });

            if (error) throw error;

            if (data.length === 0) {
                resultDiv.textContent = 'Success! No data found in table.';
                resultDiv.className = 'success';
            } else {
                resultDiv.textContent = 'Data fetched successfully:';
                resultDiv.className = 'success';
                let tableHTML = '<table><tr><th>ID</th><th>Title</th><th>Price</th><th>Type</th><th>Location</th><th>Image</th><th>Created At</th><th>Actions</th></tr>';
                const user = await checkAuth();
                console.log('Current authenticated user:', user);
                data.forEach(row => {
                    tableHTML += `<tr>
                        <td>${row.id}</td>
                        <td>${row.title}</td>
                        <td>$${row.price.toFixed(2)}</td>
                        <td>${row.type}</td>
                        <td>${row.location}</td>
                        <td><img src="${row.image_url || 'N/A'}" alt="${row.title}"></td>
                        <td>${new Date(row.created_at).toLocaleString()}</td>
                        <td class="action-buttons" data-user-id="${row.user_id}">
                            ${user && user.id === row.user_id ? `
                                <button class="edit-btn">Edit</button>
                                <button class="delete-btn">Delete</button>
                            ` : ''}
                        </td>
                    </tr>`;
                });
                tableHTML += '</table>';
                resultDiv.innerHTML = tableHTML;

                // Event delegation for edit and delete buttons
                const table = document.querySelector('table');
                table.addEventListener('click', (e) => {
                    const row = e.target.closest('tr');
                    if (!row) return;

                    const id = row.cells[0].textContent; // ID is in the first column
                    const userId = row.cells[7].dataset.userId; // User ID from data-user-id

                    if (e.target.classList.contains('edit-btn')) {
                        console.log('Edit button clicked for id:', id);
                        document.getElementById('editForm').classList.add('active');
                        document.getElementById('editId').value = id;
                        document.getElementById('editTitle').value = row.cells[1].textContent;
                        document.getElementById('editPrice').value = parseFloat(row.cells[2].textContent.replace('$', ''));
                        document.getElementById('editType').value = row.cells[3].textContent;
                        document.getElementById('editLocation').value = row.cells[4].textContent;
                    } else if (e.target.classList.contains('delete-btn')) {
                        console.log('Delete button clicked for id:', id, 'and userId:', userId);
                        deleteListing(id, userId);
                    }
                });
            }
        } catch (error) {
            resultDiv.textContent = `Fetch Failed: ${error.message}`;
            resultDiv.className = 'failure';
            console.error('Fetch error details:', error);
        }
    }

    // Fetch data on button click
    fetchButton.addEventListener('click', async () => {
        console.log('Fetch button clicked.');
        await fetchListings();
    });
});