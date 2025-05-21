# Green Trade - Agricultural Marketplace

## Overview

Green Trade is a web-based marketplace connecting farmers (sellers) and buyers of agricultural products. The application is built using PHP with Firebase as the backend service for authentication and data storage. The platform supports two user types - buyers and sellers - each with their own separate workflows and dashboards.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Technology Stack

- **Frontend**: HTML, CSS (Bootstrap 5), JavaScript
- **Backend**: PHP (Session-based)
- **Database**: Firebase Firestore
- **Authentication**: Firebase Authentication
- **Storage**: Firebase Storage (for product images)
- **Hosting**: Replit PHP server

The application follows a simple MVC-like pattern where:
- PHP files handle view rendering and session management
- JavaScript files handle data manipulation and Firebase interactions
- CSS provides styling with Bootstrap as the primary framework

### Authentication Flow

The system uses Firebase Authentication for user management but maintains PHP sessions for state management during user visits. When a user logs in:

1. Credentials are verified against Firebase Authentication
2. User data is retrieved from Firestore
3. A PHP session is created to store user information
4. User is redirected to the appropriate dashboard based on account type

This dual approach allows us to leverage Firebase's security features while maintaining traditional PHP session management.

## Key Components

### User Management

- **Registration**: New users can register as either a buyer or seller
- **Login**: Existing users log in with email/password
- **Session Management**: PHP sessions track logged-in users
- **Profile Management**: Users can view and edit their profiles

### Seller Features

- **Dashboard**: Overview of products and orders
- **Product Management**: Add, edit, and delete products
- **Order Management**: View and manage incoming orders
- **Fulfillment**: Process orders and update order status

### Buyer Features

- **Dashboard**: Overview of recent orders and cart
- **Product Browsing**: View all products with filtering options
- **Shopping Cart**: Add products to cart, update quantities
- **Checkout**: Complete purchases with shipping information
- **Order Tracking**: View order history and status

## Data Flow

### Product Listing Flow

1. Seller creates product with details (name, description, price, etc.)
2. Product is stored in Firestore 'products' collection
3. Buyers can view products on the marketplace
4. Products can be filtered by category or searched by name

### Purchase Flow

1. Buyer adds products to cart (stored in 'carts' collection)
2. Buyer proceeds to checkout and confirms shipping details
3. Order is created in 'orders' collection
4. Seller is notified of new order
5. Seller processes order and updates status
6. Buyer receives updates on order status

## External Dependencies

- **Bootstrap 5**: For responsive UI components
- **Feather Icons**: For consistent icon system
- **Firebase SDK**: For authentication and data storage

The application relies heavily on Firebase for its backend functionality. Key Firebase services used:

1. **Firebase Authentication**: User registration and login
2. **Firestore**: NoSQL database for storing products, orders, user profiles, and cart data
3. **Firebase Storage**: For storing product images

## Deployment Strategy

The application is configured to run on Replit using the PHP web server. The main configuration:

- Using PHP's built-in server: `php -S 0.0.0.0:5000`
- Entry point is `index.php` which redirects to appropriate pages based on authentication status

### Firebase Configuration

Firebase configuration is stored in `includes/firebase_config.php` and is used by the JavaScript Firebase SDK. For production deployment, these values should be secured properly.

### Session Management

PHP sessions are used to maintain user state throughout the application. Sessions are started at the beginning of each page with `session_start()` and store user information like ID, name, email, and user type.

## Development Guidelines

1. **File Structure**:
   - `/buyer` - Pages accessible to buyers
   - `/seller` - Pages accessible to sellers
   - `/includes` - Shared PHP components and functions
   - `/assets` - CSS, JavaScript, and images

2. **Authentication**:
   - Always use `ensureUserLoggedIn()` at the beginning of protected pages
   - Check user type to prevent unauthorized access between buyer/seller areas

3. **Data Handling**:
   - Use Firebase for all data operations
   - Validate input on both client and server sides
   - Implement proper error handling for Firebase operations

4. **UI Components**:
   - Use Bootstrap components for consistent UI
   - Follow the established color scheme (success/green as primary color)
   - Ensure mobile responsiveness