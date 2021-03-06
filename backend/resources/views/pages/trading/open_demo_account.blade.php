@extends('layout.layout')

@section('content')
    <!-- Open Account Start -->
    <section class="openaccount openaccount-section noheadtitle">
        <div class="container">

            <!-- Open Account Area -->
            <div class="openaccount-wrapper opendemoacc">
                <div class="openaccount-wrapper-row">

                    <!-- Welcome -->
                    <div class="openaccount-welcomecol">
                        <div class="welcomearea welcomeopendemoacc">
                            <div class="welcomeimg">
                                <img src="/assets/img/section/openaccount_welcome_opendemo_img.svg" alt="" width="324" height="378" />
                            </div>
                            <div class="sitecontent-desc contentsoft">
                                <h2>Open Demo Account</h2>
                                <p><strong>Welcome!</strong> Lorem ipsum dolor sit amet, consectetur</p>
                            </div>
                            <div class="welcomebtn">
                                <a class="btn btn-lg rounded-pill btn-secondary JsWelcomeBtn" href="javascript:void(0)" title="Open Demo Account" rel="bookmark" data-function="opendemoaccount">Open Demo Account</a>
                            </div>
                        </div>
                        <div class="welcomearea welcomeopenliveacc">
                            <div class="welcomeimg">
                                <img src="/assets/img/section/openaccount_welcome_openlive_img.svg" alt="" width="481" height="431" />
                            </div>
                            <div class="sitecontent-desc contentsoft">
                                <h2>Open Live Account</h2>
                                <p><strong>Welcome!</strong> Lorem ipsum dolor sit amet, consectetur</p>
                            </div>
                            <div class="welcomebtn">
                                <a class="btn btn-lg rounded-pill btn-secondary JsWelcomeBtn" href="{{ route('open_live_account') }}" title="Open Live Account" rel="bookmark" data-function="openliveaccount">Open Live Account</a>
                            </div>
                        </div>
                    </div>
                    <!-- Welcome -->

                    <!-- Form -->
                    <div class="openaccount-formcol">
                        <div class="formboxarea formopendemoacc">
                            <div class="sitecontent-desc">
                                <h2>Open Demo Account</h2>
                                <p><strong>Welcome!</strong> Lorem ipsum dolor sit amet, consectetur</p>
                            </div>
                            <div class="alertarea">
                                <div class="alert alert-sweet alert-warning alert-dismissible fade show d-none" role="alert">
                                    <div class="alert-flex">
                                        <div class="alert-icon">
                                            <div class="animation-alert-icons">
                                                <div class="alert-icons alert-icons-warning">
                                                    <div class="alert-icons-warning-body"></div>
                                                    <div class="alert-icons-warning-dot"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="alert-desc">
                                            <h6 class="alert-heading">Warning!</h6>
                                            <p id="alerttext">Aww yeah, you successfully read this important alert message.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="formarea">
                                <form class="siteformui" id="demoaccountform" autocomplete="off" novalidate onsubmit="return validateForm()">
                                    @csrf
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" class="form-control" placeholder="Enter First Name" name="firstname" />
                                    </div>
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" class="form-control" placeholder="Enter Last Name" name="lastname" />
                                    </div>
                                    <div class="form-group">
                                        <label>E-Mail</label>
                                        <input type="email" class="form-control" placeholder="Enter E-Mail"  name="email" />
                                    </div>
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" class="form-control" placeholder="Enter Phone"  name="phone" />
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" class="form-control" placeholder="Enter Password"  name="password" />
                                    </div>
                                    <div class="form-group">
                                        <label>Re-Password</label>
                                        <input type="password" class="form-control" placeholder="Enter Re-Password"  name="conf_pass" />
                                    </div>
                                    <div class="form-row formbottom">
                                        <div class="col-lg">
{{--                                            <div class="recaptcha-area">--}}
{{--                                                <div class="recaptcha-check">--}}
{{--                                                    <div class="g-recaptcha" data-theme="dark" data-sitekey="6LewOKAUAAAAAMDO2yohWeyDcjFAHfcuEqK2mIp4"></div>--}}
{{--                                                </div>--}}
{{--                                                <label>I'm Not Robot</label>--}}
{{--                                            </div>--}}
                                        </div>
                                        <div class="col-lg-auto">
                                            <button type="submit" class="btn btn-lg rounded-pill btn-info">Open Demo Account</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                    <!-- Form -->

                </div>
            </div>
            <!-- Open Account Area -->

        </div>
    </section>
    <!-- Open Account End -->

    <x-common-platforms/>
    <x-common-about-us/>
@endsection

@section('script')
    <script>
        const validateForm = () => {
            hideFormError();
            var firstName = document.getElementsByName('firstname')[0].value;
            var lastName = document.getElementsByName('lastname')[0].value;
            var email = document.getElementsByName('email')[0].value;
            var phone = document.getElementsByName('phone')[0].value;
            var password = document.getElementsByName('password')[0].value;
            var passwordConf = document.getElementsByName('conf_pass')[0].value;

            if(firstName.length < 2){
                showFormError("First name cannot be shorter than 2 characters");
                return false;
            }

            if(lastName.length < 2){
                showFormError("Last name cannot be shorter than 2 characters");
                return false;
            }


            if(!email.match(/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/)){
                showFormError("Please enter a valid email adress");
                return false;
            }

            if(!phone.replace(/\s/g, '').match(/^\d{11}$/g)){
                showFormError("Please enter a valid phone number");
                return false;
            }

            if(password.length < 8){
                showFormError("Password must be at least 8 characters");
                return false;
            }

            if(password !== passwordConf){
                showFormError("Passwords must match");
                return false;
            }

            let frm = $(`form#demoaccountform`);
            let originalValue = frm.find(':submit').html();
            frm.find(':submit').html('Sending...');

            $.ajax({
                type: 'POST',
                url: frm.attr('action'),
                data: frm.serialize(),
                success: function (data) {
                    if(!data.success){
                        showFormError(data.reason);
                        frm.find(':submit').html(originalValue);
                    }
                    frm.find(':submit').html('Account Created');
                },
                error: function (data) {
                    showFormError("Error sending form. Please try again later");
                    console.log('An error occurred.');
                    console.log(data);
                    frm.find(':submit').html(originalValue);
                },
            });

            return false;
        }

        const showFormError = (error) => {
            $("#alerttext").html(error);
            $(".alertarea .alert").removeClass('d-none');
        }

        const hideFormError = () => {
            $("#alerttext").html("");
            $(".alertarea .alert").addClass('d-none');
        }
    </script>
@endsection
