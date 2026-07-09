# Onix Coffee Shop

This project is a custom-built, full-stack web application developed with PHP 8 (OOP), MySQL, and Bootstrap 5 for a modern coffee shop. It provides a complete digital experience, featuring a dynamic menu catalog, user authentication, and an interactive table booking system.

The architecture follows strict Object-Oriented Programming (OOP) principles, utilizing `PDO::FETCH_CLASS` for seamless entity hydration, Singleton patterns for database connections, and a secure Role-Based Access Control (RBAC) system to separate regular customers from administrators.

## Project Structure

- `database/`: 
  - `onix_db.sql`: The initialization script containing the complete relational database schema and baseline data.
- `src/`: The main application source code directory.
  - `assets/`: Contains global stylesheets (`styles.css`), branding icons (`onix-favicon.ico`), and dynamic product/category image uploads.
  - `bookings/`: Manages the reservation logic. Includes the `Booking` entity, interactive calendar scripts (`calendar.js`), and complete CRUD operations for users and admins.
  - `categories/`: OOP models and CRUD interfaces for managing the menu hierarchy.
  - `products/`: Core menu catalog management. Includes the `Product` entity, image upload sanitization, and secure, paginated, and sortable listing views.
  - `templates/`: Reusable UI components (headers, footers) to maintain DRY principles across the frontend.
  - `users/`: Handles authentication (login/registration), session management, password hashing, and profile editing (`User` entity).
  - `utils/`: Contains the core `Database.php` (PDO Singleton connection) and the administrative control panel view.
  - `contact.php`: Public contact page integrated with the dynamic booking calendar.
  - `index.php`: The coffee shop's main entry point and landing page.
  - `menu.php`: The public catalog showcase displaying categories and products.
- `.env.example`: Template for required environment variables (Database connection details).
- `docker-compose.yaml` & `Dockerfile`: Fully containerized setup integrating the PHP/Apache server with the MySQL database environment.


## Features

- **Object-Oriented Data Hydration**: Leverages advanced PDO fetch modes (`PDO::FETCH_CLASS`) paired with SQL aliasing to directly map relational database rows into strictly typed PHP entity objects (`Product`, `Category`, `Booking`, `User`).
- **Interactive Booking System**: A robust JavaScript-powered calendar that handles dynamic date selection, prevents booking on past dates or closed days (Sundays), and enforces capacity limits per hour seamlessly.
- **Role-Based Access Control (RBAC)**: Implements strict session checks across all routes, explicitly dividing capabilities between `admin` and `user`.
- **Hardened Security Measures**: Protects against SQL Injections using strict PDO Prepared Statements and hardcoded whitelist mapping arrays for all dynamic `ORDER BY` sorting operations.
- **Dynamic File Management**: Securely handles image uploads with size limitations, MIME type validation, and dynamic absolute path resolution for reliable rendering across all subdirectories.
- **Environment Agnostic**: Uses custom `.env` parsing logic to keep sensitive database credentials out of the source code, allowing seamless transitions between local Docker development and remote hosting.


## Running the Project

This application is fully containerized for easy deployment and testing.

1. Clone the repository and navigate to the project root.
2. Duplicate the `.env.example` file, rename it to `.env`, and insert your database credentials.
3. Build and start the Docker containers:
    ```bash
    docker compose up --build -d
    ```
4. Access the application by navigating to http://localhost (or your configured port) in your web browser.


## Test Credentials

To explore the different Role-Based Access Control (RBAC) permissions and test the platform's features, you can use the following pre-configured database accounts (assuming default seed data):

| Email | Password | Role | Status |
| :--- | :--- | :--- | :--- |
| `jose@onix.com` | `onix1433` | Administrator | Active |
| `alicia@onix.com` | `alicia2522` | User | Active |