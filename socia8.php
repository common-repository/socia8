<?php
/*
 Plugin Name:   socia8
 Plugin URI:    https://plugins.svn.wordpress.org/socia8/
 Description:   Manage multiple social media accounts by posting to all at once, you can review post responses like comments, likes, retweets.
 Version:       0.1
 Author:        Team socia8
 Author URI:    https://socia8.com
 License:       GPL2
*/

register_activation_hook( __FILE__, 'socis8_plugin_activated' );

function socis8_plugin_activated() {
	if ( ! get_option( 'socia_my_plugin_activated' ) ) {
		add_option( 'socia_my_plugin_activated', "loggedOut" );
	}
}

function socia8_admin_menu() {
	add_menu_page( "socia8", "socia8", 'manage_options', 'socia8-settings', 'socia8_settings_screen', plugin_dir_url( __FILE__ ) . 'assets/socia88.ICO' );
	add_submenu_page( 'socia8-settings', __( 'Settings', "socia8" ), __( 'Settings', "socia8" ), 'manage_options', 'socia8-settings', 'socia8_settings_screen' );
}

add_action( 'admin_menu', 'socia8_admin_menu' );


function socia8_settings_screen() {
	$url = $_SERVER['QUERY_STRING'];
	parse_str( $url, $arr );
	$h = '?';
	foreach ( $arr as $key => $value ) {
		if ( $key != 'success' ) {
			$h .= $key . '=' . $value . '&';
		}
	}
	$protocol    = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off'
	                 || $_SERVER['SERVER_PORT'] == 443 ) ? 'https://' : 'http://';
	$redirectUrl = base64_encode( $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $h );

	wp_enqueue_style( "Socia8_bootstrap_css", plugin_dir_url( __FILE__ ) . 'assets/css/bootstrap.css' );
	wp_enqueue_script( 'Socia8_bootstrap_js', plugin_dir_url( __FILE__ ) . 'assets/js/bootstrap.js', array( 'jquery' ) );
	if ( isset( $_POST['loginSocia8'] ) ) {
		$email    = sanitize_email( $_POST['username'] );
		$password = sanitize_text_field( $_POST['password'] );
		$url      = "https://socia8.com/wpsocia8/";
		if ( isset( $_POST['rememberMe1'] ) && $_POST['rememberMe1'] == 'on' ) {
			$rememberMe = 1;
		} else {
			$rememberMe = 0;
		}
		$data         = [
			'email'       => $email,
			'password'    => $password,
			'rememberMe'  => $rememberMe,
			'redirectUrl' => $redirectUrl
		];
		$args         = array(
			'body'   => $data,
			'method' => 'POST',
		);
		$login_result = wp_remote_post( $url, $args );
		$login        = $login_result['body'];
		if ( $login != "NotValid" ) {
			if ( get_option( 'socia_my_plugin_activated' ) || get_option( 'socia_my_plugin_activated' ) == "" ) {
				update_option( 'socia_my_plugin_activated', $login );
			} else {
				add_option( 'socia_my_plugin_activated', $login );
			}
		} else {
			?>
            <div class="notice notice-error is-dismissible">
                <p>
					<?php echo sprintf( __( '%s: Enter valid Email or Password.', "socia8" ), 'socia8' ); ?>
                </p>
            </div>
			<?php
		}
	}
	if ( isset( $_POST['logOutSocia8'] ) ) {
		$user_id       = sanitize_html_class( $_POST['user_id'] );
		$url           = "https://socia8.com/wpsocia8/logout";
		$data          = [ 'user_id' => $user_id, 'redirectUrl' => $redirectUrl ];
		$args          = array(
			'body'   => $data,
			'method' => 'POST',
		);
		$logout_result = wp_remote_post( $url, $args );

		if ( $logout_result['body'] == '1' ) {
			if ( get_option( 'socia_my_plugin_activated' ) ) {
				update_option( 'socia_my_plugin_activated', "loggedOut" );
			}
		}
	}
	if ( get_option( 'socia_my_plugin_activated' ) ) {
		$userToken = get_option( 'socia_my_plugin_activated' );
		if ( $userToken != 'loggedOut' ) {
			$url    = "https://socia8.com/wpsocia8/wpprofiles";
			$data   = [ 'user_id' => $userToken, 'redirectUrl' => $redirectUrl ];
			$args   = array(
				'body'   => $data,
				'method' => 'POST',
			);
			$result = wp_remote_post( $url, $args );
			if ( $result['body'] != "loggedOut" ) {
				$result = json_decode( $result['body'], true );
				update_option( 'socia_my_plugin_activated', $result['updatedToken'] );
				?>
                <style>
                    .profileImg {
                        height: 50px;
                        width: 50px;
                    }

                    .socialImg {
                        height: 50px;
                        /*width:35%;*/
                        display: inline-block;
                    }

                    .tdClass {
                        vertical-align: middle !important;
                    }

                    .ProfileDropDown {
                        height: 35px !important;
                        width: 70% !important;
                    }

                    .green-haze {
                        color: #FFFFFF;
                        background-color: #44b6ae;
                    }

                    .row-programs > .col-program {
                        position: relative;
                        padding: 0px;
                        padding-top: 5px;
                        min-height: 100px;
                        zoom: 1;
                        border-radius: 5px !important;
                        transition: zoom 1.5s ease;
                        margin-top: 10px;
                    }

                    .row-programs > .col-program:hover {
                        margin-top: -10px;
                        margin-bottom: 10px;
                        transition: background-color .4s ease-in-out;
                        z-index: 1;
                        border: 2px solid #26a69a;
                        zoom: 1.1;
                    }
                </style>
				<?php
				if ( isset( $_POST['fbGroupSaveBtn1'] ) ) {
					$groupIcon = sanitize_text_field( $_POST['fbGroupIcon'] );
					$groupId   = sanitize_text_field( $_POST['fbGroupId'] );
					$groupName = sanitize_text_field( $_POST['fbGroupName'] );
					$url       = "https://socia8.com/wpsocia8/savefbgroup";
					$user_id   = $userToken;
					$data      = [
						'user_id'     => $user_id,
						'groupId'     => $groupId,
						'logoUrl'     => $groupIcon,
						'groupName'   => $groupName,
						'redirectUrl' => $redirectUrl
					];
					$args      = array(
						'body'   => $data,
						'method' => 'POST',
					);
					$response  = wp_remote_post( $url, $args );
					$response  = $response['body'];
					?>
                        <script>
                            window.location.href = "admin.php?page=socia8-settings&success=fbgConnect";
                        </script>
						<?php
				}
				if ( isset( $_POST['fbPageSaveBtn1'] ) ) {
					$pageIcon = sanitize_text_field( $_POST['fbPageIcon'] );
					$pageId   = sanitize_text_field( $_POST['fbPageId'] );
					$pageName = sanitize_text_field( $_POST['fbPageName'] );
					$url      = "https://socia8.com/wpsocia8/savefbpage";
					$user_id  = $userToken;
					$data     = [
						'user_id'     => $user_id,
						'pageId'      => $pageId,
						'logoUrl'     => $pageIcon,
						'pageName'    => $pageName,
						'redirectUrl' => $redirectUrl
					];
					$args     = array(
						'body'   => $data,
						'method' => 'POST',
					);
					$response = wp_remote_post( $url, $args );
					$response = $response['body'];

						?>
                        <script>
                            window.location.href = "admin.php?page=socia8-settings&success=fbpConnect";
                        </script>
						<?php

				}
				if ( isset( $_POST['glPageSaveBtn1'] ) ) {
					$pageIcon = sanitize_text_field( $_POST['glPageIcon'] );
					$pageId   = sanitize_text_field( $_POST['glPageId'] );
					$pageName = sanitize_text_field( $_POST['glPageName'] );
					$url      = "https://socia8.com/wpsocia8/saveglpage";
					$user_id  = $userToken;
					$data     = [
						'user_id'     => $user_id,
						'pageId'      => $pageId,
						'logoUrl'     => $pageIcon,
						'pageName'    => $pageName,
						'redirectUrl' => $redirectUrl
					];
					$args     = array(
						'body'   => $data,
						'method' => 'POST',
					);
					$response = wp_remote_post( $url, $args );
					$response = $response['body'];
                    ?>
                    <script>
                        window.location.href = "admin.php?page=socia8-settings&success=glpConnect";
                    </script>
                    <?php
				}
				if ( isset( $_POST['lnPageSaveBtn1'] ) ) {
					$pageIcon = sanitize_text_field( $_POST['lnPageIcon'] );
					$pageId   = sanitize_text_field( $_POST['lnPageId'] );
					$pageName = sanitize_text_field( $_POST['lnPageName'] );
					$url      = "https://socia8.com/wpsocia8/savelnpage";
					$user_id  = $userToken;
					$data     = [
						'user_id'     => $user_id,
						'pageId'      => $pageId,
						'logoUrl'     => $pageIcon,
						'pageName'    => $pageName,
						'redirectUrl' => $redirectUrl
					];
					$args     = array(
						'body'   => $data,
						'method' => 'POST',
					);
					$response = wp_remote_post( $url, $args );
					$response = $response['body'];
                    ?>
                    <script>
                        window.location.href = "admin.php?page=socia8-settings&success=lnpConnect";
                    </script>
                    <?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'fbConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Facebook Connected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'fbDisConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Facebook DisConnected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'fbpConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Facebook Page Connected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['error'] == 'fbpConnect' ) {
					?>
                    <div class="notice notice-error is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Facebook Page Not Connected.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'fbPageConnect' ) {
					$url      = "https://socia8.com/wpsocia8/getfbpages";
					$user_id  = $userToken;
					$data     = [ 'user_id' => $user_id, 'redirectUrl' => $redirectUrl ];
					$args     = array(
						'body'   => $data,
						'method' => 'POST',
					);
					$fbpages1 = wp_remote_post( $url, $args );
					$fbpages1 = $fbpages1['body'];
					$fbpages1 = json_decode( $fbpages1, true );
					if ( isset( $fbpages1 ) ) {
						?>
                        <div class="modal fade" id="fbPageListModal1" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button class="close" data-dismiss="modal" id="closeFbPage1"
                                                style="display: none" onclick="closeModalBtn('fbPage')">x
                                        </button>
                                        <center>
                                            <h4 class="modal-title" id="fbPageModalTitle1">Select your
                                                facebook page, you want to manage here..</h4>
                                        </center>
                                    </div>
                                    <div class="modal-body">
                                        <div id="fbPageList1">
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form method="post">
                            <input type="hidden" name="fbPageId" id="fbPageId">
                            <input type="hidden" name="fbPageIcon" id="fbPageIcon">
                            <input type="hidden" name="fbPageName" id="fbPageName">
                            <button type="submit" name="fbPageSaveBtn1" id="fbPageSaveBtn1" style="display: none;">
                                save page
                            </button>
                        </form>
						<?php
					}
					?>
                    <script>
                        function saveFbPage1(pageId) {
                            var logoUrl = jQuery('#logoUrl-' + pageId).attr('src');
                            var pageName = jQuery('#pageName-' + pageId).html();
                            jQuery('#fbPageId').val(pageId);
                            jQuery('#fbPageIcon').val(logoUrl);
                            jQuery('#fbPageName').val(pageName);
                            jQuery('#fbPageSaveBtn1').click();
                        }
                        var pages =<?php echo json_encode( $fbpages1 );?>;
                        var html = '<div class="row-programs row col-md-12">';
                        if (pages == '1') {
                            jQuery('#fbPageModalTitle1').html('Whoops !');
                            jQuery('#closeFbPage1').show();
                            html += '<center><h1>No Facebook Pages Found.</h1></center>';
                        }
                        else {
                            jQuery('#closeFbPage1').hide();
                            pages.forEach(function (data) {
                                html += '<div class="col-program col-md-3 clearfix" align="center" onclick="saveFbPage1(' + data.id + ')">' +
                                    '<img class="img-rounded" id="logoUrl-' + data.id + '" src="' + data.picture.data.url + '"><br>' +
                                    '<span id="pageName-' + data.id + '">' + data.name + '</span>' +
                                    '</div>';
                            });
                        }
                        html += '</div>';
                        jQuery(document).ready(function ($) {
                            $('#fbPageList1').html(html);
                            $('#fbPageListModal1').modal({
                                backdrop: 'static',
                                keyboard: false,
                                show: true
                            });
                        });
                    </script>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Facebook Page Connected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'glPageConnect' ) {
					$url      = "https://socia8.com/wpsocia8/getglpages";
					$user_id  = $userToken;
					$data     = [ 'user_id' => $user_id, 'redirectUrl' => $redirectUrl ];
					$args     = array(
						'body'   => $data,
						'method' => 'POST',
					);
					$glpages1 = wp_remote_post( $url, $args );
					$glpages1 = $glpages1['body'];
					$glpages1 = json_decode( $glpages1, true );
					if ( isset( $glpages1 ) ) {
						?>
                        <div class="modal fade" id="glPageListModal1" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button class="close" data-dismiss="modal" id="closeglPage1"
                                                style="display: none"
                                                onclick="closeModalBtn('glPage')">x
                                        </button>
                                        <center>
                                            <h4 class="modal-title" id="glPageModalTitle1">Select your
                                                google+ page, you want to manage here..</h4>
                                        </center>
                                    </div>
                                    <div class="modal-body">
                                        <div id="glPageList1">
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form method="post">
                            <input type="hidden" name="glPageId" id="glPageId">
                            <input type="hidden" name="glPageIcon" id="glPageIcon">
                            <input type="hidden" name="glPageName" id="glPageName">
                            <button type="submit" name="glPageSaveBtn1" id="glPageSaveBtn1" style="display: none;">
                                save page
                            </button>
                        </form>
						<?php
					}
					?>
                    <script>
                        function saveGlPage1(pageId) {
                            var logoUrl = jQuery('#logoUrl1-' + pageId).attr('src');
                            console.log(logoUrl);
                            var pageName = jQuery('#pageName-' + pageId).html();
                            jQuery('#glPageId').val(pageId);
                            jQuery('#glPageIcon').val(logoUrl);
                            jQuery('#glPageName').val(pageName);
                            jQuery('#glPageSaveBtn1').click();
                        }
                        var pages =<?php echo json_encode( $glpages1 );?>;
                        console.log(pages);
                        var html = '<div class="row-programs row col-md-12">';

                        if (pages == '1') {
                            jQuery('#glPageModalTitle1').html('Whoops !');
                            jQuery('#closeglPage1').show();
                            html += '<center><h1>No Google+ Pages Found.</h1></center>';
                        }
                        else {
                            jQuery('#closeglPage1').hide();
                            pages.forEach(function (data) {
                                html += '<div class="col-program col-md-3 clearfix" align="center" onclick="saveGlPage1(\'' + data.id + '\')">' +
                                    '<img class="img-rounded" id="logoUrl1-' + data.id + '" src="' + data.image + '"><br>' +
                                    '<span id="pageName-' + data.id + '">' + data.name + '</span>' +
                                    '</div>';
                            });
                        }
                        html += '</div>';
                        jQuery(document).ready(function ($) {
                            $('#glPageList1').html(html);
                            $('#glPageListModal1').modal({
                                backdrop: 'static',
                                keyboard: false,
                                show: true
                            });
                        });
                    </script>

                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Google+ Page Connected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'lnPageConnect' ) {
					$url      = "https://socia8.com/wpsocia8/getlnpages";
					$user_id  = $userToken;
					$data     = [ 'user_id' => $user_id, 'redirectUrl' => $redirectUrl ];
					$args     = array(
						'body'   => $data,
						'method' => 'POST',
					);
					$lnpages1 = wp_remote_post( $url, $args );
					$lnpages1 = $lnpages1['body'];
					$lnpages1 = json_decode( $lnpages1, true );

					if ( isset( $lnpages1 ) ) {
						?>
                        <div class="modal fade" id="lnPageListModal1" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button class="close" data-dismiss="modal" id="closeLnPage1"
                                                style="display: none"
                                                onclick="closeModalBtn('lnPage')">x
                                        </button>
                                        <center>
                                            <h4 class="modal-title" id="lnPageModalTitle1">Select your
                                                linkedin page, you want to manage here..</h4>
                                        </center>
                                    </div>
                                    <div class="modal-body">
                                        <div id="lnPageList1">
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form method="post">
                            <input type="hidden" name="lnPageId" id="lnPageId">
                            <input type="hidden" name="lnPageIcon" id="lnPageIcon">
                            <input type="hidden" name="lnPageName" id="lnPageName">
                            <button type="submit" name="lnPageSaveBtn1" id="lnPageSaveBtn1" style="display: none;">
                                save page
                            </button>
                        </form>
						<?php
					}
					?>
                    <script>
                        function saveLnPage1(pageId) {
                            var logoUrl = jQuery('#logoUrl-' + pageId).attr('src');
                            var pageName = jQuery('#pageName-' + pageId).html();
                            jQuery('#lnPageId').val(pageId);
                            jQuery('#lnPageIcon').val(logoUrl);
                            jQuery('#lnPageName').val(pageName);
                            jQuery('#lnPageSaveBtn1').click();
                        }
                        var pages =<?php echo json_encode( $lnpages1 );?>;
                        console.log(pages);
                        var html = '<div class="row-programs row col-md-12">';
                        if (pages == '1') {
                            jQuery('#lnPageModalTitle1').html('Whoops !');
                            jQuery('#closeLnPage1').show();
                            html += '<center><h1>No Linkedin Pages Found.</h1></center>';
                        }
                        else {
                            jQuery('#closeLnPage1').hide();
                            pages.forEach(function (data) {
                                html += '<div class="col-program col-md-3 clearfix" align="center" onclick="saveLnPage1(' + data.id + ')">' +
                                    '<img class="img-rounded" id="logoUrl-' + data.id + '" src="' + data.logoUrl + '"><br>' +
                                    '<span id="pageName-' + data.id + '">' + data.name + '</span>' +
                                    '</div>';
                            });
                        }
                        html += '</div>';
                        jQuery('#lnPageList1').html(html);
                        jQuery(document).ready(function ($) {
                            $('#lnPageListModal1').modal({
                                backdrop: 'static',
                                keyboard: false,
                                show: true
                            });
                        });
                    </script>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Linkedin Page Connected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'fbgConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Facebook Group Connected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['error'] == 'fbgConnect' ) {
					?>
                    <div class="notice notice-error is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Facebook Group Not Connected.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'fbpDisConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Facebook Page DisConnected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'fbGrpConnect' ) {
					$url     = "https://socia8.com/wpsocia8/getfbgroups";
					$user_id = $userToken;
					$data    = [ 'user_id' => $user_id, 'redirectUrl' => $redirectUrl ];
					$args    = array(
						'body'   => $data,
						'method' => 'POST',
					);
					$fbgrps1 = wp_remote_post( $url, $args );
					$fbgrps1 = $fbgrps1['body'];
					$fbgrps1 = json_decode( $fbgrps1, true );
					if ( isset( $fbgrps1 ) ) {
						?>
                        <div class="modal fade" id="fbGroupListModal1" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button class="close" data-dismiss="modal" id="closeFbGrp1"
                                                style="display: none"
                                                onclick="closeModalBtn('fbGrp')">x
                                        </button>
                                        <center>
                                            <h4 class="modal-title" id="fbGroupModalTitle1">Select your
                                                facebook group, you want to manage here..</h4>
                                        </center>
                                    </div>
                                    <div class="modal-body">
                                        <div id="fbGroupList1">
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form method="post">
                            <input type="hidden" name="fbGroupId" id="fbGroupId">
                            <input type="hidden" name="fbGroupIcon" id="fbGroupIcon">
                            <input type="hidden" name="fbGroupName" id="fbGroupName">
                            <button type="submit" name="fbGroupSaveBtn1" id="fbGroupSaveBtn1" style="display: none;">
                                save group
                            </button>
                        </form>
						<?php
					}
					?>
                    <script>
                        function saveFbGroup1(groupId) {
                            var logoUrl = jQuery('#logoUrl-' + groupId).attr('src');
                            var groupName = jQuery('#groupName-' + groupId).html();
                            jQuery('#fbGroupId').val(groupId);
                            jQuery('#fbGroupIcon').val(logoUrl);
                            jQuery('#fbGroupName').val(groupName);

                            jQuery('#fbGroupSaveBtn1').click();
                        }
                        var group =<?php echo json_encode( $fbgrps1 );?>;
                        console.log(group);
                        var html = '<div class="row-programs row col-md-12">';
                        if (group == '1') {
                            jQuery('#fbGroupModalTitle1').html('Whoops !');
                            jQuery('#closeFbGrp1').show();
                            html += '<center><h1>No Facebook Groups Found.</h1></center>';
                        }
                        else {
                            jQuery('#closeFbGrp1').hide();
                            group.forEach(function (data) {
                                html += '<div class="col-program col-md-3 clearfix" align="center" onclick="saveFbGroup1(' + data.id + ')">' +
                                    '<img class="img-rounded" id="logoUrl-' + data.id + '" src="' + data.icon + '"><br>' +
                                    '<span id="groupName-' + data.id + '">' + data.name + '</span>' +
                                    '</div>';
                            });
                        }
                        html += '</div>';
                        jQuery('#fbGroupList1').html(html);
                        jQuery(document).ready(function ($) {
                            $('#fbGroupListModal1').modal({
                                backdrop: 'static',
                                keyboard: false,
                                show: true
                            });
                        });
                    </script>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Facebook Group Connected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'fbgDisConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Facebook Group DisConnected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'twConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Twitter Connected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'twDisConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Twitter DisConnected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'glConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Google+ Connected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'glDisConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Google+ DisConnected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'glpConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Google+ Page Connected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['error'] == 'glpConnect' ) {
					?>
                    <div class="notice notice-error is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Google+ Page Not Connected.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'glpDisConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Google+ Page DisConnected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'lnConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Linkedin Connected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'lnDisConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Linkedin DisConnected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'lnpConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Linkedin Page Connected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['error'] == 'lnpConnect' ) {
					?>
                    <div class="notice notice-error is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Linkedin Page Not Connected.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'lnpDisConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Linkedin Page DisConnected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'piConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Pinterest Connected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'pibConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Pinterest Board Connected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				if ( isset( $_GET['success'] ) && $_GET['success'] == 'piDisConnect' ) {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p>
							<?php echo sprintf( __( '%s: Pinterest DisConnected Successfully.', "socia8" ), 'socia8' ); ?>
                        </p>
                    </div>
					<?php
				}
				?>
                <div class="col-md-12">
                    <div class="col-md-12" style="padding-top: 2%;padding-bottom: 2%">
                        <form method="post" style="display: inline-block;float: right">
                            <input type="hidden" name="user_id" value="<?php echo $userToken; ?>">
                            <button type="submit" name="logOutSocia8" class="btn btn-warning pull-right">Log out From
                                socia8
                            </button>
                        </form>
                    </div>
                    <div class="col-md-12">
                        <table class="table table-striped table-bordered table-hover table-responsive">
                            <thead>
                            <tr>
                                <th colspan="4">Social Connectivity</th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							if ( isset( $result[1] ) ) {
								?>
                                <tr>
                                    <td class="tdClass" align="center" width="25%">
                                        <img src="<?php echo plugin_dir_url( __FILE__ ); ?>assets/icons/fbprofile.png"
                                             class="socialImg"/>
                                    </td>
                                    <td class="tdClass" align="center" width="25%"></td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[1]['status'] ) && $result[1]['status'] == 'Connected' ) {
											?>
                                            <img src="<?php echo $result[1]['profileInfo']['picture']; ?>"
                                                 class="img-circle profileImg">
                                            <br>
											<?php
											echo $result[1]['profileInfo']['name'];
										}
										?>
                                    </td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[1]['status'] ) && $result[1]['status'] == 'Connected' ) {
											?>
                                            <a href="https://www.socia8.com/wpsocia8/fbdisconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-danger">Disconnect</button>
                                            </a>
											<?php
										} else {
											?>
                                            <a href="https://www.socia8.com/wpsocia8/fbconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-info">Connect</button>
                                            </a>
											<?php
										}
										?>
                                    </td>
                                </tr>
								<?php
							}
							if ( isset( $result[2] ) ) {
								?>
                                <tr>
                                    <td class="tdClass" align="center" width="25%">
                                        <img src="<?php echo plugin_dir_url( __FILE__ ); ?>assets/icons/twitterprofile.png"
                                             class="socialImg"/>
                                    </td>
                                    <td class="tdClass" align="center" width="25%"></td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[2]['status'] ) && $result[2]['status'] == 'Connected' ) {
											?>
                                            <img src="<?php echo $result[2]['profileInfo']['picture']; ?>"
                                                 class="img-circle profileImg">
                                            <br>
											<?php
											echo $result[2]['profileInfo']['screen_name'];
										}
										?>
                                    </td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[2]['status'] ) && $result[2]['status'] == 'Connected' ) {
											?>
                                            <a href="https://www.socia8.com/wpsocia8/twdisconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-danger">Disconnect</button>
                                            </a>
											<?php
										} else {
											?>
                                            <a href="https://www.socia8.com/wptwitter/callback?authclient=twitter&sociaRedirect=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-info">Connect</button>
                                            </a>
											<?php
										}
										?>
                                    </td>
                                </tr>
								<?php
							}
							if ( isset( $result[3] ) ) {
								?>
                                <tr>
                                    <td class="tdClass" align="center" width="25%">
                                        <img src="<?php echo plugin_dir_url( __FILE__ ); ?>assets/icons/g+profile.png"
                                             class="socialImg"/>
                                    </td>
                                    <td class="tdClass" align="center" width="25%"></td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[3]['status'] ) && $result[3]['status'] == 'Connected' ) {
											?>
                                            <img src="<?php echo $result[3]['profileInfo']['picture']; ?>"
                                                 class="img-circle profileImg">
                                            <br>
											<?php
											echo $result[3]['profileInfo']['name'];
										}
										?>
                                    </td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[3]['status'] ) && $result[3]['status'] == 'Connected' ) {
											?>
                                            <a href="https://www.socia8.com/wpsocia8/gldisconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-danger">Disconnect</button>
                                            </a>
											<?php
										} else {
											?>
                                            <a href="https://www.socia8.com/wpsocia8/glconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-info">Connect</button>
                                            </a>
											<?php
										}
										?>
                                    </td>
                                </tr>
								<?php
							}
							if ( isset( $result[4] ) ) {
								?>
                                <tr>
                                    <td class="tdClass" align="center" width="25%">
                                        <img src="<?php echo plugin_dir_url( __FILE__ ); ?>assets/icons/linkdienprofile.png"
                                             class="socialImg"/>
                                    </td>
                                    <td class="tdClass" align="center" width="25%"></td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[4]['status'] ) && $result[4]['status'] == 'Connected' ) {
											?>
                                            <img src="<?php echo $result[4]['profileInfo']['picture']; ?>"
                                                 class="img-circle profileImg">
                                            <br>
											<?php
											echo $result[4]['profileInfo']['name'];
										}
										?>
                                    </td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[4]['status'] ) && $result[4]['status'] == 'Connected' ) {
											?>
                                            <a href="https://www.socia8.com/wpsocia8/lndisconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-danger">Disconnect</button>
                                            </a>
											<?php
										}
										else {
											?>
                                            <a href="https://socia8.com/linkdein/callback?authclient=linkdein&sociaRedirect=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-info">Connect</button>
                                            </a>
											<?php
										}
										?>
                                    </td>
                                </tr>
								<?php
							}
							if ( isset( $result[6] ) ) {
								?>
                                <tr>
                                    <td class="tdClass" align="center" width="25%">
                                        <img src="<?php echo plugin_dir_url( __FILE__ ); ?>assets/icons/pinterestprofile.png"
                                             class="socialImg"/>
                                    </td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[6]['status'] ) && $result[6]['status'] == 'Connected' ) {
											$url     = "https://socia8.com/wpsocia8/getpinterestboards";
											$user_id = $userToken;
											$data    = [ 'user_id' => $user_id, 'redirectUrl' => $redirectUrl ];
											$args    = array(
												'body'   => $data,
												'method' => 'POST',
											);
											$result1 = wp_remote_post( $url, $args );
											$result1 = $result1['body'];
    										$result1 = json_decode( $result1, true );
										}
										if ( isset( $result1 ) && count( $result1 ) > 0 ) {
										    if(isset($_POST['boardSaveBtn'])){
                                                $boardName = sanitize_text_field( $_POST['boardName'] );
                                                $url       = "https://socia8.com/wpsocia8/setpinterestboard";
                                                $user_id   = $userToken;
                                                $data      = [
                                                    'user_id'     => $user_id,
                                                    'boardName'     => $boardName
                                                ];
                                                $args      = array(
                                                    'body'   => $data,
                                                    'method' => 'POST',
                                                );
                                                $response  = wp_remote_post( $url, $args );
                                                $response  = $response['body'];
                                                ?>
                                                    <script>
                                                        window.location.href = "admin.php?page=socia8-settings&success=pibConnect";
                                                    </script>
                                                <?php
                                            }
											?>
                                            <select class="form-control ProfileDropDown" onchange="changeBoard(this.value)" name="piBoard">
												<?php
												foreach($result1['page'] as $r) {
												    if($r== $result1['board']) {
													    ?>
                                                        <option selected value="<?php echo $r; ?>"><?php echo $r; ?></option>
													    <?php
												    }
												    else{
													    ?>
                                                        <option value="<?php echo $r; ?>"><?php echo $r; ?></option>
													    <?php
                                                    }
												}
												?>
                                            </select>
                                            <form method="post">
                                                <input type="hidden" name="boardName" id="boardName">
                                                <button type="submit" name="boardSaveBtn" id="boardSaveBtn" style="display: none;">save board
                                                </button>
                                            </form>
                                            <script>
                                                function changeBoard(boardName) {
                                                    jQuery('#boardName').val(boardName);
                                                    jQuery('#boardSaveBtn').click();
                                                }
                                            </script>
											<?php
										}
										?>
                                    </td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[6]['status'] ) && $result[6]['status'] == 'Connected' ) {
											?>
                                            <img src="<?php echo $result[6]['profileInfo']['picture']; ?>"
                                                 class="img-circle profileImg">
                                            <br>
											<?php
											echo $result[6]['profileInfo']['name'];
										}
										?>
                                    </td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[6]['status'] ) && $result[6]['status'] == 'Connected' ) {
											?>
                                            <a href="https://www.socia8.com/wpsocia8/pidisconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-danger">Disconnect</button>
                                            </a>
											<?php
										} else {
											?>
                                            <a href="https://www.socia8.com/wpsocia8/piconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-info">Connect</button>
                                            </a>
											<?php
										}
										?>
                                    </td>
                                </tr>
								<?php
							}
							if ( isset( $result[8] ) ) {
								?>
                                <tr>
                                    <td class="tdClass" align="center" width="25%">
                                        <img src="<?php echo plugin_dir_url( __FILE__ ); ?>assets/icons/fb-group.png"
                                             class="socialImg"/>
                                    </td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[8]['status'] ) && $result[8]['status'] == 'Connected' ) {
											$url     = "https://socia8.com/wpsocia8/getfbgroups";
											$user_id = $userToken;
											$data    = [ 'user_id' => $user_id, 'redirectUrl' => $redirectUrl ];
											$args    = array(
												'body'   => $data,
												'method' => 'POST',
											);
											$fbgrps  = wp_remote_post( $url, $args );
											$fbgrps  = $fbgrps['body'];

											$fbgrps = json_decode( $fbgrps, true );
											?>
                                            <button type="submit" id="fbgBtn" class="btn green-haze"
                                                    onclick="selectFbGroup('<?php echo $userToken; ?>')"
                                                    name="fbGroupBtn">
                                                <i class="fa fa-users" aria-hidden="true"></i>
                                                Select Group
                                            </button>
										<?php
										if ( isset( $_POST['fbGroupSaveBtn'] ) ){
										$groupIcon = sanitize_text_field( $_POST['fbGroupIcon'] );
										$groupId   = sanitize_text_field( $_POST['fbGroupId'] );
										$groupName = sanitize_text_field( $_POST['fbGroupName'] );
										$url       = "https://socia8.com/wpsocia8/savefbgroup";
										$user_id   = $userToken;
										$data      = [
											'user_id'     => $user_id,
											'groupId'     => $groupId,
											'logoUrl'     => $groupIcon,
											'groupName'   => $groupName,
											'redirectUrl' => $redirectUrl
										];
										$args      = array(
											'body'   => $data,
											'method' => 'POST',
										);
										$response  = wp_remote_post( $url, $args );
										$response  = $response['body'];
                                        ?>
                                            <script>
                                                window.location.href = "admin.php?page=socia8-settings&success=fbgConnect";
                                            </script>
										<?php

										}

										if ( isset( $fbgrps ) ) {
										?>
                                            <div class="modal fade" id="fbGroupListModal" role="dialog">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button class="close" data-dismiss="modal" id="closeFbGrp"
                                                                    style="display: none"
                                                                    onclick="closeModalBtn('fbGrp')">x
                                                            </button>
                                                            <center>
                                                                <h4 class="modal-title" id="fbGroupModalTitle"
                                                                    style="display: none;">
                                                                    Select your facebook group, you want to manage
                                                                    here..</h4>
                                                            </center>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div id="fbGroupList">
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <form method="post">
                                                <input type="hidden" name="fbGroupId" id="fbGroupId">
                                                <input type="hidden" name="fbGroupIcon" id="fbGroupIcon">
                                                <input type="hidden" name="fbGroupName" id="fbGroupName">
                                                <button type="submit" name="fbGroupSaveBtn" id="fbGroupSaveBtn"
                                                        style="display: none;">save group
                                                </button>
                                            </form>
										<?php
										}
										?>
                                            <script>
                                                function saveFbGroup(groupId) {
                                                    var logoUrl = jQuery('#logoUrl-' + groupId).attr('src');
                                                    var groupName = jQuery('#groupName-' + groupId).html();
                                                    jQuery('#fbGroupId').val(groupId);
                                                    jQuery('#fbGroupIcon').val(logoUrl);
                                                    jQuery('#fbGroupName').val(groupName);
                                                    jQuery('#fbGroupSaveBtn').click();
                                                }
                                                function selectFbGroup(user_id) {
                                                    jQuery(this).attr('disabled', 'true');

                                                    var group =<?php echo json_encode( $fbgrps );?>;
                                                    console.log(group);
                                                    var html = '<div class="row-programs row col-md-12">';

                                                    if (group == '1') {
                                                        jQuery('#fbGroupModalTitle').html('Whoops !');
                                                        jQuery('#closeFbGrp').show();
                                                        html += '<center><h1>No Facebook Groups Found.</h1></center>';
                                                    }
                                                    else {
                                                        jQuery('#closeFbGrp').hide();
                                                        group.forEach(function (data) {
                                                            html += '<div class="col-program col-md-3 clearfix" align="center" onclick="saveFbGroup(' + data.id + ',\'' + user_id + '\')">' +
                                                                '<img class="img-rounded" id="logoUrl-' + data.id + '" src="' + data.icon + '"><br>' +
                                                                '<span id="groupName-' + data.id + '">' + data.name + '</span>' +
                                                                '</div>';
                                                        });
                                                    }
                                                    html += '</div>';
                                                    jQuery('#fbGroupList').html(html);
                                                    jQuery('#fbGroupModalTitle').show();
                                                    jQuery(document).ready(function ($) {
                                                        $('#fbGroupListModal').modal({
                                                            backdrop: 'static',
                                                            keyboard: false,
                                                            show: true
                                                        });
                                                    });
                                                }
                                            </script>
											<?php
										}
										?>
                                    </td>
                                    <td class="tdClass" align="center" width="25%" align="center">
										<?php
										if ( isset( $result[8]['status'] ) && $result[8]['status'] == 'Connected' ) {
											?>
                                            <img src="<?php echo $result[8]['profileInfo']['picture']; ?>"
                                                 class="img-circle profileImg">
                                            <br>
											<?php
											echo $result[8]['profileInfo']['name'];
										}
										?>
                                    </td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[8]['status'] ) && $result[8]['status'] == 'Connected' ) {
											?>
                                            <a href="https://www.socia8.com/wpsocia8/fbgdisconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-danger">Disconnect</button>
                                            </a>
											<?php
										}
										else {
											?>
                                            <a href="https://www.socia8.com/wpsocia8/fbgconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-info">Connect</button>
                                            </a>
											<?php
										}
										?>
                                    </td>
                                </tr>
								<?php
							}
							if ( isset( $result[9] ) ) {
							?>
                            <tr>
                                <td class="tdClass" align="center" width="25%">
                                    <img src="<?php echo plugin_dir_url( __FILE__ ); ?>assets/icons/fb-page.png"
                                         class="socialImg"/>
                                </td>
                                <td class="tdClass" align="center" width="25%">
									<?php
									if ( isset( $result[9]['status'] ) && $result[9]['status'] == 'Connected' ) {
									$url     = "https://socia8.com/wpsocia8/getfbpages";
									$user_id = $userToken;
									$data    = [ 'user_id' => $user_id, 'redirectUrl' => $redirectUrl ];
									$args    = array(
										'body'   => $data,
										'method' => 'POST',
									);
									$fbpages = wp_remote_post( $url, $args );
									$fbpages = $fbpages['body'];
									$fbpages = json_decode( $fbpages, true );
									?>
                                    <button type="submit" id="fbpBtn" class="btn green-haze"
                                            onclick="selectFbPage('<?php echo $userToken; ?>')"
                                            name="fbPageBtn">
                                        <i class="fa fa-flag" aria-hidden="true"></i>
                                        Select Page
                                    </button>
									<?php
									if ( isset( $_POST['fbPageSaveBtn'] ) ) {
                                        $pageIcon = sanitize_text_field( $_POST['fbPageIcon'] );
                                        $pageId   = sanitize_text_field( $_POST['fbPageId'] );
                                        $pageName = sanitize_text_field( $_POST['fbPageName'] );
                                        $url      = "https://socia8.com/wpsocia8/savefbpage";
                                        $user_id  = $userToken;
                                        $data     = [
                                            'user_id'     => $user_id,
                                            'pageId'      => $pageId,
                                            'logoUrl'     => $pageIcon,
                                            'pageName'    => $pageName,
                                            'redirectUrl' => $redirectUrl
                                        ];
                                        $args     = array(
                                            'body'   => $data,
                                            'method' => 'POST',
                                        );
                                        $response = wp_remote_post( $url, $args );
                                        $response = $response['body'];
                                        ?>
                                            <script>
                                                window.location.href = "admin.php?page=socia8-settings&success=fbpConnect";
                                            </script>
                                        <?php

									}
									if ( isset( $fbpages ) ) {
									?>
                                        <div class="modal fade" id="fbPageListModal" role="dialog">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button class="close" data-dismiss="modal" id="closeFbPage"
                                                                style="display: none"
                                                                onclick="closeModalBtn('fbPage')">x
                                                        </button>
                                                        <center>
                                                            <h4 class="modal-title" id="fbPageModalTitle"
                                                                style="display: none;">Select your
                                                                facebook page, you want to manage here..</h4>
                                                        </center>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div id="fbPageList">
                                                        </div>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <form method="post">
                                            <input type="hidden" name="fbPageId" id="fbPageId">
                                            <input type="hidden" name="fbPageIcon" id="fbPageIcon">
                                            <input type="hidden" name="fbPageName" id="fbPageName">
                                            <button type="submit" name="fbPageSaveBtn" id="fbPageSaveBtn"
                                                    style="display: none;">Save Page
                                            </button>
                                        </form>
									<?php
									}
									?>
                                        <script>
                                            function saveFbPage(pageId) {
                                                var logoUrl = jQuery('#logoUrl-' + pageId).attr('src');
                                                var pageName = jQuery('#pageName-' + pageId).html();
                                                jQuery('#fbPageId').val(pageId);
                                                jQuery('#fbPageIcon').val(logoUrl);
                                                jQuery('#fbPageName').val(pageName);
                                                jQuery('#fbPageSaveBtn').click();
                                            }
                                            function selectFbPage(user_id) {
                                                jQuery(this).attr('disabled', 'true');

                                                var pages =<?php echo json_encode( $fbpages );?>;
                                                console.log(pages);
                                                var html = '<div class="row-programs row col-md-12">';

                                                if (pages == '1') {
                                                    jQuery('#fbPageModalTitle').html('Whoops !');
                                                    jQuery('#closeFbPage').show();
                                                    html += '<center><h1>No Facebook Pages Found.</h1></center>';
                                                }
                                                else {
                                                    jQuery('#closeFbPages').hide();
                                                    pages.forEach(function (data) {
                                                        html += '<div class="col-program col-md-3 clearfix" align="center" onclick="saveFbPage(' + data.id + ',\'' + user_id + '\')">' +
                                                            '<img class="img-rounded" id="logoUrl-' + data.id + '" src="' + data.picture.data.url + '"><br>' +
                                                            '<span id="pageName-' + data.id + '">' + data.name + '</span>' +
                                                            '</div>';
                                                    });
                                                }
                                                html += '</div>';
                                                jQuery('#fbPageList').html(html);
                                                jQuery('#fbPageModalTitle').show();
                                                jQuery(document).ready(function ($) {
                                                    $('#fbPageListModal').modal({
                                                        backdrop: 'static',
                                                        keyboard: false,
                                                        show: true
                                                    });
                                                });
                                            }
                                        </script>
										<?php
									}
									?>
                                </td>
                                <td class="tdClass" align="center" width="25%" align="center">
									<?php
									if ( isset( $result[9]['status'] ) && $result[9]['status'] == 'Connected' ) {
										?>
                                        <img src="<?php echo $result[9]['profileInfo']['picture']; ?>"
                                             class="img-circle profileImg">
                                        <br>
										<?php
										echo $result[9]['profileInfo']['name'];
									}
									?>
                                </td>
                                <td class="tdClass" align="center" width="25%">
									<?php
									if ( isset( $result[9]['status'] ) && $result[9]['status'] == 'Connected' ) {
										?>
                                        <a href="https://www.socia8.com/wpsocia8/fbpdisconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                            <button class="btn btn-danger">Disconnect</button>
                                        </a>
										<?php
									} else {
										?>
                                        <a href="https://www.socia8.com/wpsocia8/fbpconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                            <button class="btn btn-info">Connect</button>
                                        </a>
										<?php
									}
									?>
                                </td>
                            </tr>
							<?php
							}
							if ( isset( $result[10] ) ) {
								?>
                                <tr>
                                    <td class="tdClass" align="center" width="25%">
                                        <img src="<?php echo plugin_dir_url( __FILE__ ); ?>assets/icons/linkdienpage.png"
                                             class="socialImg"/>
                                    </td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[10]['status'] ) && $result[10]['status'] == 'Connected' ) {
											$url     = "https://socia8.com/wpsocia8/getlnpages";
											$user_id = $userToken;
											$data    = [ 'user_id' => $user_id, 'redirectUrl' => $redirectUrl ];
											$args    = array(
												'body'   => $data,
												'method' => 'POST',
											);
											$lnpages = wp_remote_post( $url, $args );
											$lnpages = $lnpages['body'];
											$lnpages = json_decode( $lnpages, true );
											?>
                                            <button type="submit" id="lnpBtn" class="btn green-haze"
                                                    onclick="selectLnPage('<?php echo $userToken; ?>')"
                                                    name="lnPageBtn">
                                                <i class="fa fa-flag" aria-hidden="true"></i>
                                                Select Page
                                            </button>
										<?php
										if ( isset( $_POST['lnPageSaveBtn'] ) ){
										$pageIcon = sanitize_text_field( $_POST['lnPageIcon'] );
										$pageId   = sanitize_text_field( $_POST['lnPageId'] );
										$pageName = sanitize_text_field( $_POST['lnPageName'] );
										$url      = "https://socia8.com/wpsocia8/savelnpage";
										$user_id  = $userToken;
										$data     = [
											'user_id'     => $user_id,
											'pageId'      => $pageId,
											'logoUrl'     => $pageIcon,
											'pageName'    => $pageName,
											'redirectUrl' => $redirectUrl
										];
										$args     = array(
											'body'   => $data,
											'method' => 'POST',
										);
										$response = wp_remote_post( $url, $args );
										$response = $response['body'];

										?>
                                            <script>
                                                window.location.href = "admin.php?page=socia8-settings&success=lnpConnect";
                                            </script>
										<?php
                                        }

										if ( isset( $lnpages ) ) {
										?>
                                            <div class="modal fade" id="lnPageListModal" role="dialog">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button class="close" data-dismiss="modal" id="closeLnPage"
                                                                    style="display: none"
                                                                    onclick="closeModalBtn('lnPage')">x
                                                            </button>
                                                            <center>
                                                                <h4 class="modal-title" id="lnPageModalTitle"
                                                                    style="display: none;">Select your
                                                                    linkedin page, you want to manage here..</h4>
                                                            </center>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div id="lnPageList">
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <form method="post">
                                                <input type="hidden" name="lnPageId" id="lnPageId">
                                                <input type="hidden" name="lnPageIcon" id="lnPageIcon">
                                                <input type="hidden" name="lnPageName" id="lnPageName">
                                                <button type="submit" name="lnPageSaveBtn" id="lnPageSaveBtn"
                                                        style="display: none;">Save Page
                                                </button>
                                            </form>
										<?php
										}
										?>
                                            <script>
                                                function saveLnPage(pageId) {
                                                    var logoUrl = jQuery('#logoUrl-' + pageId).attr('src');
                                                    var pageName = jQuery('#pageName-' + pageId).html();
                                                    jQuery('#lnPageId').val(pageId);
                                                    jQuery('#lnPageIcon').val(logoUrl);
                                                    jQuery('#lnPageName').val(pageName);
                                                    jQuery('#lnPageSaveBtn').click();
                                                }
                                                function selectLnPage(user_id) {
                                                    jQuery(this).attr('disabled', 'true');

                                                    var pages =<?php echo json_encode( $lnpages );?>;
                                                    console.log(pages);
                                                    var html = '<div class="row-programs row col-md-12">';

                                                    if (pages == '1') {
                                                        jQuery('#lnPageModalTitle').html('Whoops !');
                                                        jQuery('#closeLnPage').show();
                                                        html += '<center><h1>No Linkedin Pages Found.</h1></center>';
                                                    }
                                                    else {
                                                        jQuery('#closeLnPage').hide();
                                                        pages.forEach(function (data) {
                                                            html += '<div class="col-program col-md-3 clearfix" align="center" onclick="saveLnPage(' + data.id + ',\'' + user_id + '\')">' +
                                                                '<img class="img-rounded" id="logoUrl-' + data.id + '" src="' + data.logoUrl + '"><br>' +
                                                                '<span id="pageName-' + data.id + '">' + data.name + '</span>' +
                                                                '</div>';
                                                        });
                                                    }
                                                    html += '</div>';
                                                    jQuery('#lnPageList').html(html);
                                                    jQuery('#lnPageModalTitle').show();
                                                    jQuery(document).ready(function ($) {
                                                        $('#lnPageListModal').modal({
                                                            backdrop: 'static',
                                                            keyboard: false,
                                                            show: true
                                                        });
                                                    });
                                                }
                                            </script>
											<?php
										}
										?>
                                    </td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[10]['status'] ) && $result[10]['status'] == 'Connected' ) {
											?>
                                            <img src="<?php echo $result[10]['profileInfo']['picture']; ?>"
                                                 class="img-circle profileImg">
                                            <br>
											<?php
											echo $result[10]['profileInfo']['name'];
										}
										?>
                                    </td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[10]['status'] ) && $result[10]['status'] == 'Connected' ) {
											?>
                                            <a href="https://www.socia8.com/wpsocia8/lnpdisconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-danger">Disconnect</button>
                                            </a>
											<?php
										} else {
											?>
                                            <a href="https://socia8.com/linkdeincompanypage/callback?authclient=linkdein&sociaRedirect=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-info">Connect</button>
                                            </a>
											<?php
										}
										?>
                                    </td>
                                </tr>
								<?php
							}
							if ( isset( $result[13] ) ) {
								?>
                                <tr>
                                    <td class="tdClass" align="center" width="25%">
                                        <img src="<?php echo plugin_dir_url( __FILE__ ); ?>assets/icons/g+page.png"
                                             class="socialImg"/>
                                    </td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[13]['status'] ) && $result[13]['status'] == 'Connected' ) {
											$url     = "https://socia8.com/wpsocia8/getglpages";
											$user_id = $userToken;
											$data    = [ 'user_id' => $user_id, 'redirectUrl' => $redirectUrl ];

											$args    = array(
												'body'   => $data,
												'method' => 'POST',
											);
											$glpages = wp_remote_post( $url, $args );
											$glpages = $glpages['body'];
											$glpages = json_decode( $glpages, true );
											?>
                                            <button type="submit" id="glpBtn" class="btn green-haze"
                                                    onclick="selectGlPage('<?php echo $userToken; ?>')"
                                                    name="glPageBtn">
                                                <i class="fa fa-flag" aria-hidden="true"></i>
                                                Select Page
                                            </button>
										<?php
										if ( isset( $_POST['glPageSaveBtn'] ) ){
										$pageIcon = sanitize_text_field( $_POST['glPageIcon'] );
										$pageId   = sanitize_text_field( $_POST['glPageId'] );
										$pageName = sanitize_text_field( $_POST['glPageName'] );
										$url      = "https://socia8.com/wpsocia8/saveglpage";
										$user_id  = $userToken;
										$data     = [
											'user_id'     => $user_id,
											'pageId'      => $pageId,
											'logoUrl'     => $pageIcon,
											'pageName'    => $pageName,
											'redirectUrl' => $redirectUrl
										];
										$args     = array(
											'body'   => $data,
											'method' => 'POST',
										);
										$response = wp_remote_post( $url, $args );
										$response = $response['body'];
                                        ?>
                                            <script>
                                                window.location.href = "admin.php?page=socia8-settings&success=glpConnect";
                                            </script>
										<?php

										}

										if ( isset( $glpages ) ) {
										?>
                                            <div class="modal fade" id="glPageListModal" role="dialog">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button class="close" data-dismiss="modal" id="closeGlPage"
                                                                    style="display: none"
                                                                    onclick="closeModalBtn('glPage')">x
                                                            </button>
                                                            <center>
                                                                <h4 class="modal-title" id="glPageModalTitle"
                                                                    style="display: none;">Select your
                                                                    google+ page, you want to manage here..</h4>
                                                            </center>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div id="glPageList">
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <form method="post">
                                                <input type="hidden" name="glPageId" id="glPageId">
                                                <input type="hidden" name="glPageIcon" id="glPageIcon">
                                                <input type="hidden" name="glPageName" id="glPageName">
                                                <button type="submit" name="glPageSaveBtn" id="glPageSaveBtn"
                                                        style="display: none;">Save Page
                                                </button>
                                            </form>
										<?php
										}
										?>
                                            <script>
                                                function saveGlPage(pageId) {
                                                    console.log(pageId);
                                                    var logoUrl = jQuery('#logoUrl-' + pageId).attr('src');
                                                    console.log(logoUrl);
                                                    var pageName = jQuery('#pageName-' + pageId).html();
                                                    jQuery('#glPageId').val(pageId);
                                                    jQuery('#glPageIcon').val(logoUrl);
                                                    jQuery('#glPageName').val(pageName);
                                                    jQuery('#glPageSaveBtn').click();
                                                }
                                                function selectGlPage(user_id) {
                                                    jQuery(this).attr('disabled', 'true');

                                                    var pages =<?php echo json_encode( $glpages );?>;
                                                    console.log(pages);
                                                    var html = '<div class="row-programs row col-md-12">';

                                                    if (pages == '1') {
                                                        jQuery('#glPageModalTitle').html('Whoops !');
                                                        jQuery('#closeGlPage').show();
                                                        html += '<center><h1>No Google+ Pages Found.</h1></center>';
                                                    }
                                                    else {
                                                        jQuery('#closeGlPage').hide();
                                                        pages.forEach(function (data) {
                                                            html += '<div class="col-program col-md-3 clearfix" align="center" onclick="saveGlPage(\'' + data.id + '\')">' +
                                                                '<img class="img-rounded" id="logoUrl-' + data.id + '" src="' + data.image + '"><br>' +
                                                                '<span id="pageName-' + data.id + '">' + data.name + '</span>' +
                                                                '</div>';
                                                        });
                                                    }
                                                    html += '</div>';
                                                    jQuery('#glPageList').html(html);
                                                    jQuery('#glPageModalTitle').show();
                                                    jQuery(document).ready(function ($) {
                                                        $('#glPageListModal').modal({
                                                            backdrop: 'static',
                                                            keyboard: false,
                                                            show: true
                                                        });
                                                    });
                                                }
                                            </script>
											<?php
										}
										?>
                                    </td>
                                    <td class="tdClass" align="center" width="25%" align="center">
										<?php
										if ( isset( $result[13]['status'] ) && $result[13]['status'] == 'Connected' ) {
											?>
                                            <img src="<?php echo $result[13]['profileInfo']['picture']; ?>"
                                                 class="img-circle profileImg">
                                            <br>
											<?php
											echo $result[13]['profileInfo']['name'];
										}
										?>
                                    </td>
                                    <td class="tdClass" align="center" width="25%">
										<?php
										if ( isset( $result[13]['status'] ) && $result[13]['status'] == 'Connected' ) {
											?>
                                            <a href="https://www.socia8.com/wpsocia8/glpdisconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-danger">Disconnect</button>
                                            </a>
											<?php
										} else {
											?>
                                            <a href="https://www.socia8.com/wpsocia8/glpconnect?redirectUrl=<?php echo $redirectUrl; ?>&user_id=<?php echo $userToken; ?>">
                                                <button class="btn btn-info">Connect</button>
                                            </a>
											<?php
										}
										?>
                                    </td>
                                </tr>
								<?php
							}
							?>
                            </tbody>
                        </table>
                    </div>
                </div>
				<?php
			}
			else {
				update_option( 'socia_my_plugin_activated', "loggedOut" );
				?>
                <div class="row col-md-12" style="text-align: center;margin-top: 15%">
                    <div class="col-md-offset-4">
                        <form method="post">
							<?php wp_nonce_field( 'name_of_my_action', 'name_of_nonce_field' ); ?>
                            <div class="form-group col-md-6">
                                <input type="text" name="username" class="form-control" placeholder="Enter Email">
                                <input type="hidden" name="redirectUrl" class="form-control" value="<?php echo $redirectUrl;?>">
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <input type="password" name="password" class="form-control"
                                       placeholder="Enter Password">
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-6">
                                <div class="col-md-6">
                                    <input type="checkbox" name="rememberMe1" id="rememberMe" class="form-control">
                                    <label style="padding-top: 7px;" for="rememberMe">Remember Me</label>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" name="loginSocia8" class="btn btn-danger">Login To socia8
                                    </button>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <input type="hidden" name="url" value="https://socia8.com/wpsocia8/">
                            <div class="col-md-6 form-group" align="center">
                                <a href="https://www.socia8.com/signup" target="_blank">If you are new to
                                    socia8, Sign up here..</a>
                            </div>
                        </form>
                    </div>
                </div>';
				<?php
			}
        }
			else {
					?>
                    <div class="row col-md-12" style="text-align: center;margin-top: 15%">
                        <div class="col-md-offset-4">
                            <form method="post">
								<?php wp_nonce_field( 'name_of_my_action', 'name_of_nonce_field' ); ?>
                                <div class="form-group col-md-6">
                                    <input type="text" name="username" class="form-control" placeholder="Enter Email">
                                </div>
                                <div class="clearfix"></div>
                                <div class="form-group col-md-6">
                                    <input type="password" name="password" class="form-control"
                                           placeholder="Enter Password">
                                </div>
                                <div class="clearfix"></div>
                                <div class="form-group col-md-6">
                                    <div class="col-md-6">
                                        <input type="checkbox" name="rememberMe1" id="rememberMe" class="form-control">
                                        <label style="padding-top: 7px;" for="rememberMe">Remember Me</label>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" name="loginSocia8" class="btn btn-danger">Login To socia8
                                        </button>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <input type="hidden" name="url" value="https://socia8.com/wpsocia8/">
                                <div class="col-md-6 form-group" align="center">
                                    <a href="https://www.socia8.com/signup" target="_blank">If you are new to
                                        socia8, Sign up here..</a>
                                </div>
                            </form>
                        </div>
                    </div>';
					<?php
				}
			}
			else {
				$html = ' 
                    <div class="row col-md-12" style="text-align: center;margin-top: 15%">
        				<div class="col-md-offset-4">
        		            <form method="post">
        		                <div class="form-group col-md-6">
        		                    <input type="text" name="username" class="form-control" placeholder="Enter Email">
        						</div>
        						<div class="clearfix"></div>
        		                <div class="form-group col-md-6">
        		                    <input type="password" name="password" class="form-control" placeholder="Enter Password">
        						</div>
        						<div class="clearfix"></div>
        		                <div class="form-group col-md-6">
        		                    <div class="col-md-6">
                                        <input type="checkbox" name="rememberMe1" id="rememberMe" class="form-control">
                                        <label style="padding-top: 7px;" for="rememberMe">Remember Me</label>
                                    </div>  
                                    <div class="col-md-6">
                                        <button type="submit" name="loginSocia8" class="btn btn-danger">Login To socia8</button>
                                    </div>
        						</div>
        						<div class="clearfix"></div>
        		                <input type="hidden" name="url" value="https://socia8.com/wpsocia8/">
        		                <div class="col-md-6 form-group" align="center">
        		                    <a href="https://www.socia8.com/signup" target="_blank">If you are new to socia8, Sign up here..</a>
        						</div>
        					</form>
        				</div>
        			</div>';
				echo $html;
			}
		}
function socia8_custom_meta_box_markup( $object ) {
    wp_nonce_field( basename( __FILE__ ), "meta-box-nonce" );
    ?>
    <div>
        <?php
        $userToken = get_option( 'socia_my_plugin_activated' );

        if ( $userToken != "loggedOut" || $userToken != "") {
//             $email                      = $userToken;
            $data   = [ 'email' => $userToken ];
            $args   = array(
                'body'        => $data,
                'method'      => 'POST',
                'timeout'     => 45,
                'redirection' => 5,
            );
            $url    = "https://socia8.com/wpsocia8/getactivepackage";
            $result = wp_remote_post( $url, $args );
            if ( $result['body'] == "loggedOut" ) {
                $servicesAllowed=[];
                update_option( 'socia_my_plugin_activated', "loggedOut" );
                ?>
                <style>
                    .btn {
                        display: inline-block;
                        padding: 6px 12px;
                        margin-bottom: 0;
                        font-size: 14px;
                        font-weight: 400;
                        line-height: 1.42857143;
                        text-align: center;
                        white-space: nowrap;
                        vertical-align: middle;
                        -ms-touch-action: manipulation;
                        touch-action: manipulation;
                        cursor: pointer;
                        -webkit-user-select: none;
                        -moz-user-select: none;
                        -ms-user-select: none;
                        user-select: none;
                        background-image: none;
                        border: 1px solid transparent;
                        border-radius: 4px;
                    }

                    .btn-success {
                        color: #fff;
                        background-color: #5cb85c;
                        border-color: #4cae4c;
                    }
                </style>
                <?php
                echo '<center><a href="admin.php?page=socia8-settings"><button type="button" class="btn btn-success">Authorize to socia8</button></a></center>';
            } else {
                $servicesAllowed = json_decode( $result['body'], true );
                update_option( 'socia_my_plugin_activated', $servicesAllowed['updatedToken'] );
                ?>
                <table class="widefat fixed">
                    <tr>
                        <th colspan="2" style="text-align: center">
                            <?php
                            if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
                                ?>
                                <input name="meta-box-checkbox-socia8" id="meta-box-checkbox-socia8"
                                       type="checkbox"
                                       value="no" onchange="checkBoxChanged()">
                                <?php
                            } else {
                                ?>
                                <input name="meta-box-checkbox-socia8" id="meta-box-checkbox-socia8"
                                       type="checkbox"
                                       value="yes" checked onchange="checkBoxChanged()">
                                <?php
                            }
                            ?>
                            <b><label for="meta-box-checkbox-socia8">Post on socia8 Also</label></b><br>
                            <hr>
                        </th>
                    </tr>
                    <tr id="platformsRow">
                        <th><label for="meta-box-checkbox"> Post On Following Social Platforms:</label></th>
                        <td>
                            <?php
                            if ( array_key_exists( 1, $servicesAllowed ) ) {
                                ?>
                                <input name="meta-box-checkbox-fb" id="meta-box-checkbox-fb" type="checkbox"
                                       value="1"
                                       checked>
                                <b><label for="meta-box-checkbox-fb">Facebook</label></b><br>
                                <?php
                            }
                            if ( array_key_exists( 2, $servicesAllowed ) ) {
                                ?>
                                <input name="meta-box-checkbox-tw" id="meta-box-checkbox-tw" type="checkbox"
                                       value="2"
                                       checked>
                                <b><label for="meta-box-checkbox-tw">Twitter</label></b><br>
                                <?php
                            }
                            if ( array_key_exists( 3, $servicesAllowed ) ) {
                                ?>
                                <input name="meta-box-checkbox-gl" id="meta-box-checkbox-gl" type="checkbox"
                                       value="3"
                                       checked>
                                <b><label for="meta-box-checkbox-gl">Google +</label></b><br>
                                <?php
                            }
                            if ( array_key_exists( 4, $servicesAllowed ) ) {
                                ?>
                                <input name="meta-box-checkbox-ln" id="meta-box-checkbox-ln" type="checkbox"
                                       value="4" checked>
                                <b><label for="meta-box-checkbox-ln">Linkedin</label></b><br>
                                <?php
                            }
                            if ( array_key_exists( 6, $servicesAllowed ) ) {
                                ?>
                                <input name="meta-box-checkbox-pi" id="meta-box-checkbox-pi" type="checkbox"
                                       value="6" checked>
                                <b><label for="meta-box-checkbox-pi">Pinterest</label></b><br>
                                <?php
                            }
                            if ( array_key_exists( 8, $servicesAllowed ) ) {
                                ?>
                                <input name="meta-box-checkbox-fg" id="meta-box-checkbox-fg" type="checkbox"
                                       value="8" checked>
                                <b><label for="meta-box-checkbox-fg">FaceBook Group</label></b><br>
                                <?php
                            }
                            if ( array_key_exists( 9, $servicesAllowed ) ) {
                                ?>
                                <input name="meta-box-checkbox-fp" id="meta-box-checkbox-fp" type="checkbox"
                                       value="9" checked>
                                <b><label for="meta-box-checkbox-fp">FaceBook Page</label></b><br>
                                <?php
                            }
                            if ( array_key_exists( 10, $servicesAllowed ) ) {
                                ?>
                                <input name="meta-box-checkbox-lp" id="meta-box-checkbox-lp" type="checkbox"
                                       value="10" checked>
                                <b><label for="meta-box-checkbox-lp">Linkedin Page</label></b><br>
                                <?php
                            }
                            if ( array_key_exists( 13, $servicesAllowed ) ) {
                                ?>
                                <input name="meta-box-checkbox-gp" id="meta-box-checkbox-gp" type="checkbox"
                                       value="13" checked>
                                <b><label for="meta-box-checkbox-gp">Google+ Page</label></b><br>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <tr id="publishTimeRow">
                        <th><label for="meta-box-radio">Select Post Publish time:</label></th>
                        <td>
                            <input name="meta-box-radio-schedule" id="immediate" onclick="hideDateDiv()"
                                   type="radio"
                                   value="immediate" checked>
                            <b><label for="immediate">Immediate</label></b>
                            <input name="meta-box-radio-schedule" id="schedule" onclick="showDateDiv()"
                                   type="radio"
                                   value="scheduled">
                            <b><label for="schedule">Schedule</label></b><br>
                            <?php
                            wp_enqueue_style( "Socia8_bootstrap_css", plugin_dir_url( __FILE__ ) . 'assets/css/bootstrap.css' );
                            wp_enqueue_script( 'Socia8_bootstrap_js', plugin_dir_url( __FILE__ ) . 'assets/js/bootstrap.js', array( 'jquery' ) );

                            wp_register_style( 'Socia8_datepicker_css', plugin_dir_url( __FILE__ ) . 'assets/css/datepicker.css' );
                            wp_enqueue_style( 'Socia8_datepicker_css' );

                            wp_register_script( 'Socia8_moment_js', plugin_dir_url( __FILE__ ) . 'assets/js/moment.js' );
                            wp_enqueue_script( 'Socia8_moment_js' );

                            wp_register_script( 'Socia8_datetimepicker_js', plugin_dir_url( __FILE__ ) . 'assets/js/datetimepicker.js', array( 'jquery' ) );
                            wp_enqueue_script( 'Socia8_datetimepicker_js' );
                            ?>
                            <script>
                                function showDateDiv() {
                                    jQuery('#datetimepicker1').show();
                                }
                                function hideDateDiv() {
                                    jQuery('#datetimepicker1').hide();
                                }
                                function checkBoxChanged() {
                                    if (jQuery('#meta-box-checkbox-socia8').prop('checked') == true) {
                                        jQuery('#platformsRow').show();
                                        jQuery('#publishTimeRow').show();
                                        jQuery('#meta-box-checkbox-socia8').val('yes');
                                    }
                                    else {
                                        jQuery('#platformsRow').hide();
                                        jQuery('#publishTimeRow').hide();
                                        jQuery('#meta-box-checkbox-socia8').val('no');
                                    }
                                }
                                if (jQuery('#meta-box-checkbox-socia8').prop('checked') == true) {
                                    jQuery('#platformsRow').show();
                                    jQuery('#publishTimeRow').show();
                                    jQuery('#meta-box-checkbox-socia8').val('yes');
                                }
                                else {
                                    jQuery('#platformsRow').hide();
                                    jQuery('#publishTimeRow').hide();
                                    jQuery('#meta-box-checkbox-socia8').val('no');
                                }
                            </script>

                            <div class='input-group date' id='datetimepicker1' style="display:none;">
                                <input type='text' name="meta-box-schedule-time" class="form-control"/>
                                <span class="input-group-addon">
                         <span class="glyphicon glyphicon-calendar"></span>
                     </span>
                            </div>

                            <script type="text/javascript">
                                jQuery(function () {
                                    jQuery('#datetimepicker1').datetimepicker({
                                        sideBySide: true,
                                        format: 'DD MMMM YYYY -hh:mm A',
                                        minDate: new Date(),
//                                     minuteStep : 2
                                    });
                                });
                            </script>
                        </td>
                    </tr>
                </table>
                <?php
            }
        } else {
            ?>
            <style>
                .btn {
                    display: inline-block;
                    padding: 6px 12px;
                    margin-bottom: 0;
                    font-size: 14px;
                    font-weight: 400;
                    line-height: 1.42857143;
                    text-align: center;
                    white-space: nowrap;
                    vertical-align: middle;
                    -ms-touch-action: manipulation;
                    touch-action: manipulation;
                    cursor: pointer;
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                    user-select: none;
                    background-image: none;
                    border: 1px solid transparent;
                    border-radius: 4px;
                }

                .btn-success {
                    color: #fff;
                    background-color: #5cb85c;
                    border-color: #4cae4c;
                }
            </style>
            <?php
            echo '<center><a href="admin.php?page=socia8-settings"><button type="button" class="btn btn-success">Authorize to socia8</button></a></center>';
        }
        ?>
    </div>
    <?php
}

function socia8_add_custom_meta_box() {
    add_meta_box( "demo-meta-box", "socia8", "socia8_custom_meta_box_markup",
        "post", "normal", "high", null );
}

add_action( "add_meta_boxes", "socia8_add_custom_meta_box" );
function socia8_get_string_between( $string, $start, $end ) {
    $string = ' ' . $string;
    $ini    = strpos( $string, $start );
    if ( $ini == 0 ) {
        return '';
    }
    $ini += strlen( $start );
    $len = strpos( $string, $end, $ini ) - $ini;

    return substr( $string, $ini, $len );
}

function socia8_save_post_meta( $post_id, $post ) {
    $fb = sanitize_html_class( $_POST['meta-box-checkbox-fb'] );
    if ( isset( $_POST['meta-box-checkbox-tw'] ) ) {
        $tw = sanitize_html_class( $_POST['meta-box-checkbox-tw'] );
    } else {
        $tw = 0;
    }
    $gl       = sanitize_html_class( $_POST['meta-box-checkbox-gl'] );
    $ln       = sanitize_html_class( $_POST['meta-box-checkbox-ln'] );
    $pi       = sanitize_html_class( $_POST['meta-box-checkbox-pi'] );
    $fp       = sanitize_html_class( $_POST['meta-box-checkbox-fp'] );
    $fg       = sanitize_html_class( $_POST['meta-box-checkbox-fg'] );
    $gp       = sanitize_html_class( $_POST['meta-box-checkbox-gp'] );
    $lp       = sanitize_html_class( $_POST['meta-box-checkbox-lp'] );
    $socia8   = sanitize_html_class( $_POST['meta-box-checkbox-socia8'] );
    $postType = sanitize_html_class( $_POST['meta-box-radio-schedule'] );
    $postTime = sanitize_text_field( $_POST['meta-box-schedule-time'] );
    update_post_meta( $post_id, 'meta-box-checkbox-fb', $fb );
    update_post_meta( $post_id, 'meta-box-checkbox-tw', $tw );
    update_post_meta( $post_id, 'meta-box-checkbox-gl', $gl );
    update_post_meta( $post_id, 'meta-box-checkbox-ln', $ln );
    update_post_meta( $post_id, 'meta-box-checkbox-pi', $pi );
    update_post_meta( $post_id, 'meta-box-checkbox-fp', $fp );
    update_post_meta( $post_id, 'meta-box-checkbox-fg', $fg );
    update_post_meta( $post_id, 'meta-box-checkbox-gp', $gp );
    update_post_meta( $post_id, 'meta-box-checkbox-lp', $lp );
    update_post_meta( $post_id, 'meta-box-radio-schedule', $postType );
    update_post_meta( $post_id, 'meta-box-schedule-time', $postTime );
    update_post_meta( $post_id, 'meta-box-checkbox-socia8', $socia8 );
}

add_action( 'publish_post', 'socia8_save_post_meta', 10, 2 );

function socia8_post_on_socia8( $post_id, $post ) {
    $socia8 = get_post_meta( $post_id, 'meta-box-checkbox-socia8' )[0];
    if ( $socia8 == "yes" ) {
        if ( $post->post_content != "" ) {
            $img = socia8_get_string_between( $post->post_content, 'src="', '" ' );
        }
        $fb           = get_post_meta( $post_id, 'meta-box-checkbox-fb' );
        $tw           = get_post_meta( $post_id, 'meta-box-checkbox-tw' );
        $gl           = get_post_meta( $post_id, 'meta-box-checkbox-gl' );
        $ln           = get_post_meta( $post_id, 'meta-box-checkbox-ln' );
        $pi           = get_post_meta( $post_id, 'meta-box-checkbox-pi' );
        $fp           = get_post_meta( $post_id, 'meta-box-checkbox-fp' );
        $fg           = get_post_meta( $post_id, 'meta-box-checkbox-fg' );
        $gp           = get_post_meta( $post_id, 'meta-box-checkbox-gp' );
        $lp           = get_post_meta( $post_id, 'meta-box-checkbox-lp' );
        $postType     = get_post_meta( $post_id, 'meta-box-radio-schedule' )[0];
        $postTime     = get_post_meta( $post_id, 'meta-box-schedule-time' )[0];
        $post_media[] = $fb[0];
        $post_media[] = $tw[0];
        $post_media[] = $gl[0];
        $post_media[] = $ln[0];
        $post_media[] = $pi[0];
        $post_media[] = $fp[0];
        $post_media[] = $fg[0];
        $post_media[] = $gp[0];
        $post_media[] = $lp[0];
        $userToken    = get_option( 'socia_my_plugin_activated' );

        $url = $_SERVER['QUERY_STRING'];
        parse_str( $url, $arr );
        $h = '?';
        foreach ( $arr as $key => $value ) {
            if ( $key != 'success' ) {
                $h .= $key . '=' . $value . '&';
            }
        }
        $protocol    = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off'
                         || $_SERVER['SERVER_PORT'] == 443 ) ? 'https://' : 'http://';
        $redirectUrl = base64_encode( $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $h );

        if ( isset( $img ) && $img != "" ) {
            $image       = file_get_contents( $img );
            $fname       = time() . "-" . basename( $img );
            $postMessage = $post->post_title;
            $data        = [
                'post_message' => $postMessage,
                'post_media'   => json_encode( $post_media ),
                'postType'     => $postType,
                'postTime'     => $postTime,
                'timezone'     => $userToken,
                'user_id'      => $userToken,
                'post_type'    => "image",
                'image'        => $image,
                'file_name'    => $fname,
                'redirectUrl'  => $redirectUrl
            ];
        } else {
            $postMessage = $post->post_title;
            $data        = [
                'post_message' => $postMessage,
                'post_media'   => json_encode( $post_media ),
                'postType'     => $postType,
                'postTime'     => $postTime,
                'timezone'     => $userToken,
                'user_id'      => $userToken,
                'post_type'    => 'text',
                'redirectUrl'  => $redirectUrl
            ];
        }
        $url    = "https://socia8.com/wpsocia8/wppost";
        $args   = array(
            'body'        => $data,
            'method'      => 'POST',
            'timeout'     => 100
        );
        $result = wp_remote_post( $url, $args );
        if($result['body'] == "Something went wrong"){
	        ?>
            <div class="notice notice-error is-dismissible">
                <p>
			        <?php echo sprintf( __( '%s: Something went wrong.', "socia8" ), 'socia8' ); ?>
                </p>
            </div>
	        <?php
        }
        else {
	        $result = json_decode( $result['body'], true );

	        if ( isset( $result['success'] ) && isset( $result['error'] ) && $result['error'] == "" ) {
		        ?>
                <div class="notice notice-success is-dismissible">
                    <p>
				        <?php echo sprintf( __( '%s: Post successfully added to socia8.', "socia8" ), 'socia8' ); ?>
                    </p>
                </div>
		        <?php
	        } else {
		        ?>
                <div class="notice notice-error is-dismissible">
                    <p>
				        <?php echo sprintf( __( '%s: something went wrong while posting to your selected social networks. Kindly login to socia8 and check the posts that were posted successfully.', "socia8" ), 'socia8' ); ?>
                    </p>
                </div>
		        <?php
	        }
        }
    }
}
add_action( 'publish_post', 'socia8_post_on_socia8', 11, 2 );