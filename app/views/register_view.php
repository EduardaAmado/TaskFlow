<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .register-container {
            animation: fadeIn 0.8s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
        }
        .password-strength {
            height: 5px;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="register-container bg-white rounded-xl shadow-xl w-full max-w-md p-8 md:p-10 my-8">
        <div class="text-center mb-8">
            <div class="inline-block p-3 rounded-full bg-indigo-100 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Create an account</h1>
            <p class="text-gray-500 mt-2">Join us today and get started</p>
        </div>
        
        <form id="registerForm" action="../app/controllers/RegisterController.php" method="POST" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" id="firstName" name="firstName" required 
                        class="form-input w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-indigo-500 transition-colors" 
                        placeholder="First name">
                </div>
                <div>
                    <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" id="lastName" name="lastName" required 
                        class="form-input w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-indigo-500 transition-colors" 
                        placeholder="Last name">
                </div>
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" required 
                    class="form-input w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-indigo-500 transition-colors" 
                    placeholder="Enter your email">
                <p id="emailError" class="mt-1 text-sm text-red-600 hidden">Please enter a valid email address</p>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required 
                    class="form-input w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-indigo-500 transition-colors" 
                    placeholder="Create a password">
                <div class="mt-2 bg-gray-200 rounded-full overflow-hidden">
                    <div id="passwordStrength" class="password-strength bg-red-500 rounded-full" style="width: 0%"></div>
                </div>
                <p id="passwordHint" class="mt-1 text-xs text-gray-500">Password should be at least 8 characters with letters, numbers and symbols</p>
            </div>
            
            <div>
                <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required 
                    class="form-input w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-indigo-500 transition-colors" 
                    placeholder="Confirm your password">
                <p id="passwordMatchError" class="mt-1 text-sm text-red-600 hidden">Passwords do not match</p>
            </div>
            
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="terms" name="terms" type="checkbox" required
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label for="terms" class="text-gray-700">I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-500">Terms of Service</a> and <a href="#" class="text-indigo-600 hover:text-indigo-500">Privacy Policy</a></label>
                </div>
            </div>
            
            <div>
                <button type="submit" 
                    class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                    Create Account
                </button>
            </div>
        </form>
        
        <div class="mt-8 pt-6 border-t border-gray-200 text-center">
            <p class="text-gray-600">Already have an account? 
                <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500">Sign in</a>
            </p>
        </div>
        
        <div id="notification" class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm">Account created successfully! This is a demo registration page.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            let isValid = true;
            
            // Email validation
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('emailError').classList.remove('hidden');
                isValid = false;
            } else {
                document.getElementById('emailError').classList.add('hidden');
            }
            
            // Password match validation
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            if (password !== confirmPassword) {
                document.getElementById('passwordMatchError').classList.remove('hidden');
                isValid = false;
            } else {
                document.getElementById('passwordMatchError').classList.add('hidden');
            }
            
            if (isValid) {
                // Show success notification
                const notification = document.getElementById('notification');
                notification.classList.remove('hidden');
                
                // Hide notification after 3 seconds
                setTimeout(() => {
                    notification.classList.add('hidden');
                }, 3000);
            }
        });
        
        // Password strength meter
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            let strength = 0;
            
            if (password.length >= 8) strength += 25;
            if (password.match(/[a-z]+/)) strength += 25;
            if (password.match(/[A-Z]+/)) strength += 25;
            if (password.match(/[0-9]+/) || password.match(/[^a-zA-Z0-9]+/)) strength += 25;
            
            const strengthBar = document.getElementById('passwordStrength');
            strengthBar.style.width = strength + '%';
            
            // Change color based on strength
            if (strength <= 25) {
                strengthBar.className = 'password-strength bg-red-500 rounded-full';
            } else if (strength <= 50) {
                strengthBar.className = 'password-strength bg-orange-500 rounded-full';
            } else if (strength <= 75) {
                strengthBar.className = 'password-strength bg-yellow-500 rounded-full';
            } else {
                strengthBar.className = 'password-strength bg-green-500 rounded-full';
            }
        });
    </script>
</body>
</html>
