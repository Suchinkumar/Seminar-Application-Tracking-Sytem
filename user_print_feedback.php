<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 20px;
        }
        .overview-box {
            border: 1px solid black;
            padding: 10px;
            margin-top: 10px;
            width: 97%; /* Adjust width as needed */
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1); /* Optional: adds a shadow for better visual appearance */
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2, h3 {
            color: #73007b;
        }

        h2, h3 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #00509e;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #0965bfab;
            color: #ffffff;
        }

        /* td {
            background-color: #f0f8ff;
        } */
/* 
        tr:nth-child(even) td {
            background-color: #d1e7ff;
        } */

        tr:hover td {
            background-color: #b3d4ff;
        }

        .print-button {
            /* display: block; */
            /* width: 100%; */
            margin: 20px 0;
            padding: 10px;
            background-color: #00509e;
            color: #ffffff;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
            padding: 10px;
        }
        .header-table .left {
            width: 50%;
        }
        .header-table .right {
            width: 50%;
            border-left: 1px solid black; /* Vertical line */
        }
        .left-aligned {
            text-align: left;
        }
        .organization-info {
            text-align: center;
            font-weight: bold;
            font-size: 1.2em; /* Adjust font size as needed */
        }

        .pc {
            text-align: center;
        }

        .pt {
            
            color:#000000;
            font-size: 15px;
            font-weight: bold;
        }

        .sig {
            text-align: center;
            margin-top: 50px;
        }
        .sig-c {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td class="left">
                    <p><strong>Centre for Fire, Explosive & Environment Safety</strong></p>
                    <p>Delhi</p>
                    <p><strong>QUALITY FORMAT - TCP&HR</strong></p>
                    <p>Section Title: Feedback Report of Participants</p>
                </td>
                <td class="right">
                    <p>Doc. No.: QF/TCP&HRG/HRG/Feedback</p>
                    <p>Report</p>
                    <p>Issue No.:</p>
                    <p>Issue Date: </p>
                    <p>Rev. No.:</p>
                    <p>Rev. Date:</p>
                </td>
            </tr>
        </table>

        <!-- <h2>Feedback Report of Participants</h2> -->
    <p class="organization-info">Centre for Fire, Explosive & Environment Safety</p>
    <p class="organization-info">DRDO, Ministry of Defence, Government of India</p>
    <p class="organization-info">Brig. SK Mazumdar Marg, Timarpur</p>
    <p class="organization-info">Delhi - 110054</p>
        <h3>Feedback Report</h3>
        <?php
        // Database connection parameters
        $servername = "localhost"; // replace with your server name
        $username = "root"; // replace with your database username
        $password = ""; // replace with your database password
        $dbname = "permission_request_system"; // replace with your database name

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Get the id from the request (e.g., from a GET request)
        $submitted_by = $_GET['submitted_by']; // replace with appropriate method to get submitted_by
        $seminar_id = urldecode($_GET['seminar_id']);  // replace with appropriate method to get seminar_id

        // Query to get the specific row by submitted_by and seminar_id
        $sql = "SELECT * FROM feedback WHERE submitted_by = ? AND seminar_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $submitted_by, $seminar_id);
        $stmt->execute();
        $result = $stmt->get_result();


        if ($result->num_rows > 0) {
            // Fetch the row
            $row = $result->fetch_assoc();
        ?>
        <table>
            <tr>
                <td>Name</td>
                <td><?php echo htmlspecialchars($row['submitted_by']); ?></td>
            </tr>
            <tr>
                <td>Designation</td>
                <td><?php echo htmlspecialchars($row['designation']); ?></td>
            </tr>
            <tr>
                <td>Group</td>
                <td><?php echo htmlspecialchars($row['user_group']); ?></td>
            </tr>
        </table>

        <p class="pt">Type of Training Event:</p>
        <table>
            <tr>
                <td><?php echo $row['programme_type'] == 'CEP' ? '✓' : ''; ?> CEP</td>
                <td><?php echo $row['programme_type'] == 'Course' ? '✓' : ''; ?> Course</td>
                <td><?php echo $row['programme_type'] == 'Training' ? '✓' : ''; ?> Training</td>
                <td><?php echo $row['programme_type'] == 'Conference' ? '✓' : ''; ?> Conference</td>
                <td><?php echo $row['programme_type'] == 'Seminar' ? '✓' : ''; ?> Seminar</td>
                <td><?php echo $row['programme_type'] == 'Workshop' ? '✓' : ''; ?> Workshop</td>
                <td><?php echo $row['programme_type'] == 'Symposium' ? '✓' : ''; ?> Symposium</td>
                <td><?php echo $row['programme_type'] == 'Meeting' ? '✓' : ''; ?> Meeting</td>
            </tr>
        </table>

        <h3>Details of the Programme</h3>
        <table>
            <tr>
                <td>Complete Title of the Programme</td>
                <td><?php echo htmlspecialchars($row['programme_title']); ?></td>
            </tr>
            <tr>
                <td>Organising Institution</td>
                <td><?php echo htmlspecialchars($row['location']); ?></td>
            </tr>
            <tr>
                <td>No. of Days</td>
                <td><?php echo htmlspecialchars($row['no_of_days']); ?></td>
            </tr>
            <tr>
                <td>From</td>
                <td><?php echo htmlspecialchars($row['from_date']); ?></td>
            </tr>
            <tr>
                <td>To</td>
                <td><?php echo htmlspecialchars($row['to_date']); ?></td>
            </tr>
            <tr>
                <td>Training Fee (if any)</td>
                <td><?php echo htmlspecialchars($row['training_fee']); ?></td>
            </tr>
        </table>

        <h3>Brief overview of the Programme (Up to 200 words)</h3>
        <p class="overview-box"><?php echo htmlspecialchars($row['overview']); ?></p>
        
        <h3>Feedback</h3>
        <table>
    <tr>
        <th>Quality Aspect</th>
        <th>Level of Satisfaction (1=Lowest, 5=Highest)</th>
    </tr>
    <tr>
        <td>Effectiveness of Training Module</td>
        <td>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <input type="radio" name="effectiveness" value="<?php echo $i; ?>" <?php echo ($row['effectiveness'] == $i) ? 'checked' : ''; ?>><?php echo $i; ?>
            <?php endfor; ?>
        </td>
    </tr>
    <tr>
        <td>Training Programme</td>
        <td>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <input type="radio" name="programme" value="<?php echo $i; ?>" <?php echo ($row['programme'] == $i) ? 'checked' : ''; ?>><?php echo $i; ?>
            <?php endfor; ?>
        </td>
    </tr>
    <tr>
        <td>Mode of conduct of Programme</td>
        <td>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <input type="radio" name="conduct" value="<?php echo $i; ?>" <?php echo ($row['conduct'] == $i) ? 'checked' : ''; ?>><?php echo $i; ?>
            <?php endfor; ?>
        </td>
    </tr>
    <tr>
        <td>Instructor's Performance</td>
        <td>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <input type="radio" name="instructor" value="<?php echo $i; ?>" <?php echo ($row['instructor'] == $i) ? 'checked' : ''; ?>><?php echo $i; ?>
            <?php endfor; ?>
        </td>
    </tr>
    <tr>
        <td>Training aids/Teaching aids</td>
        <td>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <input type="radio" name="aids" value="<?php echo $i; ?>" <?php echo ($row['aids'] == $i) ? 'checked' : ''; ?>><?php echo $i; ?>
            <?php endfor; ?>
        </td>
    </tr>
</table>

        
    <h3 class="left-aligned">Remark by Associate Director</h3>
    <p class="overview-box"><?php echo htmlspecialchars($row['ad_remarks']); ?></p>

    <h3 class="left-aligned">Remark by TCP-HR Head</h3>
    <p class="overview-box"><?php echo htmlspecialchars($row['tcp_hr_head_remarks']); ?></p>

    <h3 class="left-aligned">Remark by TCP-HR Associate Director</h3>
    <p class="overview-box"><?php echo htmlspecialchars($row['tcp_hr_ad_remarks']); ?></p>

        <!-- <p>Remark: Please attach the certificate of attendance and/or fee payment receipt. (If applicable)</p> -->

        <h3 class="sig">Signature</h3>
        <div class="sig-c">
            <p>Signature of Participant</p>
            
            <p>Signature of AD/Head-HR</p>
        </div>
       
        <div class="pc">
            <a href="javascript:window.print()" class="print-button">Print</a>
        </div>
        
        <?php
        } else {
            echo "<p>No record found.</p>";
        }

        $stmt->close();
        $conn->close();
        ?>
    </div>
</body>
</html>
