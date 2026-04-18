<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }
        
        @keyframes pulse-ring {
            0% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
            }
        }
        
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        
        .pulse-ring {
            animation: pulse-ring 2s infinite;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="bg-gray-50 overflow-hidden">
    <!-- Animated background -->
    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 gradient-bg opacity-5"></div>
        <svg class="absolute w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none">
            <path fill="#667eea" fill-opacity="0.05" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,144C960,149,1056,139,1152,128C1248,117,1344,107,1392,101.3L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path>
        </svg>
    </div>

    <!-- Main content -->
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <!-- Icon -->
            <div class="flex justify-center">
                <div class="relative w-24 h-24">
                    <div class="absolute inset-0 bg-blue-100 rounded-full pulse-ring"></div>
                    <div class="relative w-24 h-24 bg-gradient-to-br from-blue-50 to-blue-100 rounded-full flex items-center justify-center float-animation">
                        <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Heading -->
            <div class="space-y-2">
                <h1 class="text-4xl sm:text-5xl font-bold text-gray-900">
                    We'll Be Back Soon
                </h1>
                <p class="text-base sm:text-lg text-gray-500">
                    We're currently performing maintenance to serve you better.
                </p>
            </div>

            <!-- Status message -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    <span class="font-semibold">Expected downtime:</span> We expect to be back online shortly. Thank you for your patience!
                </p>
            </div>

            <!-- Features list -->
            <div class="space-y-3 pt-4">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm text-gray-600">Service optimization in progress</span>
                </div>
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm text-gray-600">New features coming soon</span>
                </div>
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm text-gray-600">Better performance guaranteed</span>
                </div>
            </div>

            <!-- Contact section -->
            <div class="space-y-3 pt-6">
                <p class="text-sm text-gray-600">
                    Questions? We're here to help!
                </p>
                <div class="flex gap-3 justify-center">
                    <a href="mailto:support@fdaapp.com" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white gradient-bg hover:shadow-lg transition-shadow duration-200">
                        Contact Support
                    </a>
                    <a href="#" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                        Learn More
                    </a>
                </div>
            </div>

            <!-- Loading indicator -->
            <div class="pt-6">
                <div class="flex justify-center space-x-2">
                    <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0s;"></div>
                    <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                    <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
                </div>
                <p class="text-xs text-gray-500 mt-4">Returning to normal operations...</p>
            </div>

            <!-- Footer -->
            <div class="pt-8 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    FDA Application &copy; {{ date('Y') }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
