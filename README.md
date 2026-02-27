🍺 Pubbar – Takeaway Ordering System (Laravel)

Pubbar is a Laravel-based web application designed to manage a pub/bar takeaway service.
Customers can browse products, select variants (e.g., different beer sizes with different prices), book available time slots, and place takeaway orders.
Staff and administrators can manage products, categories, time slot capacities, and order statuses.

This project was developed for academic purposes.
✨ Features
    👤 Customer
        User registration and login
        Browse products by categories
        Product variants (e.g., different beer sizes with different prices)
        Add products to an order
        Book orders based on available time slots
        Pay using PayPal (Sandbox mode configurable via .env)
    
    🛠 Staff / Administrator
        Create, edit and delete categories
        Create, edit and delete products
        Manage product variants
        Manage time slots and capacity limits
        View all orders
        Update order status:
            In preparation
            Ready

🧱 Technologies
    Laravel
    PHP
    MySQL
    Blade
    Bootstrap
    PayPal API (Sandbox)

📋 Requirements
    PHP
    Composer
    MySQL / MariaDB
    Node.js + npm

🚀 Installation & Setup

    1) Clone the repository
        git clone https://github.com/alessandro-biagio/pubbar.git
        cd pubbar
        
    2) Install PHP dependencies
        composer install
        
    3) Create the environment file and generate the app key
        cp .env.example .env
        php artisan key:generate
        
    4) Configure the database
    Edit .env and set your DB credentials:
    
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=pubbar
        DB_USERNAME=root
        DB_PASSWORD=
        
    5) Run migrations and seeders
        php artisan migrate --seed
    
    This will run the following seeders:
    CategorySeeder
    ProductSeeder
    ProductVariantSeeder
    SettingsSeeder
    UserSeeder
    
    6) Enable storage for product images
        php artisan storage:link
    This creates a symlink so files in storage/app/public are accessible via /storage/....
    
    7) Install frontend dependencies and run Vite (if needed)
        npm install
        npm run dev
        
    8) Start the development server
        php artisan serve
    
    Open your browser at:
    http://127.0.0.1:8000
    
👥 Test Accounts
    These users are created automatically by the seeders:
    
    Administrator
    Email: ale@ale.com
    Password: Alessandro1*
    
    Staff
    Email: matteo@matteo.com
    Password: Matteo1*
    
    Regular User
    Email: mirco@mirco.com
    Password: Mirco1**
    
    These credentials are for development/testing purposes only.

💳 PayPal Sandbox Configuration

    PayPal credentials are not included in the repository.
    Add your sandbox credentials to .env:
    
    PAYPAL_CLIENT_ID=your_sandbox_client_id
    PAYPAL_SECRET=your_sandbox_secret
    PAYPAL_MODE=sandbox
    PAYPAL_CURRENCY=EUR
    
    Sandbox credentials can be created from the PayPal Developer Dashboard:
    https://developer.paypal.com/

🛠 Troubleshooting

    If images are not displaying:
        php artisan storage:link
    
    If needed:
        rmdir public/storage
        php artisan storage:link
        
📚 License
    Academic project.
