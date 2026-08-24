<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin SignIn | Unicorn</title>

    <link rel="stylesheet" href="bootstrap.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css" />

    <link rel="icon" href="logo.jpg" />
</head>

<body style="background-color: #79aaf7;background-image: linear-gradient(90deg,#79aaf7 0%,#0b244d 100%);">

    <div class="container-fluid justify-content-center" style="margin-top: 20px;">
        <div class="row align-content-center">

            <div class="col-12 col-lg-4 mt-3 justify-content-center">
                <div class="mt-3 m-2 p-2 bg-primary bg-opacity-75 text-black col-12 rounded-3">
                    <a href="home.php" class="text-decoration-none text-black d-inline-flex align-items-center">
                        <img src="logo.jpg" class="logo" />
                        <label class="fs-2">Unicorn Book Shop</label>
                    </a>
                </div>


                <div class="rounded-3 shadow-lg m-2 mt-3 col-lg-12 d-none d-lg-block">
                    <img src="register.jpg" class="register bg-opacity-25" />
                </div>

            </div>
            <!--  -->
            <div class="col-12 col-lg-8 mt-4 justify-content-center mb-2">
                <div class="row mt-4">
                    <div class="col-12 mt-4 mb-4 d-none d-lg-block">
                        <p></p>
                    </div>
                    <div class="col-2">
                        <p></p>
                    </div>
                    <div class="col-8 d-block shadow align-items-center bg-primary bg-opacity-25 rounded-3 mt-1 p-4">

                        <div class="row">

                            <div class="col-12 text-center">
                                <img src="resource/permission.png"/>
                                <p class="title02">Admin Sign In.</p>
                            </div>
                            <div class="col-12 mt-2">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" placeholder="ex : admin@gmail.com" id="e" />
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" placeholder="Your admin password" id="p" />
                            </div>

                            <div class="col-12 text-center mt-2 bg-white bg-opacity-25 shadow text-danger fs-5 mb-2" id="message">

                            </div>

                            <div class="col-12 col-lg-6 d-grid mb-3 mt-2">
                                <button class="btn btn-primary" onclick="adminSignIn();">Sign In</button>
                            </div>
                            <div class="col-12 col-lg-6 d-grid mb-3 mt-2">
                                <a class="btn btn-danger" href="signIn.php">Back to Customer Log In</a>
                            </div>

                        </div>
                    </div>

                    <div class="col-lg-12 text-center text-black-50 mt-4 mb-2">
                        <label>Unicorn Book Shop | Solution by Nena Maharjan&copy; <?php echo (date("Y")) ?></label>
                    </div>
                </div>
            </div>

            <!--  -->

        </div>
    </div>

    <script src="bootstrap.bundle.js"></script>
    <script src="script.js"></script>
</body>

</html>
