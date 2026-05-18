@include('layouts.home.head')
<body class="animsition">

    @include('layouts.home.header')
    @include('layouts.home.newcart')

    <br><br><br><br><br>

    <!-- breadcrumb -->
    <div class="container">
        <div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
            <a href="{{ url('/') }}" class="stext-109 cl8 hov-cl1 trans-04">
                Home
                <i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
            </a>
            <a href="{{ url('/cart') }}" class="stext-109 cl8 hov-cl1 trans-04">
                Shopping Cart
                <i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
            </a>
            <span class="stext-109 cl4">
                Secure Checkout
            </span>
        </div>
    </div>

    <!-- Checkout -->
    <div class="bg0 p-t-75 p-b-85">
        <div class="container">
            <form method="POST" action="{{ route('checkout.store') }}" id="checkoutForm">
                @csrf
                
                <!-- Checkout Header -->
                <div class="row">
                    <div class="col-12 m-b-30">
                        <h2 class="ltext-103 cl5 txt-center">
                            Secure Checkout
                        </h2>
                        <p class="stext-111 cl6 txt-center p-t-5">
                            Complete your order by providing your payment details
                        </p>
                    </div>
                </div>

                <div class="row">
                    <!-- Left Column: Billing & Payment -->
                    <div class="col-lg-7 col-xl-7 m-lr-auto m-b-50">
                        
                        <!-- Billing Information Card -->
                        <div class="bor10 p-lr-40 p-t-30 p-b-40 m-lr-0-xl p-lr-15-sm m-b-30">
                            <div class="flex-w flex-sb-m p-b-25 bor12">
                                <h4 class="mtext-109 cl2">
                                    <i class="fa fa-user-o m-r-10"></i> Billing Details
                                </h4>
                                <span class="stext-111 cl6">* Required fields</span>
                            </div>

                            <div class="p-t-20">
                                <!-- Name & Email Row -->
                                <div class="row">
                                    <div class="col-sm-6 m-b-20">
                                        <label class="stext-111 cl3 d-block m-b-8">Full Name *</label>
                                        <div class="bor8 bg0">
                                            <input class="stext-111 cl8 plh3 size-111 p-lr-15" type="text" name="fullname" placeholder="Enter your full name" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 m-b-20">
                                        <label class="stext-111 cl3 d-block m-b-8">Email Address *</label>
                                        <div class="bor8 bg0">
                                            <input class="stext-111 cl8 plh3 size-111 p-lr-15" type="email" name="email" placeholder="example@email.com" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Phone -->
                                <div class="m-b-20">
                                    <label class="stext-111 cl3 d-block m-b-8">Phone Number *</label>
                                    <div class="bor8 bg0">
                                        <input class="stext-111 cl8 plh3 size-111 p-lr-15" type="tel" name="phone" placeholder="0300-1234567" required>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="m-b-20">
                                    <label class="stext-111 cl3 d-block m-b-8">Street Address *</label>
                                    <div class="bor8 bg0 m-b-12">
                                        <input class="stext-111 cl8 plh3 size-111 p-lr-15" type="text" name="address" placeholder="House #, Street, Area" required>
                                    </div>
                                </div>

                                <!-- City & Postcode Row -->
                                <div class="row">
                                    <div class="col-sm-6 m-b-20">
                                        <label class="stext-111 cl3 d-block m-b-8">City *</label>
                                        <div class="bor8 bg0">
                                            <input class="stext-111 cl8 plh3 size-111 p-lr-15" type="text" name="city" placeholder="Islamabad" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 m-b-20">
                                        <label class="stext-111 cl3 d-block m-b-8">Postcode / Zip *</label>
                                        <div class="bor8 bg0">
                                            <input class="stext-111 cl8 plh3 size-111 p-lr-15" type="text" name="postcode" placeholder="44000" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Country -->
                                <div class="m-b-10">
                                    <label class="stext-111 cl3 d-block m-b-8">Country *</label>
                                    <div class="rs1-select2 rs2-select2 bor8 bg0">
                                        <select class="js-select2" name="country" required>
                                            <option value="">Select a country...</option>
                                            <option value="PK" selected>Pakistan</option>
                                            <option value="USA">United States</option>
                                            <option value="UK">United Kingdom</option>
                                            <option value="AE">United Arab Emirates</option>
                                            <option value="SA">Saudi Arabia</option>
                                        </select>
                                        <div class="dropDownSelect2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods Card -->
                        <div class="bor10 p-lr-40 p-t-30 p-b-40 m-lr-0-xl p-lr-15-sm">
                            <div class="flex-w flex-sb-m p-b-25 bor12">
                                <h4 class="mtext-109 cl2">
                                    <i class="fa fa-credit-card m-r-10"></i> Payment Method
                                </h4>
                                <span class="stext-111 cl6">Select your preferred option</span>
                            </div>

                            <div class="p-t-20">
                                
                                <!-- Cash on Delivery Option -->
                                <div class="payment-option bor12 p-t-20 p-b-20 p-lr-25 m-b-20 pointer hov-bg9 transition-04" onclick="selectPayment('cod')" id="cod-card">
                                    <div class="flex-w flex-sb-m flex-align-center">
                                        <label class="flex-w flex-align-center pointer m-r-20">
                                            <input type="radio" name="payment_method" value="cod" id="pay-cod" class="m-r-15" checked onchange="togglePayment('cod')">
                                            <div>
                                                <span class="mtext-101 cl2 d-block">Cash on Delivery</span>
                                                <span class="stext-111 cl6">Pay cash when your order arrives at your doorstep</span>
                                            </div>
                                        </label>
                                        <div class="payment-icon">
                                            <i class="fa fa-money fa-2x cl3"></i>
                                        </div>
                                    </div>
                                    
                                    <div id="cod-details" class="p-t-15 p-l-35">
                                        <div class="bg9 p-lr-20 p-t-15 p-b-15 bor10">
                                            <p class="stext-111 cl6">
                                                <i class="fa fa-check-circle m-r-8 cl5"></i>
                                                No advance payment required
                                            </p>
                                            <p class="stext-111 cl6 p-t-5">
                                                <i class="fa fa-check-circle m-r-8 cl5"></i>
                                                Inspect your items before paying
                                            </p>
                                            <p class="stext-111 cl6 p-t-5">
                                                <i class="fa fa-check-circle m-r-8 cl5"></i>
                                                Available for all locations within Pakistan
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bank Transfer Option -->
                                <div class="payment-option bor12 p-t-20 p-b-20 p-lr-25 m-b-20 pointer hov-bg9 transition-04" onclick="selectPayment('bank')" id="bank-card">
                                    <div class="flex-w flex-sb-m flex-align-center">
                                        <label class="flex-w flex-align-center pointer m-r-20">
                                            <input type="radio" name="payment_method" value="bank" id="pay-bank" class="m-r-15" onchange="togglePayment('bank')">
                                            <div>
                                                <span class="mtext-101 cl2 d-block">Bank Transfer / Online Payment</span>
                                                <span class="stext-111 cl6">Pay via bank transfer, JazzCash, or EasyPaisa</span>
                                            </div>
                                        </label>
                                        <div class="payment-icon">
                                            <i class="fa fa-university fa-2x cl3"></i>
                                        </div>
                                    </div>

                                    <div id="bank-details" class="p-t-15 p-l-35">
                                        <!-- Bank Info Box -->
                                        <div class="bor10 p-lr-25 p-t-20 p-b-20 bg8 m-b-15">
                                            <h5 class="mtext-108 cl2 p-b-15">
                                                <i class="fa fa-university m-r-8"></i> Bank Account Details
                                            </h5>
                                            
                                            <div class="flex-w flex-t p-b-10">
                                                <div class="size-150">
                                                    <span class="stext-111 cl6">Bank Name:</span>
                                                </div>
                                                <div class="size-209">
                                                    <span class="mtext-110 cl2">HBL / Meezan Bank Limited</span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex-w flex-t p-b-10">
                                                <div class="size-150">
                                                    <span class="stext-111 cl6">Account Title:</span>
                                                </div>
                                                <div class="size-209">
                                                    <span class="mtext-110 cl2">NUST Donation Marketplace</span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex-w flex-t p-b-10">
                                                <div class="size-150">
                                                    <span class="stext-111 cl6">Account Number:</span>
                                                </div>
                                                <div class="size-209">
                                                    <span class="mtext-110 cl2 cl1">1234-5678-9012-3456</span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex-w flex-t p-b-10">
                                                <div class="size-150">
                                                    <span class="stext-111 cl6">IBAN:</span>
                                                </div>
                                                <div class="size-209">
                                                    <span class="mtext-110 cl2">PK36HBL1234567890123456</span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex-w flex-t p-b-10">
                                                <div class="size-150">
                                                    <span class="stext-111 cl6">Branch Code:</span>
                                                </div>
                                                <div class="size-209">
                                                    <span class="mtext-110 cl2">0123</span>
                                                </div>
                                            </div>

                                            <div class="p-t-10 bor12 p-t-15 m-t-10">
                                                <p class="stext-111 cl1">
                                                    <i class="fa fa-info-circle m-r-5"></i>
                                                    Please transfer the exact total amount and enter your transaction reference below.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Reference Input -->
                                        <div class="m-b-10">
                                            <label class="stext-111 cl3 d-block m-b-8">Transaction Reference / Receipt Number *</label>
                                            <div class="bor8 bg0">
                                                <input class="stext-111 cl8 plh3 size-111 p-lr-15" type="text" name="bank_reference" id="bank_reference" placeholder="e.g. TRX123456789 or Receipt #001122">
                                            </div>
                                        </div>
                                        
                                        <div class="m-b-10">
                                            <label class="stext-111 cl3 d-block m-b-8">Upload Payment Receipt (Optional)</label>
                                            <div class="bor8 bg0 p-lr-15 p-t-10 p-b-10">
                                                <input class="stext-111 cl8" type="file" name="payment_receipt" accept="image/*,.pdf">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mobile Wallets Info -->
                                <div class="bor10 p-lr-25 p-t-15 p-b-15 bg0">
                                    <p class="stext-111 cl6 txt-center">
                                        <i class="fa fa-mobile fa-lg m-r-8 cl5"></i>
                                        Also accept <strong>JazzCash</strong> & <strong>EasyPaisa</strong> transfers to above account
                                    </p>
                                </div>

                            </div>
                        </div>

                        <!-- Terms -->
                        <div class="p-t-20 p-lr-15">
                            <label class="flex-w flex-align-center pointer">
                                <input type="checkbox" name="terms" class="m-r-10" required id="termsCheck">
                                <span class="stext-111 cl6">I agree to the <a href="#" class="cl5 hov-cl1 trans-04">terms and conditions</a> and confirm my order details are correct.</span>
                            </label>
                        </div>
                    </div>

                    <!-- Right Column: Order Summary -->
                    <div class="col-sm-10 col-lg-5 col-xl-5 m-lr-auto m-b-50">
                        <div class="bor10 p-lr-30 p-t-30 p-b-40 m-l-63 m-r-40 m-lr-0-xl p-lr-15-sm sticky-top" style="top: 20px;">
                            
                            <div class="flex-w flex-sb-m p-b-25 bor12">
                                <h4 class="mtext-109 cl2">
                                    <i class="fa fa-shopping-bag m-r-10"></i> Order Summary
                                </h4>
                                <span class="stext-111 cl6">2 Items</span>
                            </div>

                            <!-- Product 1 -->
                            <div class="flex-w flex-t p-t-15 p-b-15 bor12">
                                <div class="size-100">
                                    <img src="templates/images/item-cart-04.jpg" alt="IMG" class="bor10" style="width: 80px; border-radius: 6px;">
                                </div>
                                <div class="size-209 p-l-15">
                                    <span class="mtext-101 cl2">Fresh Strawberries</span>
                                    <div class="flex-w flex-sb-m p-t-5">
                                        <span class="stext-111 cl6">Qty: 1</span>
                                        <span class="mtext-110 cl2">$36.00</span>
                                    </div>
                                    <span class="stext-111 cl6">Category: Organic Food</span>
                                </div>
                            </div>

                            <!-- Product 2 -->
                            <div class="flex-w flex-t p-t-15 p-b-15 bor12">
                                <div class="size-100">
                                    <img src="templates/images/item-cart-05.jpg" alt="IMG" class="bor10" style="width: 80px; border-radius: 6px;">
                                </div>
                                <div class="size-209 p-l-15">
                                    <span class="mtext-101 cl2">Lightweight Jacket</span>
                                    <div class="flex-w flex-sb-m p-t-5">
                                        <span class="stext-111 cl6">Qty: 1</span>
                                        <span class="mtext-110 cl2">$16.00</span>
                                    </div>
                                    <span class="stext-111 cl6">Category: Clothing</span>
                                </div>
                            </div>

                            <!-- Price Breakdown -->
                            <div class="p-t-20 p-b-20 bor12">
                                <div class="flex-w flex-t p-b-10">
                                    <div class="size-208">
                                        <span class="stext-110 cl2">Subtotal:</span>
                                    </div>
                                    <div class="size-209">
                                        <span class="mtext-110 cl2">$52.00</span>
                                    </div>
                                </div>

                                <div class="flex-w flex-t p-b-10">
                                    <div class="size-208">
                                        <span class="stext-110 cl2">Shipping:</span>
                                    </div>
                                    <div class="size-209">
                                        <span class="mtext-110 cl2">$15.00</span>
                                    </div>
                                </div>

                                <div class="flex-w flex-t p-b-10">
                                    <div class="size-208">
                                        <span class="stext-110 cl2">Tax (GST):</span>
                                    </div>
                                    <div class="size-209">
                                        <span class="mtext-110 cl2">$12.65</span>
                                    </div>
                                </div>

                                <div class="flex-w flex-t p-t-10">
                                    <div class="size-208">
                                        <span class="stext-110 cl2">Discount:</span>
                                    </div>
                                    <div class="size-209">
                                        <span class="mtext-110 cl2 cl5">-$0.00</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="flex-w flex-t p-t-25 p-b-20">
                                <div class="size-208">
                                    <span class="mtext-101 cl2" style="font-size: 20px;">Total:</span>
                                </div>
                                <div class="size-209 txt-right">
                                    <span class="mtext-110 cl2 cl1" style="font-size: 26px; font-weight: 700;">
                                        $79.65
                                    </span>
                                </div>
                            </div>

                            <div class="p-b-15 bor12 m-b-20">
                                <div class="bg9 p-lr-20 p-t-15 p-b-15 bor10">
                                    <p class="stext-111 cl6 txt-center">
                                        <i class="fa fa-shield m-r-5 cl5"></i>
                                        Secure SSL Encrypted Transaction
                                    </p>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04 pointer m-b-15" id="placeOrderBtn">
                                <i class="fa fa-lock m-r-8"></i> Place Order Now
                            </button>

                            <div class="txt-center m-b-15">
                                <img src="templates/images/icons/icon-pay-01.png" alt="Payment Methods" class="m-r-5" style="height: 30px; opacity: 0.6;">
                                <img src="templates/images/icons/icon-pay-02.png" alt="Payment Methods" class="m-r-5" style="height: 30px; opacity: 0.6;">
                                <img src="templates/images/icons/icon-pay-03.png" alt="Payment Methods" class="m-r-5" style="height: 30px; opacity: 0.6;">
                                <img src="templates/images/icons/icon-pay-04.png" alt="Payment Methods" class="m-r-5" style="height: 30px; opacity: 0.6;">
                                <img src="templates/images/icons/icon-pay-05.png" alt="Payment Methods" style="height: 30px; opacity: 0.6;">
                            </div>

                            <div class="txt-center">
                                <a href="{{ url('/cart') }}" class="stext-111 cl6 hov-cl1 trans-04">
                                    <i class="fa fa-arrow-left m-r-5"></i> Return to Shopping Cart
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('layouts.home.footer')
    @include('layouts.home.script')

    <style>
        .payment-option {
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .payment-option:hover {
            border-color: #e6e6e6;
        }
        .payment-option.active-payment {
            border-color: #717fe0;
            background-color: #f8f9fa;
        }
        .payment-icon {
            width: 50px;
            text-align: center;
        }
        .transition-04 {
            transition: all 0.4s;
        }
        .hov-bg9:hover {
            background-color: #f5f5f5;
        }
        .bg9 {
            background-color: #f8f9fa;
        }
        .sticky-top {
            position: sticky;
            top: 20px;
        }
        .size-150 {
            width: 150px;
        }
        .d-block {
            display: block;
        }
        #bank-details {
            display: none;
        }
        #cod-details {
            display: block;
        }
        input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .fa-lg {
            font-size: 1.33333333em;
            line-height: 0.75em;
            vertical-align: -15%;
        }
    </style>

    <script>
        function togglePayment(method) {
            var codDetails = document.getElementById('cod-details');
            var bankDetails = document.getElementById('bank-details');
            var codCard = document.getElementById('cod-card');
            var bankCard = document.getElementById('bank-card');
            var bankRef = document.getElementById('bank_reference');
            
            if (method === 'cod') {
                // Show COD details, hide Bank details
                codDetails.style.display = 'block';
                bankDetails.style.display = 'none';
                
                // Styling active card
                codCard.classList.add('active-payment');
                bankCard.classList.remove('active-payment');
                
                // Disable bank reference requirement
                if(bankRef) bankRef.required = false;
                
                // Check the radio
                document.getElementById('pay-cod').checked = true;
            } else {
                // Hide COD details, show Bank details
                codDetails.style.display = 'none';
                bankDetails.style.display = 'block';
                
                // Styling active card
                bankCard.classList.add('active-payment');
                codCard.classList.remove('active-payment');
                
                // Enable bank reference requirement
                if(bankRef) bankRef.required = true;
                
                // Check the radio
                document.getElementById('pay-bank').checked = true;
            }
        }

        function selectPayment(method) {
            togglePayment(method);
        }

        // Initialize: COD selected by default
        togglePayment('cod');

        // Form validation enhancement
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            var terms = document.getElementById('termsCheck');
            if (!terms.checked) {
                e.preventDefault();
                alert('Please agree to the terms and conditions to proceed.');
                return false;
            }
            
            var btn = document.getElementById('placeOrderBtn');
            btn.innerHTML = '<i class="fa fa-spinner fa-spin m-r-8"></i> Processing...';
            btn.disabled = true;
        });
    </script>

</body>
</html>