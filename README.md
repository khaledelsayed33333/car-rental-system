##### 🚗 Car Rental System



A PHP-MySQL based Car Rental Web Application that allows users to register, browse available cars, make reservations, pay using wallet balance, and manage their profiles. Admins have a full dashboard to manage cars, users, reservations, and news.

---

## 📂 Project Structure

```bash
car-rental-system/
│
├── img/                     # Static images used in the site
├── uploads/                 # Uploaded car/news images
├── add_car.php              # Admin: Add new car
├── add_news.php             # Admin: Post news/announcements
├── admin_dashboard.php      # Admin home page
├── change_password.php      # User password update
├── db_connection.php        # MySQL DB connection file
├── delete_car.php           # Admin: Delete a car
├── edit_car.php             # Admin: Edit car info
├── edit_user.php            # Admin: Edit user info
├── fund_wallet.php          # Add balance to wallet
├── index.php                # Main landing page
├── login.php                # User login
├── logout.php               # User logout
├── manage_cars.php          # Admin: Manage cars
├── manage_news.php          # Admin: Manage news
├── manage_reservations.php  # Admin: Manage reservations
├── manage_users.php         # Admin: Manage users
├── my_reservation.php       # User: View reservations
├── payment.php              # Process payments
├── README.md                # Project overview (you're here!)
├── register.php             # User registration
├── reservation.php          # Reserve form handler
├── reserve_car.php          # User: Select and reserve a car
├── search_car.php           # Search functionality
├── SQL.txt                  # SQL dump for database setup
├── test_connection.php      # Test DB connection
├── update_profile.php       # Edit user profile
├── user_profile.php         # User profile page
├── wallet.php               # View wallet balance
```



## 💡 Features

* 🔐 User registration, login, and profile management
* 🛻 Car listings with search and reservation system
* 💳 Wallet balance & payment tracking
* 🧾 Admin dashboard to manage users, cars, reservations, and news
* 📰 News and announcements system
* 📥 Image upload support for cars and news

## ⚙️ Tech Stack

* **Frontend:** HTML, CSS, Bootstrap
* **Backend:** PHP
* **Database:** MySQL
* **Tools:** XAMPP, phpMyAdmin

## 🚀 Getting Started

1. **Clone the repository**
   git clone https://github.com/khaledelsayed33333/real-estate-app
   cd car-rental-system
2. **Import the database**

   * Open `phpMyAdmin`
   * Create a new database `crental_system`
   * Import `SQL.txt`
3. **Configure database**

   * Update `db_connection.php` with your MySQL credentials
4. **Run the project**

   * Start XAMPP (Apache & MySQL)
   * Visit `http://localhost/car-rental-system/`

   ## 🔐 Admin Access (Demo)

   To add an admin manually:

   INSERT INTO users (first_name, last_name, email, password, role)
   VALUES ('Admin', 'User', 'admin@demo.com', MD5('admin123'), 'admin');


<img src="img/test.gif" width="100%" />


## 🧑‍💻 Author

* **Khaled Elsayed**
* [GitHub](https://github.com/khaledelsayed33333)
* [LinkedIn](https://www.linkedin.com/in/khaled-elsayed-a15b53359)

📄 License
This project is created for educational use. Feel free to modify or expand it.
