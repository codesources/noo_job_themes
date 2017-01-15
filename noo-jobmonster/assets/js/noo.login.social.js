jQuery(document).ready(function($) {
  // -- login facebook
  $('.button_socical').on('click', '.fa-facebook-square', function(event) {
    event.preventDefault();
    var $this = $(this);
    if ( typeof(FB) != 'undefined' ) {
      FB.login(function(result) {
        if (result.authResponse) {
          var grantedScopes = result.authResponse.grantedScopes.split(',');
          if( grantedScopes.indexOf( 'email' ) !== -1 ) {
            FB.api('/me?fields=name,email', function(response) {
              if (!response || response.error) {
                $('.noo-ajax-result').show().html( nooSocial.msgMissingAppID );
              } else if( !response.email ) {
                $('.noo-ajax-result').show().html( nooSocial.msgFBMissingEmail );
              } else {
                var data = {
                  action : 'check_login',
                  using : 'fb',
                  id : response.email
                };
                $.post( nooSocial.ajax_url, data, function( result ) {
                  if ( result.status === 'success' ) {

                    $('.noo-ajax-result').show().html( nooSocial.msgLoginSuccessful );
                    window.location.reload();

                  } else if( result.status === 'not_user' ) {
                    if ( nooSocial.allow == 'both' ) {
                      var registerModal = $('.memberModalRegisterSocial');
                      // -- Hide .register_social
                        registerModal.find('.register_social').hide();
                      
                      // -- Set title and background
                        registerModal.find('.modal-header').css( {
                          'background': '#2952aa',
                          'border-radius': '3px 2px 0 0',
                        });
                        registerModal.find('.close').css( 'color', '#fff' );
                        registerModal.find('.modal-title').html( nooSocial.msgFacebookModalTitle ).css('color', '#fff');

                      // -- Return text hello
                        registerModal.find('.register-heading').show().html(nooSocial.msgHi + response.name).css({
                          margin: '0px 0px 20px 0px'
                        });

                      // -- set value user
                        registerModal.find('input[name="user_login"]').val( response.email );

                      // -- set value email
                        registerModal.find('input[name="user_email"]').val( response.email );

                      // -- hide input
                        var rand_pass = random_pass();

                        registerModal.find('input[name="user_password"]').val(rand_pass);
                        registerModal.find('input[name="cuser_password"]').val(rand_pass);

                      // -- set id
                        registerModal.find('form').append(
                          '<input type="hidden" class="using" name="using" value="fb" />' +
                          '<input type="hidden" class="userid" name="userid" value="' + response.id + '" />' +
                          '<input type="hidden" class="name" name="name" value="' + response.name + '" />' +
                          '<input type="hidden" class="using_id" name="using_id" value="' + response.email + '" />' 
                        );
                      
                      // -- Hide box login if not user
                        $('.memberModalLogin').modal('hide');

                      // -- Show box register
                        registerModal.modal('show');
                    } else {
                      var info_user = {};
                      if ( nooSocial.allow == 'employer' ) {
                        info_user = {
                          action : 'create_user',
                          security : nooSocial.security,
                          using: 'facebook',
                          id : response.email,
                          userid : response.id,
                          name : response.name,
                          capabilities : 'employer'
                        };
                      } else if ( nooSocial.allow == 'candidate' ) {
                        info_user = {
                          action : 'create_user',
                          security : nooSocial.security,
                          using: 'facebook',
                          id : response.email,
                          userid : response.id,
                          name : response.name,
                          capabilities : 'candidate',
                          birthday : response.birthday,
                          address : response.address,
                        };
                      }
                      $.post(nooSocial.ajax_url, info_user, function(result) {
                        // console.log(result);
                        if ( result.status == 'success' ) window.location.reload();
                      });
                    }
                  } else if( result.status === 'error' ) {
                    $('.noo-ajax-result').show().html( result.message );
                    return false;
                  } else {
                    $('.noo-ajax-result').show().html( nooSocial.msgServerError );
                    return false;
                  }
                });
              }
            });
          } else {
            $('.noo-ajax-result').show().html( nooSocial.msgFBMissingEmail );
            FB.api('/me/permissions', 'DELETE');
          }
        } else {
          $('.noo-ajax-result').show().html( nooSocial.msgFBUserCanceledLogin);
          return false;
        }
      }, {
        scope: 'email', 
        return_scopes: true
      });

      return false;
    }
  });

  // -- Login google
  var startApp = function() {
    gapi.load('auth2', function(){
      var client_id = nooSocial.google_client_id;
      client_id = client_id.split(".")[0];
      var cookiepolicy = nooSocial.google_client_secret;

      // Retrieve the singleton for the GoogleAuth library and set up the client.
      auth2 = gapi.auth2.init({
        client_id: client_id + '.apps.googleusercontent.com',
        cookiepolicy: cookiepolicy,
        // Request scopes in addition to 'profile' and 'email'
        //scope: 'additional_scope'
      });
      $('.button_socical .fa-google-plus').each(function() {
        attachSignin(this.id);
      });
    });
  };

  function attachSignin(element) {
    auth2.attachClickHandler(element, {}, 
        function( infoUser ){
          var authResponse = infoUser.getAuthResponse();
          var data = {
            action : 'check_login',
            using : 'gg',
            id : infoUser.getBasicProfile().getEmail(),
            id_token : authResponse.id_token
          };
          
          $this = $(element);
          $.post( nooSocial.ajax_url, data, function( result ) {
            // console.log(result); return;
            if ( result.status === 'success' ){
              $('.noo-ajax-result').show().html( nooSocial.msgLoginSuccessful );
              window.location.reload();
            }else if( result.status === 'not_user' ){
              if ( nooSocial.allow == 'both' ) {
                var registerModal = $('.memberModalRegisterSocial');
                // -- Hide .register_social
                  registerModal.find('.register_social').hide();
                
                // -- Set title and background
                  registerModal.find('.modal-header').css( {
                    'background': '#cc3d2d',
                    'border-radius': '3px 2px 0 0',
                  });
                  registerModal.find('.close').css( 'color', '#fff' );
                  registerModal.find('.modal-title').html( nooSocial.msgGoogleModalTitle ).css('color', '#fff');

                // -- Return text hello
                  registerModal.find('.register-heading').show().html(nooSocial.msgHi + infoUser.getBasicProfile().getName()).css({
                    margin: '0px 0px 20px 0px'
                  });
                
                // -- Hide box login if not user
                  $('.memberModalLogin').modal('hide');

                // -- Show box register
                  registerModal.modal('show').removeClass('form-actions');

                // -- set value user
                  // var user = (response.name).replace(/\s+/g, '_').toLowerCase();
                  registerModal.find('input[name="user_login"]').val( infoUser.getBasicProfile().getEmail() );

                // -- set value email
                  registerModal.find('input[name="user_email"]').val( infoUser.getBasicProfile().getEmail() );

                // -- hide input
                  var rand_pass = random_pass();

                  registerModal.find('input[name="user_password"]').val(rand_pass);
                  registerModal.find('input[name="cuser_password"]').val(rand_pass);

                // -- set id
                  registerModal.find('form').append(
                    '<input type="hidden" class="using" name="using" value="gg" />' +
                    '<input type="hidden" class="userid" name="userid" value="' + infoUser.getBasicProfile().getId() + '" />' +
                    '<input type="hidden" class="name" name="name" value="' + infoUser.getBasicProfile().getName() + '" />' +
                    '<input type="hidden" class="using_id" name="using_id" value="' + infoUser.getBasicProfile().getEmail() + '" />' 
                  );
              } else {
                var info_user = {};
                if ( nooSocial.allow == 'employer' ) {
                  info_user = {
                    action : 'create_user',
                    security : nooSocial.security,
                    using: 'google',
                    id : infoUser.getBasicProfile().getEmail(),
                    userid : infoUser.getBasicProfile().getId(),
                    name : infoUser.getBasicProfile().getName(),
                    capabilities : 'employer'
                  };
                } else if ( nooSocial.allow == 'candidate' ) {
                  info_user = {
                    action : 'create_user',
                    security : nooSocial.security,
                    using: 'google',
                    id : infoUser.getBasicProfile().getEmail(),
                    userid : infoUser.getBasicProfile().getId(),
                    name : infoUser.getBasicProfile().getName(),
                    capabilities : 'candidate',
                    // birthday : response.birthday,
                    // address : response.address,
                  };
                }
                $.post(nooSocial.ajax_url, info_user, function(result) {
                  // var result = JSON.parse(result);
                  // console.log(result);
                  if ( result.status == 'success' ) window.location.reload();
                });
              }
            } else if ( result.status === 'error' ) {
              $('.noo-ajax-result').show().html( result.message );
              return false;
            } else {
              $('.noo-ajax-result').show().html( nooSocial.msgServerError );
            }
          });
        }, function( error ) {
          $('.noo-ajax-result').show().html( error );
          return false;
        });
  }

  if( nooSocial.google_client_id !== "" && ( typeof gapi !== "undefined" ) ) {
    startApp();
  }

  // -- Login LinkedIn
  $('.button_socical').on('click', '.fa-linkedin-square', function(event) {
    event.preventDefault();

    var $this = $(this);

    if ( ( typeof IN !== "undefined" ) && IN.User.isAuthorized() ) {

      IN.API.Profile("me")
      .fields( "id", "email-address", "firstName", "lastName" )
      .result( function(result) {
        
        reques_api( result );
      
      });

    } else {
      
      IN.UI.Authorize().place();
      onLinkedInAuth = function (){
        IN.API.Profile("me")
        .fields( "id", "email-address", "firstName", "lastName" )
        .result( function(result) {
          
          reques_api( result );
        
        });
      };
      IN.Event.on(IN, "auth", onLinkedInAuth);
    
    }

    reques_api = function( info ) {
      var name = info.values[0]["firstName"] + ' ' + info.values[0]["lastName"];
      var data = {
        action : 'check_login',
        using : 'linkedin',
        id : info.values[0]["emailAddress"]
      };
      $.post( nooSocial.ajax_url, data, function( result ) {
        if ( result.status === 'success' ){
          $('.noo-ajax-result').show().html( nooSocial.msgLoginSuccessful );
          window.location.reload();
        } else if( result.status === 'not_user' ){
          if ( nooSocial.allow == 'both' ) {
            var registerModal = $('.memberModalRegisterSocial');
            // -- Hide .register_social
              registerModal.find('.register_social').hide();
            
            // -- Set title and background
              registerModal.find('.modal-header').css( {
                'background': '#0077b4',
                'border-radius': '3px 2px 0 0',
              });
              registerModal.find('.close').css( 'color', '#fff' );
              registerModal.find('.modal-title').html( nooSocial.msgLinkedInModalTitle ).css('color', '#fff');

            // -- Return text hello
              registerModal.find('.register-heading').show().html(nooSocial.msgHi + name).css({
                margin: '0px 0px 20px 0px'
              });
            
            // -- Hide box login if not user
              $('.memberModalLogin').modal('hide');

            // -- Show box register
              registerModal.modal('show').removeClass('form-actions');

            // -- set value user
              // var user = (response.name).replace(/\s+/g, '_').toLowerCase();
              registerModal.find('input[name="user_login"]').val( info.values[0]["emailAddress"] );

            // -- set value email
              registerModal.find('input[name="user_email"]').val( info.values[0]["emailAddress"]);

            // -- hide input
              var rand_pass = random_pass();

              registerModal.find('input[name="user_password"]').val(rand_pass);
              registerModal.find('input[name="cuser_password"]').val(rand_pass);

            // -- set id
              registerModal.find('form').append(
                '<input type="hidden" class="using" name="using" value="gg" />' +
                '<input type="hidden" class="name" name="name" value="' + name + '" />' +
                '<input type="hidden" class="using_id" name="using_id" value="' + info.values[0]["emailAddress"] + '" />' 
              );
          } else {
            var info_user = {};
            if ( nooSocial.allow == 'employer' ) {
              info_user = {
                action : 'create_user',
                security : nooSocial.security,
                using : 'linkedin',
                id : info.values[0]["emailAddress"],
                name : name,
                capabilities : 'employer'
              };
            } else if ( nooSocial.allow == 'candidate' ) {
              info_user = {
                action : 'create_user',
                security : nooSocial.security,
                using : 'linkedin',
                id : info.values[0]["emailAddress"],
                name : name,
                capabilities : 'candidate',
                // birthday : response.birthday,
                // address : response.address,
              };
            }
            $.post(nooSocial.ajax_url, info_user, function(result) {
              if ( result.status == 'success' ) window.location.reload();
            });
          }
        } else if ( result.status === 'error' ) {
          $('.noo-ajax-result').show().html( result.message );
          return false;
        } else{
          $('.noo-ajax-result').show().html( nooSocial.msgServerError );
        }
      });
    };

  });

});

//  -- Random password
  function random_pass(){
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < 15; i++ ) {
      text += possible.charAt(Math.floor(Math.random() * possible.length));
    }

    return text;
}

window.fbAsyncInit = function() {
  FB.init({
    appId      : nooSocial.facebook_api,
    cookie     : true,
                      
    xfbml      : true,
    version    : 'v2.5'
  });

};

// Load the SDK asynchronously
if( nooSocial.facebook_api !== "" ) {
  (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
}