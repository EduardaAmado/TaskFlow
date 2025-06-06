# TaskFlow

TaskFlow is a powerful and intuitive task management tool designed to help individuals and teams organize, prioritize, and track their tasks efficiently. With a user-friendly interface, TaskFlow simplifies the process of managing your daily activities, ensuring that you stay productive and focused on what matters most.

---

## 🚀 Features

- User registration and authentication
- Create, edit, and delete tasks
- Task priorities (low, medium, high)
- Deadline support
- Comment system for tasks
- Token system for session management

---

## 📁 Project Structure

```
taskflow/
│
├── public/                # Public entry point (index.php, login.php, etc.)
│   ├── assets/            # Static files (CSS, JS, images)
│
├── app/                   # Application logic
│   ├── controllers/       # Handles HTTP requests
│   ├── models/            # Database interaction (PDO/MySQLi)
│   ├── views/             # Layouts and page templates
│   ├── config/            # DB connection config
│   └── helpers/           # Utility functions
│
├── database/              # SQL scripts
│   ├── migrations.sql     # Table creation
│   └── seeds.sql          # Sample data (optional)
│
├── README.md              # Project documentation
└── .gitignore             # Git ignored files
```

---

## 🗄️ Database Setup

1. Create a new database using MySQL or PostgreSQL.

2. Run the migration script to create the necessary tables:

   **For MySQL:**

   ```bash
   mysql -u your_user -p your_database < database/migrations.sql
   ```

   **For PostgreSQL:**

   ```bash
   psql -U your_user -d your_database -f database/migrations.sql
   ```

3. *(Optional)* Populate the database with sample data:

   ```bash
   mysql -u your_user -p your_database < database/seeds.sql
   ```

---

### 🔧 Notes

- Make sure your database connection is properly configured in:
  ```
  app/config/db.php
  ```

- If you're using **PostgreSQL**, and `ENUM` types cause an error, uncomment or create the custom enum:

   ```sql
   CREATE TYPE priority_enum AS ENUM ('low', 'medium', 'high');
   ```

   And modify the column:
   ```sql
   priority priority_enum NOT NULL,
   ```

---

## 🧑‍💻 Development

You can run the project on a local PHP server:

```bash
php -S localhost:8000 -t public/
```

Then open your browser at: [http://localhost:8000](http://localhost:8000)

---

## 📦 Dependencies

- PHP 7.4+
- MySQL or PostgreSQL
- Composer (if needed for packages)
- TailwindCSS (compiled CSS in `assets/css/`)

---

## 📜 License

This project is licensed under the MIT License. Feel free to use and modify it as you wish.

---

## 🙌 Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.
