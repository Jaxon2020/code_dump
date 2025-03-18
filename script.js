// Initialize Supabase
const supabase = Supabase.createClient(
    'YOUR_PROJECT_URL', // Replace with your Supabase Project URL
    'YOUR_ANON_KEY'     // Replace with your Supabase Anon Key
);

// Toggle Auth Dropdown
function toggleAuthDropdown(event) {
    event.preventDefault(); // Prevent default link behavior
    const dropdown = document.getElementById('auth-dropdown');
    const isVisible = dropdown.style.display === 'block';
    dropdown.style.display = isVisible ? 'none' : 'block';
    if (!isVisible) {
        // Hide forms if logged in
        const session = supabase.auth.getSession();
        if (session.data.session) {
            document.getElementById('signup-form').style.display = 'none';
            document.getElementById('signin-form').style.display = 'none';
            document.getElementById('logout').style.display = 'block';
        }
    }
}

// Authentication Functions
async function signUp() {
    const username = document.getElementById('signup-username').value;
    const email = document.getElementById('signup-email').value;
    const password = document.getElementById('signup-password').value;
    const repeatPassword = document.getElementById('signup-repeat-password').value;

    if (password !== repeatPassword) {
        alert('Passwords do not match!');
        return;
    }

    const { data, error } = await supabase.auth.signUp({
        email: email,
        password: password,
        options: {
            data: { username: username }
        }
    });
    if (error) {
        alert('Error signing up: ' + error.message);
    } else {
        alert('Sign-up successful! Check your email to confirm.');
        document.getElementById('auth-dropdown').style.display = 'none';
    }
}

async function signIn() {
    const email = document.getElementById('signin-email').value;
    const password = document.getElementById('signin-password').value;
    const { data, error } = await supabase.auth.signInWithPassword({ email, password });
    if (error) {
        alert('Error signing in: ' + error.message);
    } else {
        alert('Signed in successfully!');
        document.getElementById('signup-form').style.display = 'none';
        document.getElementById('signin-form').style.display = 'none';
        document.getElementById('logout').style.display = 'block';
        document.getElementById('listing-form').style.display = 'block';
        document.getElementById('auth-dropdown').style.display = 'none';
    }
}

async function signOut() {
    const { error } = await supabase.auth.signOut();
    if (error) {
        alert('Error signing out: ' + error.message);
    } else {
        alert('Signed out successfully!');
        document.getElementById('signup-form').style.display = 'block';
        document.getElementById('signin-form').style.display = 'block';
        document.getElementById('logout').style.display = 'none';
        document.getElementById('listing-form').style.display = 'none';
        document.getElementById('auth-dropdown').style.display = 'none';
    }
}

// Initialize Authentication State
function initAuth() {
    const authToggle = document.getElementById('auth-toggle');
    if (authToggle) {
        authToggle.addEventListener('click', toggleAuthDropdown);
    } else {
        console.error('auth-toggle element not found');
    }

    supabase.auth.onAuthStateChange((event, session) => {
        const dropdown = document.getElementById('auth-dropdown');
        if (session) {
            document.getElementById('signup-form').style.display = 'none';
            document.getElementById('signin-form').style.display = 'none';
            document.getElementById('logout').style.display = 'block';
            document.getElementById('listing-form').style.display = 'block';
            if (dropdown && dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            }
        } else {
            document.getElementById('signup-form').style.display = 'block';
            document.getElementById('signin-form').style.display = 'block';
            document.getElementById('logout').style.display = 'none';
            document.getElementById('listing-form').style.display = 'none';
        }
    });
}

// Listing Functions (unchanged)
async function createListing() {
    const user = await supabase.auth.getUser();
    if (!user.data.user) {
        alert('Please sign in to create a listing.');
        return;
    }

    const title = document.getElementById('listing-title').value;
    const description = document.getElementById('listing-description').value;
    const price = parseFloat(document.getElementById('listing-price').value);

    const { data, error } = await supabase
        .from('listings')
        .insert([
            {
                user_id: user.data.user.id,
                title,
                description,
                price,
            },
        ]);

    if (error) {
        alert('Error creating listing: ' + error.message);
    } else {
        alert('Listing created successfully!');
        fetchListings();
    }
}

async function fetchListings() {
    const { data, error } = await supabase
        .from('listings')
        .select('*')
        .order('created_at', { ascending: false });

    if (error) {
        console.error('Error fetching listings:', error.message);
        return;
    }

    const listingsContainer = document.getElementById('listings-container');
    listingsContainer.innerHTML = '';
    data.forEach(listing => {
        const listingElement = document.createElement('div');
        listingElement.className = 'listing-card';
        listingElement.innerHTML = `
            <h3>${listing.title}</h3>
            <p>${listing.description}</p>
            <p>Price: $${listing.price}</p>
            <hr>
        `;
        listingsContainer.appendChild(listingElement);
    });
}

window.onload = function() {
    fetchListings();
    initAuth();
};