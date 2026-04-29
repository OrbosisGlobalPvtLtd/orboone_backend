<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Account Created</title>

<style>

body{
    margin:0;
    padding:0;
    background:#f4f6f9;
    font-family:Arial, Helvetica, sans-serif;
}

.email-container{
    width:100%;
    max-width:650px;
    margin:auto;
    background:#ffffff;
    border-radius:8px;
    overflow:hidden;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}

.header{
    background:#1560ab;
    padding:25px;
    text-align:center;
}

.header img{
    height:60px;
}

.header h1{
    color:white;
    margin-top:10px;
    font-size:22px;
}

.content{
    padding:30px;
}

.content h2{
    color:#1560ab;
    margin-bottom:10px;
}

.content p{
    font-size:15px;
    color:#555;
}

.credential-box{
    background:#f8f9fb;
    border:1px solid #e2e6ea;
    border-radius:6px;
    padding:20px;
    margin-top:20px;
}

.credential-table{
    width:100%;
    border-collapse:collapse;
}

.credential-table td{
    padding:10px;
    border-bottom:1px solid #e6e6e6;
    font-size:14px;
}

.credential-table td:first-child{
    font-weight:bold;
    color:#333;
}

.highlight{
    color:#1560ab;
    font-weight:bold;
}

.login-btn{
    display:inline-block;
    margin-top:25px;
    background:#1560ab;
    color:white;
    padding:12px 25px;
    border-radius:4px;
    text-decoration:none;
    font-size:14px;
}

.footer{
    background:#f1f1f1;
    text-align:center;
    padding:15px;
    font-size:13px;
    color:#777;
}

</style>

</head>

<body>

<div class="email-container">

    <!-- HEADER -->
    <div class="header">
        <img src="https://spa.orbosis.in/public/images/logo-hrm.png">
        <h1>Orbosis Global Pvt Ltd</h1>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <h2>Welcome to Orbosis Global Pvt Ltd</h2>

        <p>Hello <strong>{{ $name }}</strong>,</p>

        <p>
        We are pleased to welcome you to <b>Orbosis Global Pvt Ltd</b>.  
        Your employee account has been successfully created in our HRMS system.
        </p>

        <p>
        Below are your login credentials to access the employee portal.
        Please keep this information secure.
        </p>

        <!-- CREDENTIALS -->
        <div class="credential-box">

            <table class="credential-table">

                <tr>
                    <td>Email Address</td>
                    <td class="highlight">{{ $email }}</td>
                </tr>

                <tr>
                    <td>Employee Number</td>
                    <td class="highlight">{{ $empid }}</td>
                </tr>

                <tr>
                    <td>Password</td>
                    <td class="highlight">{{ $password }}</td>
                </tr>

            </table>

        </div>

        <p style="margin-top:20px;">
        For security reasons, we recommend changing your password after your first login.
        </p>

        <center>
            <a href="#" class="login-btn">Login To Employee Portal</a>
        </center>

    </div>

    <!-- FOOTER -->
    <div class="footer">

        © {{ date('Y') }} Orbosis Global Pvt Ltd  
        <br>
        Human Resource Management System

    </div>

</div>

</body>
</html>