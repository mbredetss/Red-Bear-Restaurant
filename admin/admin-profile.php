<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Header -->
        <div class="bg-white p-6 rounded shadow flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="https://i.pravatar.cc/80" class="rounded-full w-20 h-20" />
                <div>
                    <h2 class="text-xl font-bold">Musharof Chowdhury</h2>
                    <p class="text-gray-500">Team Manager | Arizona, United States.</p>
                </div>
            </div>
            <button class="border rounded-full px-4 py-2 text-sm">✏️ Edit</button>
        </div>

        <!-- Personal Info -->
        <div class="bg-white p-6 rounded shadow">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Personal Information</h3>
                <button class="border rounded-full px-4 py-2 text-sm">✏️ Edit</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <p><strong>First Name:</strong> Chowdhury</p>
                <p><strong>Last Name:</strong> Musharof</p>
                <p><strong>Email:</strong> randomuser@pimjo.com</p>
                <p><strong>Phone:</strong> +09 363 398 46</p>
                <p><strong>Bio:</strong> Team Manager</p>
            </div>
        </div>

        <!-- Address Info -->
        <div class="bg-white p-6 rounded shadow">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Address</h3>
                <button class="border rounded-full px-4 py-2 text-sm">✏️ Edit</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <p><strong>Country:</strong> United States</p>
                <p><strong>City/State:</strong> Arizona, United States.</p>
                <p><strong>Postal Code:</strong> ERT 2489</p>
                <p><strong>Tax ID:</strong> AS4568384</p>
            </div>
        </div>

    </div>
</body>

</html>