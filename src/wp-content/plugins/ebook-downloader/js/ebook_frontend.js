function paypal_form_validation()
{
    var divID = jQuery('#ebook_msg');
    var err = pay_form_validation();
    
    if(err.length != null)
    {
            divID.html('<font color="red">'+err+'</font>');
            divID.fadeIn();
            err = null;
            return false;
    }   
}

function pay_form_validation()
{
   var name = jQuery('#p_name').val();
   var email = jQuery('#p_email').val();
   
   var error = null;
   if(name == '')
   {    
      error = 'Please Enter Your name.' ;
   }   
  else if(email == '')
  {    
      error = 'Please Enter Your Email ';
  }    
  else if( !IsEmail(email) )
  {    
      error = 'Please Enter Valid Email Id';
  }    
  
  return error;
}

function IsEmail(email) {
var regex =  /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/;

return regex.test(email);
}