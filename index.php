<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Data</title>
 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="DataTables/datatables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
  
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Employee Data</h2>
        <div class="table-responsive">
            <table id="employeesTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Gender</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be populated here by DataTables -->
                </tbody>
            </table>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="DataTables/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function () {
        $('#employeesTable').DataTable({
            "processing": true,
            "serverSide": true,
            "searching": true,
            "responsive":true,
            "ajax": "server_processing.php",
            "deferRender":true,
            "columns": [
                { "data": 0 },
                { "data": 1 },
                { "data": 2 },
                { "data": 3 },
                { "data": 4 }
            ]
        });

       
    });
    </script>
</body>
</html>
