<?php
include('config.php');

if (isset($_POST['userlog'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $address = $_POST['address'];
    $landmark = $_POST['landmark'];
    $total = $_POST['total_amount'];
    $servicespost = json_decode($_POST['servicespost'], true); // Decode the JSON string into an associative array

    // Check if the email already exists in the `users` table
    $check_email_stmt = $conn->prepare("SELECT email FROM `users` WHERE email = ?");
    if ($check_email_stmt === false) {
        die("Error preparing email check statement: " . $conn->error);
    }
    $check_email_stmt->bind_param("s", $email);
    if (!$check_email_stmt->execute()) {
        echo "Error: " . $check_email_stmt->error;
        exit;
    }
    $check_email_stmt->store_result();


    if ($check_email_stmt->num_rows > 0) {
        echo "";
        $check_email_stmt->close();
        exit;
    }
    $check_email_stmt->close();




    // Prepare and execute the first statement for inserting into `users` table
    $stmt1 = $conn->prepare("INSERT INTO `users` (name, email, password) VALUES (?, ?, ?)");
    if ($stmt1 === false) {
        die("Error preparing statement 1: " . $conn->error);
    }
    $stmt1->bind_param("sss", $name, $email, $password);
    if (!$stmt1->execute()) {
        echo "Error: " . $stmt1->error;
        exit;
    }




    // Get the auto-generated ID from the `users` table
    $customer_id = $stmt1->insert_id;




    // Prepare and execute the second statement for inserting into `addresses` table
    $stmt2 = $conn->prepare("INSERT INTO `addresses` (address, landmark, customer_id) VALUES (?, ?, ?)");
    if ($stmt2 === false) {
        die("Error preparing statement 2: " . $conn->error);
    }
    $stmt2->bind_param("ssi", $address, $landmark, $customer_id);
    if (!$stmt2->execute()) {
        echo "Error: " . $stmt2->error;
        exit;
    }




    // Get the auto-generated ID from the `addresses` table
    $address_id = $stmt2->insert_id;




    // Process each service in the `servicespost` array
    foreach ($servicespost as $service) {
        $service_id = $service['id'];
        $price = $service['price'];
        // Get service_category_id dynamically
        $stmt = $conn->prepare("SELECT id FROM service_categories WHERE id = ?");
        if ($stmt === false) {
            die("Error preparing statement for service category selection: " . $conn->error);
        }
        $stmt->bind_param("i", $service_id);
        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
            exit;
        }
        $stmt->bind_result($service_category_id);
        $stmt->fetch();
        $stmt->close();




       




        // Prepare and execute the statement for inserting into `service_requests` table
        $stmt3 = $conn->prepare("INSERT INTO `service_requests` (customer_id, address_id, service_category_id, total, created_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)");
        if ($stmt3 === false) {
            die("Error preparing statement 3: " . $conn->error);
        }
        $stmt3->bind_param("iiid", $customer_id, $address_id, $service_category_id, $price); // Used $price instead of $total for individual service
        if (!$stmt3->execute()) {
            echo "Error: " . $stmt3->error;
            exit;
        }
        $stmt3->close();




   
    }




    // Close statements and connection
    $stmt1->close();
    $stmt2->close();
    mysqli_close($conn);
   
    echo '<script>window.open("thank.php", "_blank");</script>';
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script>
       const services = {
    "AC Service": [
        { "id": 1, "name": "periodic Services", "price": 1000 },
        { "id": 2, "name": "AC Service & Repair", "price": 2000 }
    ],
    "Periodic Services": [
        { "id": 3, "name": "Car & Bike Wash", "price": 1500 },
        { "id": 4, "name": "Tyre & Wheel Care", "price": 2500 }
    ],
    "Detailing & Care": [
        { "id": 5, "name": "Detailing Services", "price": 800 },
        { "id": 6, "name": "Denting & Paintings", "price": 1200 }
    ],
    "Accessories": [
        { "id": 7, "name": "Glass Replacement", "price": 1000 },
        { "id": 8, "name": "Batteries", "price": 1200 }
    ],
    "Exhaust Maintenance": [
        { "id": 9, "name": "Suspension Exhaust Maintenance", "price": 2000 },
        { "id": 10, "name": "Vehicle Inspections", "price": 2500 }
    ],
 
};


function validateAccountStep(btn) {
            var name = document.getElementById("name").value;
            var email = document.getElementById("email").value;
            var password = document.getElementById("password").value;
            var cpwd = document.getElementById("cpwd").value;
   
           
            var isValid = true;




            if (name === "") {
                document.getElementById("name1").innerHTML = "**Please enter first name";
                isValid = false;
            } else {
                document.getElementById("name1").innerHTML = "";
            }




            if (email === "") {
                document.getElementById("email1").innerHTML = "**Fill the Email";
                isValid = false;
            } else {
                document.getElementById("email1").innerHTML = "";
            }


            var pass = document.getElementById("password").value;
             (pass);
            if (pass == "") {
                document.getElementById("password1").innerHTML = "**Please enter password!";
                isValid = false;
            } else if (password.length < 8) {
                document.getElementById("password1").innerHTML = "**Password length must be at least 8 characters";
                isValid = false;
            } else if (password.length > 15) {
                document.getElementById("password1").innerHTML = "**Password length must not exceed 15 characters";
                isValid = false;
            } else {
                document.getElementById("password1").innerHTML = "";
            }


            var connpass = document.getElementById("cpwd").value;
            (connpass);
            if (connpass == "") {
                document.getElementById("cpwd1").innerHTML = "**Enter the password please!";
                isValid = false;
            } else if (pass != connpass) {
                document.getElementById("cpwd1").innerHTML = "**Passwords are not the same";
                isValid = false;
            } else {
                document.getElementById("cpwd1").innerHTML = "";
            }


           


            if(isValid == false)
            {
                (isValid);
                return false;
            }




            if (pass == connpass) {
                nextStep(btn);
            }
        }




        function validateAddressStep(btn) {
            var address = document.getElementById("address").value;
            var landmark = document.getElementById("landmark").value;
           
            var isValid = true;




            if (address === "") {
                document.getElementById("address1").innerHTML = "**Please enter the full address.";
                isValid = false;
            }




            if (landmark === "") {
                document.getElementById("landmark1").innerHTML = "****Please enter the Landmark!";
                isValid = false;
            }




            if (isValid) {
                nextStep(btn);
            }
        }


        function validateSuperCategoryStep(btn) {
            if (subCategoryCount<1){
                alert("Please select at least one super category.");
                return false;
            }
            else if (superCategoryCount>5){
                alert("You can select a maximum of five services");
                return false;


            }


            nextStep(btn);
        }


        function validateServiceStep(btn) {
            // calculateTotal();
            validateSuperCategoryStep(btn);
        }

          
        // var a1 = document.querySelector("#find_total");
        // a1.addEventListener("click", function() {
        //     a1.innerHTML = "CONFIRMED!";
        // });

        function nextStep(btn) {
            var current_fs, next_fs;
            current_fs = $(btn).parent();
            next_fs = $(btn).parent().next();




            $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
            next_fs.show();
            current_fs.animate({opacity: 0}, {
                step: function(now) {
                    var opacity = 1 - now;
                    current_fs.css({
                        'display': 'none',
                        'position': 'relative'
                    });
                    next_fs.css({'opacity': opacity});
                },
                duration: 600
            });
        }
let superCategoryCount = 0;
let subCategoryCount = 0;
function addService() {
    const serviceContainer = document.getElementById('service-container');
    const serviceGroup = document.createElement('div');
    serviceGroup.className = 'service-group col-md-12';
   
    const superCategorySelect = document.createElement('select');
    superCategorySelect.className = 'selectsuperservice form-control';
    superCategorySelect.required = true;
    superCategorySelect.name = 'super_category[]'; // Use an array if multiple selections are possible
    superCategorySelect.onchange = () => updateSubcategories(superCategorySelect, subCategorySelect);
    superCategorySelect.innerHTML = `<option selected disabled>Select a Super Category</option>` +
        Object.keys(services).map(cat => `<option value="${cat}">${cat}</option>`).join('');


        superCategoryCount++;
        if (superCategoryCount >= 3) {
        document.getElementById('add-service').style.display = 'none';
        document.getElementById('add-service1').innerHTML = "*Your service limit has been exceeded.";
    }


    const subCategorySelect = document.createElement('select');
    subCategorySelect.className = 'selectservice form-control';
    subCategorySelect.required = true;
    subCategorySelect.name = 'sub_category[]'; // Use an array if multiple selections are possible
    subCategorySelect.innerHTML = `<option selected disabled>Select a Sub Category</option>`;
     
   


    serviceGroup.appendChild(superCategorySelect);
    serviceGroup.appendChild(subCategorySelect);
    serviceContainer.appendChild(serviceGroup);
}
        function updateSubcategories(superCategorySelect, subCategorySelect) {
            const superCategory = superCategorySelect.value;
            subCategorySelect.innerHTML = '';




            if (superCategory && services[superCategory]) {
                services[superCategory].forEach(service => {
                    const option = document.createElement('option');
                    option.value = service.price;
                    option.id=service.id;
                    console.log(service);
                    option.textContent = `${service.name}`;
                    console.log(option);
                    subCategorySelect.appendChild(option);
                    subCategoryCount++;
                });
            }
        }

function calculateTotal() {
    console.log("shdvheb");
    console.log();
    const subCategorySelects = document.querySelectorAll('#service-container select:nth-child(2)');
    let total = 0;
    let selectedServices = [];
    let serviceCategoryId = null;

    subCategorySelects.forEach(select => {
        console.log(select.options[select.selectedIndex]);
        const serviceId = select.options[select.selectedIndex].id; // Get the id from dataset
        const categoryId = select.options[select.selectedIndex].id;  // Get the category id from dataset
        const price = parseInt(select.value || 0);
        total += price;
        console.log(serviceId,select.id);
        selectedServices.push({ id: serviceId, price: price }); // Push id and price to selectedServices array




        // Set the service category id if not already set
        if (serviceCategoryId === null) {
            serviceCategoryId = categoryId;
        }
    });




    document.getElementById('total_amount_hidden').value = total;
    //document.getElementById('total_amount').textContent = `Total: ₹${total}`;




    // Store selected services in a hidden input field
    console.log("selectedServices",selectedServices);
    document.getElementById('servicespost').value = JSON.stringify(selectedServices); // Convert array to JSON string




    // Store service category id in a hidden input field
    document.getElementById('service_category_id').value = serviceCategoryId;
}




   
        $(document).ready(function(){
            var current_fs, next_fs, previous_fs; //fieldsets
            var opacity;
           
            $(".next").click(function(){
               
                current_fs = $(this).parent();
                next_fs = $(this).parent().next();
               
                //Add Class Active
                $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
               
                //show the next fieldset
                next_fs.show();
                //hide the current fieldset with style
                current_fs.animate({opacity: 0}, {
                    step: function(now) {
                        // for making fielset appear animation
                        opacity = 1 - now;
       
                        current_fs.css({
                            'display': 'none',
                            'position': 'relative'
                        });
                        next_fs.css({'opacity': opacity});
                    },
                    duration: 600
                });
            });
           
            $(".previous").click(function(){
               
                current_fs = $(this).parent();
                previous_fs = $(this).parent().prev();
               
                //Remove class active
                $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");
               
                //show the previous fieldset
                previous_fs.show();
       
                //hide the current fieldset with style
                current_fs.animate({opacity: 0}, {
                    step: function(now) {
                        // for making fielset appear animation
                        opacity = 1 - now;
       
                        current_fs.css({
                            'display': 'none',
                            'position': 'relative'
                        });
                        previous_fs.css({'opacity': opacity});
                    },
                    duration: 600
                });
            });
           
            $(".submit").click(function(){
                return false;
            })      
            
                document.getElementById('add-service').onclick = addService;
                // document.getElementById('find_total').onclick = calculateTotal;
                var a1 = document.querySelector("#find_total");
                a1.addEventListener("click", function() {
                calculateTotal();
                a1.innerHTML = "Confirmed!";
                a1.style.color = "white"; // Correct color value
                a1.style.backgroundColor = "green";
});


            }); 
        
        // document.getElementById("find_total").addEventListener("click", function() {
        //     this.classList.add("green-button");
        // });

    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }


        html {
            height: 100%;
        }

        body {
            background-color: #9C27B0;
            background-image: linear-gradient(120deg, #FF4081, #81D4FA);
        }

        #msform {
            text-align: center;
            position: relative;
            margin-top: 20px;
        }
        #msform fieldset .form-card {
            background: white;
            border: 0 none;
            border-radius: 0px;
            box-shadow: 0 0 2px 2px rgba(0, 0, 0, 0.10);
            padding:15px;
            box-sizing: border-box;
            margin: 0;
            position: relative;
        }
        #msform fieldset {
            background: white;
            border: 0 none;
            border-radius: 0.5rem;
            box-sizing: border-box;
            width: 100%;
            margin: 0;
            padding: 15px;
            position: relative;
        }
        #msform fieldset:not(:first-of-type) {
            display: none;
        }
        #msform fieldset .form-card {
            text-align: left;
            color: #9E9E9E;
        }




        #msform input, #msform textarea {
            padding: 0px 8px 4px 8px;
            border: none;
            border-bottom: 1px solid #ccc;
            border-radius: 0px;
            margin-bottom: 15px;
            margin-top: 2px;
            width: 100%;
            box-sizing: border-box;
            font-family: montserrat;
            color: #2C3E50;
            font-size: 16px;
            letter-spacing: 1px;
        }
        #msform input[type='button'] {
    width: auto;
    margin: 10px;
    color: #fff;
    font-family: arial;
    text-transform: uppercase;
    padding: 5px 15px;
    border-radius: 2px;
}
        #msform input:focus, #msform textarea:focus {
            -moz-box-shadow: none !important;
            -webkit-box-shadow: none !important;
            box-shadow: none !important;
            border: none;
            font-weight: bold;
            border-bottom: 2px solid skyblue;
            outline-width: 0;
        }




        #msform .action-button {
            width: 100px;
            background: skyblue;
            font-weight: bold;
            color: white;
            border: 0 none;
            border-radius: 0px;
            cursor: pointer;
            padding: 10px 5px;
            margin: 10px 5px;
        }




        #msform .action-button:hover, #msform .action-button:focus {
            background-color: #45a9ff;
        }




        #msform .action-button-previous {
            width: 100px;
            background: #616161;
            font-weight: bold;
            color: white;
            border: 0 none;
            border-radius: 0px;
            cursor: pointer;
            padding: 10px 5px;
            margin: 10px 5px;
        }




        #msform .action-button-previous:hover, #msform .action-button-previous:focus {
            background-color: #000000;
        }




        select.list-dt {
            border: none;
            outline: 0;
            border-bottom: 1px solid #ccc;
            padding: 2px 5px 3px 5px;
            margin: 2px;
        }




        select.list-dt:focus {
            border-bottom: 2px solid skyblue;
        }




        .card {
            z-index: 0;
            border: none;
            border-radius: 0.5rem;
            position: relative;
        }




        .fs-title {
            font-size: 25px;
            color: #2C3E50;
            margin-bottom: 10px;
            font-weight: bold;
            text-align: left;
        }




        #progressbar {
            margin-bottom: 15px;
            overflow: hidden;
            color: lightgrey;
        }




        #progressbar .active {
            color: #000000;
        }




        #progressbar li {
            list-style-type: none;
            font-size: 12px;
            width: 25%;
            float: left;
            position: relative;
        }




        #progressbar #account:before {
            font-family: FontAwesome;
            content: "\f007";
        }




        #progressbar #personal:before {
            font-family: FontAwesome;
            content: "\f041";
        }




        #progressbar #payment:before {
            font-family: FontAwesome;
            content: "\f013";
        }




        #progressbar #confirm:before {
            font-family: FontAwesome;
            content: "\f00c";
        }




        #progressbar li:before {
            width: 50px;
            height: 50px;
            line-height: 45px;
            display: block;
            font-size: 18px;
            color: #ffffff;
            background: lightgray;
            border-radius: 50%;
            margin: 0 auto 5px auto;
            padding: 2px;
        }




        #progressbar li:after {
            content: '';
            width: 100%;
            height: 2px;
            background: lightgray;
            position: absolute;
            left: 0;
            top: 25px;
            z-index: -1;
        }




        #progressbar li.active:before, #progressbar li.active:after {
            background: #006791;
        }




        .radio-group {
            position: relative;
            margin-bottom: 25px;
        }




        .radio {
            display: inline-block;
            width: 204;
            height: 104;
            border-radius: 0;
            background: lightblue;
            box-shadow: 0 2px 2px 2px lightgray;
            box-sizing: border-box;
            cursor: pointer;
            margin: 8px 2px;
        }




        .radio:hover {
            box-shadow: 0 2px 2px 2px skyblue;
        }




        .radio.selected {
            box-shadow: 0 2px 2px 2px skyblue;
        }




        .fit-image {
            width: 100%;
            object-fit: cover;
        }
        .text-center{
            text-align:center;
        }

        .green-button {
            background-color: green;
            color: white;
        }

</style>
</head>
<body>
    <div class="container-fluid" id="grad1">
        <div class="row justify-content-center mt-0">
            <div class="col-11 col-sm-9 col-md-7 col-lg-6 text-center p-0 mt-3 mb-2">
                <div class="card px-0 pt-4 pb-0 mt-3 mb-3">
                    <div class="title text-center">
                    <h2><strong>Sign Up Your User Account</strong></h2>
                    <p class="m-0">Fill all form field to go to next step</p>
                    </div>
                    <form id="msform" action=""  method="post"  >
                        <!-- progressbar -->
                        <ul id="progressbar">
                            <li class="active" id="account"><strong>Account</strong></li>
                            <li id="personal"><strong>Address</strong></li>
                            <li id="payment"><strong>Service</strong></li>
                            <li id="confirm"><strong>Finish</strong></li>
                        </ul>
                        <!-- fieldsets -->
                        <fieldset>
                            <div class="form-card">
                                <h2 class="fs-title">Account Information</h2>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" id="name" name="name" placeholder="Name" required />
                                        <span id="name1" style="color:red"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="email" id="email" name="email" placeholder="Email" required />
                                        <span id="email1" style="color:red"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="password" id="password" name="password" placeholder="Password" required />
                                        <span id="password1" style="color:red"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="password" id="cpwd" name="cpwd" placeholder="Confirm Password" required />
                                        <span id="cpwd1" style="color:red"></span>
                                    </div>
                                </div>
                            </div>
                            <input type="button" name="next" class="btn btn-primary" value="Next Step" onclick="validateAccountStep(this)" />
                        </fieldset>
                        <fieldset>
                            <div class="form-card">
                                <h2 class="fs-title">Address Information</h2>
                                <input type="text" id="address" name="address" placeholder="Address" required />
                                <span id="address1" style="color:red"></span>
                                <input type="text" id="landmark" name="landmark" placeholder="Landmark" required />
                                <span id="landmark1" style="color:red"></span>
                            </div>
                            <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                            <input type="button" name="next" class="btn action-button" value="Next Step" onclick="validateAddressStep(this)" />
                        </fieldset>
                        <fieldset>
                              <div class="form-card">
                               <h2 class="fs-title">Service Selection</h2>
                              <div id="service-container"></div>
                              <button type="button" id="add-service" name="selectservice" class="action-button">Add Service</button>
                              <span id="add-service1" style="color:red"></span>
                              <button type="button" id="find_total" class="action-button">Confirm Services!</button>                         
                              <input type="hidden" name="total_amount" id="total_amount_hidden" value="0" />
                              <input type="hidden" name="servicespost" id="servicespost">
                              <input type="hidden" name="service_category_id" id="service_category_id">                            
                              </div>
                              <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                              <input type="button" name="next" class="btn action-button" value="Next Step" onclick="validateServiceStep(this)"/>
                        </fieldset>
                        <fieldset>
                            <div class="form-card">
                               <h2 class="fs-title">Finish</h2>
                               <button type="submit" name="userlog" class="action-button">Submit</button>
                            </div>
                            <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>









